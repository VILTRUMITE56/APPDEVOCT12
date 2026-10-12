<?php

require_once 'dbconfig.php';
require_once 'navbar.php';

try {
    $pdo = getPDO();
    
    echo "<h2>PDO Update Record Demonstration</h2>";
    
    echo "<h3>Example 1: UPDATE Single Record by ID</h3>";
    
    $viewSql = "SELECT item_id, item_name, brand_name, price_aud, stock_quantity 
                FROM grocery_items WHERE item_id = 1";
    $viewStmt = $pdo->prepare($viewSql);
    $viewStmt->execute();
    $originalRecord = $viewStmt->fetch();
    
    echo "<p>Original record (ID: 1):</p>";
    echo "<pre>";
    print_r($originalRecord);
    echo "</pre>";
    
    $updateSql = "UPDATE grocery_items 
                  SET price_aud = :new_price, 
                      stock_quantity = :new_stock,
                      item_name = :new_name
                  WHERE item_id = :id";
    
    $updateStmt = $pdo->prepare($updateSql);
    
    $newData = [
        ':id'          => 1,
        ':new_name'    => 'Tim Tam Original (Updated)',
        ':new_price'   => 3.75,
        ':new_stock'   => 200
    ];
    
    $result = $updateStmt->execute($newData);
    $affectedRows = $updateStmt->rowCount();
    
    if ($result && $affectedRows > 0) {
        echo "<div style='color: green;'>";
        echo "<p><strong>Success!</strong> Record updated. Affected rows: $affectedRows</p>";
        echo "</div>";
    } else {
        echo "<div style='color: orange;'>";
        echo "<p>No record updated (affected rows: $affectedRows)</p>";
        echo "</div>";
    }
    
    $verifyStmt = $pdo->prepare("SELECT item_id, item_name, brand_name, price_aud, stock_quantity FROM grocery_items WHERE item_id = 1");
    $verifyStmt->execute();
    $updatedRecord = $verifyStmt->fetch();
    
    echo "<p>Updated record:</p>";
    echo "<pre>";
    print_r($updatedRecord);
    echo "</pre>";
    
    echo "<h3>Example 2: UPDATE Multiple Records with Condition</h3>";
    
    $category = 'Biscuits';
    $percentageIncrease = 1.10;
    
    $countSql = "SELECT COUNT(*) as count FROM grocery_items WHERE category = ?";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$category]);
    $countResult = $countStmt->fetch();
    $biscuitCount = $countResult['count'];
    
    echo "<p>Records in '$category' category: $biscuitCount</p>";
    
    // Demo transaction: rolled back immediately so the demo never mutates live data
    $pdo->beginTransaction();
    
    try {
        $bulkUpdateSql = "UPDATE grocery_items 
                          SET price_aud = ROUND(price_aud * ?, 2),
                              stock_quantity = stock_quantity + 10
                          WHERE category = ?";
        
        $bulkUpdateStmt = $pdo->prepare($bulkUpdateSql);
        $bulkUpdateStmt->execute([$percentageIncrease, $category]);
        $bulkAffected = $bulkUpdateStmt->rowCount();
        
        $pdo->rollBack();
        
        echo "<div style='color: blue;'>";
        echo "<p><strong>Transaction Rolled Back (Demo)</strong></p>";
        echo "<p>Would have updated $bulkAffected records in '$category' category</p>";
        echo "<p>Price increase: 10%, Stock increase: +10 units</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div style='color: red;'>";
        echo "<p>Transaction Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
    
    echo "<h3>Example 3: UPDATE with CASE Statement (Conditional Logic)</h3>";
    
    echo "<p>Apply different price adjustments based on category:</p>";
    echo "<pre>";
    echo "UPDATE grocery_items 
SET price_aud = CASE 
    WHEN category = 'Biscuits' THEN ROUND(price_aud * 1.05, 2)
    WHEN category = 'Chocolate' THEN ROUND(price_aud * 1.08, 2)
    WHEN category = 'Beverages' THEN ROUND(price_aud * 0.95, 2)
    ELSE price_aud
END";
    echo "</pre>";
    
    echo "<h3>Example 4: UPDATE Using Subquery</h3>";
    
    echo "<p>Update stock based on category average price:</p>";
    echo "<pre>";
    echo "UPDATE grocery_items g
SET stock_quantity = CASE 
    WHEN g.price_aud > (SELECT AVG(price_aud) FROM grocery_items WHERE category = g.category)
    THEN stock_quantity + 20
    ELSE stock_quantity + 5
END";
    echo "</pre>";
    
    echo "<h3>Example 5: Safe UPDATE Pattern</h3>";
    
    function safeUpdate($pdo, $itemId, $newData) {
        // Column names cannot be bound as placeholders, so whitelist them here
        // or arbitrary keys would be spliced straight into the SQL
        $allowedFields = ['item_name', 'brand_name', 'category', 'price_aud', 'stock_quantity'];
        
        $updateFields = [];
        $params = ['id' => $itemId];
        
        foreach ($newData as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updateFields[] = "$key = :$key";
                $params[$key] = $value;
            }
        }
        
        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        
        $verifySql = "SELECT item_id, item_name FROM grocery_items WHERE item_id = :id";
        $verifyStmt = $pdo->prepare($verifySql);
        $verifyStmt->execute(['id' => $itemId]);
        $record = $verifyStmt->fetch();
        
        if (!$record) {
            return ['success' => false, 'message' => "Record ID $itemId not found"];
        }
        
        $sql = "UPDATE grocery_items SET " . implode(', ', $updateFields) . " WHERE item_id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        
        if ($result) {
            return [
                'success' => true, 
                'message' => 'Record updated successfully',
                'affected_rows' => $stmt->rowCount(),
                'original' => $record
            ];
        }
        
        return ['success' => false, 'message' => 'Update failed'];
    }
    
    $safeResult = safeUpdate($pdo, 2, [
        'price_aud' => 4.20,
        'stock_quantity' => 130,
        'invalid_field' => 'this will be ignored'
    ]);
    
    echo "<pre>";
    print_r($safeResult);
    echo "</pre>";
    
    echo "<h3>Example 6: UPDATE with RETURNING Clause (MySQL 8.0.20+)</h3>";
    
    echo "<p>MySQL 8.0.20+ supports RETURNING to get updated values:</p>";
    echo "<pre>";
    echo "UPDATE grocery_items 
SET price_aud = price_aud * 1.1
WHERE category = 'Chocolate'
RETURNING item_id, item_name, price_aud AS new_price;";
    echo "</pre>";
    
    $versionSql = "SELECT VERSION()";
    $versionStmt = $pdo->prepare($versionSql);
    $versionStmt->execute();
    $version = $versionStmt->fetchColumn();
    echo "<p>MySQL Version: $version</p>";
    
    echo "<h3>Example 7: Batch Update Multiple Records</h3>";
    
    $batchUpdates = [
        ['id' => 3, 'price_aud' => 4.00, 'stock_quantity' => 110],
        ['id' => 4, 'price_aud' => 3.50, 'stock_quantity' => 210],
        ['id' => 5, 'price_aud' => 3.20, 'stock_quantity' => 190]
    ];
    
    // Wrapped in a transaction so the demo shows what rowCount() reports, then discards it
    $pdo->beginTransaction();
    
    try {
        $batchSql = "UPDATE grocery_items SET price_aud = ?, stock_quantity = ? WHERE item_id = ?";
        $batchStmt = $pdo->prepare($batchSql);
        
        $successCount = 0;
        foreach ($batchUpdates as $update) {
            if ($batchStmt->execute([$update['price_aud'], $update['stock_quantity'], $update['id']])) {
                $successCount += $batchStmt->rowCount();
            }
        }
        
        $pdo->rollBack();
        
        echo "<div style='color: blue;'>";
        echo "<p><strong>Batch Update Demo (Rolled Back)</strong></p>";
        echo "<p>Would update $successCount records</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div style='color: red;'>";
        echo "<p>Batch Update Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
    
    echo "<h3>Current State of First 5 Records</h3>";
    $finalSql = "SELECT item_id, item_name, brand_name, category, price_aud, stock_quantity FROM grocery_items ORDER BY item_id LIMIT 5";
    $finalStmt = $pdo->prepare($finalSql);
    $finalStmt->execute();
    $finalRecords = $finalStmt->fetchAll();
    
    echo "<pre>";
    print_r($finalRecords);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>";
    echo "<h3>Database Error:</h3>";
    echo "<pre>";
    print_r($e->getMessage());
    echo "</pre>";
    echo "</div>";
}