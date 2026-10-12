<?php

require_once 'dbconfig.php';
require_once 'navbar.php';

try {
    $pdo = getPDO();
    
    echo "<h2>PDO Delete Record Demonstration</h2>";
    
    echo "<h3>Example 1: DELETE Single Record by ID</h3>";
    
    $viewSql = "SELECT item_id, item_name, brand_name FROM grocery_items ORDER BY item_id DESC LIMIT 5";
    $viewStmt = $pdo->prepare($viewSql);
    $viewStmt->execute();
    $lastRecords = $viewStmt->fetchAll();
    
    echo "<p>Last 5 records before deletion:</p>";
    echo "<pre>";
    print_r($lastRecords);
    echo "</pre>";
    
    $targetId = $lastRecords[0]['item_id'];
    $targetName = $lastRecords[0]['item_name'];
    
    $deleteSql = "DELETE FROM grocery_items WHERE item_id = ?";
    $deleteStmt = $pdo->prepare($deleteSql);
    
    $result = $deleteStmt->execute([$targetId]);
    
    $affectedRows = $deleteStmt->rowCount();
    
    if ($result && $affectedRows > 0) {
        echo "<div style='color: green;'>";
        echo "<p><strong>Success!</strong> Deleted record ID: $targetId ($targetName)</p>";
        echo "<p>Affected rows: $affectedRows</p>";
        echo "</div>";
    } else {
        echo "<div style='color: orange;'>";
        echo "<p>No record found with ID: $targetId (affected rows: $affectedRows)</p>";
        echo "</div>";
    }
    
    echo "<h3>Example 2: DELETE Multiple Records with Condition</h3>";
    
    $checkSql = "SELECT COUNT(*) as count FROM grocery_items WHERE stock_quantity = 0";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute();
    $countResult = $checkStmt->fetch();
    $zeroStockCount = $countResult['count'];
    
    echo "<p>Records with zero stock: $zeroStockCount</p>";
    
    if ($zeroStockCount > 0) {
        $deleteMultiSql = "DELETE FROM grocery_items WHERE stock_quantity = 0";
        $deleteMultiStmt = $pdo->prepare($deleteMultiSql);
        $deleteMultiStmt->execute();
        $deletedCount = $deleteMultiStmt->rowCount();
        
        echo "<div style='color: green;'>";
        echo "<p><strong>Success!</strong> Deleted $deletedCount out-of-stock records.</p>";
        echo "</div>";
    } else {
        echo "<p>No out-of-stock records to delete.</p>";
    }
    
    echo "<h3>Example 3: DELETE with Transaction (Rollback Demo)</h3>";
    
    // Demo transaction: rolled back so the destructive delete never commits
    $pdo->beginTransaction();
    
    try {
        $deleteTransSql = "DELETE FROM grocery_items WHERE category = ? AND price_aud > ?";
        $deleteTransStmt = $pdo->prepare($deleteTransSql);
        $deleteTransStmt->execute(['Snacks', 4.00]);
        $transAffected = $deleteTransStmt->rowCount();
        
        $pdo->rollBack();
        
        echo "<div style='color: blue;'>";
        echo "<p><strong>Transaction Rolled Back (Demo)</strong></p>";
        echo "<p>Would have deleted $transAffected records from Snacks category with price > 4.00</p>";
        echo "<p>Rollback preserves data integrity for demonstration purposes.</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div style='color: red;'>";
        echo "<p>Transaction Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
    
    echo "<h3>Example 4: DELETE Using Subquery/Join</h3>";
    
    echo "<p>Advanced DELETE with subquery example:</p>";
    echo "<pre>";
    echo "DELETE FROM grocery_items 
WHERE brand_name NOT IN (SELECT brand_name FROM approved_brands)";
    echo "</pre>";
    
    echo "<h3>Example 5: Safe DELETE Pattern (Verify First)</h3>";
    
    function safeDelete($pdo, $itemId) {
        // Read the row first so the caller can see exactly what is about to go away
        $verifySql = "SELECT item_id, item_name, brand_name FROM grocery_items WHERE item_id = ?";
        $verifyStmt = $pdo->prepare($verifySql);
        $verifyStmt->execute([$itemId]);
        $record = $verifyStmt->fetch();
        
        if (!$record) {
            return ['success' => false, 'message' => "Record ID $itemId not found"];
        }
        
        echo "<p>About to delete: {$record['item_name']} ({$record['brand_name']}) [ID: {$record['item_id']}]</p>";
        
        $deleteSql = "DELETE FROM grocery_items WHERE item_id = ?";
        $deleteStmt = $pdo->prepare($deleteSql);
        $result = $deleteStmt->execute([$itemId]);
        
        if ($result && $deleteStmt->rowCount() > 0) {
            return ['success' => true, 'message' => "Record deleted successfully", 'deleted' => $record];
        }
        
        return ['success' => false, 'message' => 'Delete failed'];
    }
    
    $safeResult = safeDelete($pdo, 999999);
    echo "<pre>";
    print_r($safeResult);
    echo "</pre>";
    
    echo "<h3>Verification: Records After Deletion Operations</h3>";
    $finalSql = "SELECT COUNT(*) as total FROM grocery_items";
    $finalStmt = $pdo->prepare($finalSql);
    $finalStmt->execute();
    $finalCount = $finalStmt->fetch();
    echo "<p>Total records in table: {$finalCount['total']}</p>";
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>";
    echo "<h3>Database Error:</h3>";
    echo "<pre>";
    print_r($e->getMessage());
    echo "</pre>";
    echo "</div>";
}