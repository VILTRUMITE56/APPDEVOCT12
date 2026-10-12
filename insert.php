<?php

require_once 'dbconfig.php';
require_once 'navbar.php';

try {
    $pdo = getPDO();
    
    echo "<h2>PDO Insert Record Demonstration</h2>";
    
    echo "<h3>Example 1: INSERT with Named Placeholders</h3>";
    
    $sql = "INSERT INTO grocery_items (item_name, brand_name, category, price_aud, stock_quantity) 
            VALUES (:item_name, :brand_name, :category, :price_aud, :stock_quantity)";
    
    $stmt = $pdo->prepare($sql);
    
    $newItem = [
        ':item_name'     => 'Tim Tam Chewy Caramel',
        ':brand_name'    => 'Arnott\'s',
        ':category'      => 'Biscuits',
        ':price_aud'     => 3.90,
        ':stock_quantity'=> 100
    ];
    
    $result = $stmt->execute($newItem);
    
    if ($result) {
        $newId = $pdo->lastInsertId();
        echo "<div style='color: green;'>";
        echo "<p><strong>Success!</strong> New record inserted with ID: $newId</p>";
        echo "</div>";
    } else {
        echo "<div style='color: red;'><p>Insert failed</p></div>";
    }
    
    echo "<h3>Example 2: INSERT with Positional Placeholders</h3>";
    
    $sql2 = "INSERT INTO grocery_items (item_name, brand_name, category, price_aud, stock_quantity) 
             VALUES (?, ?, ?, ?, ?)";
    
    $stmt2 = $pdo->prepare($sql2);
    
    $anotherItem = [
        'Vegemite 400g',
        'Bega',
        'Spreads',
        9.50,
        50
    ];
    
    $result2 = $stmt2->execute($anotherItem);
    
    if ($result2) {
        $newId2 = $pdo->lastInsertId();
        echo "<div style='color: green;'>";
        echo "<p><strong>Success!</strong> New record inserted with ID: $newId2</p>";
        echo "</div>";
    } else {
        echo "<div style='color: red;'><p>Insert failed</p></div>";
    }
    
    echo "<h3>Example 3: Multiple INSERTs in a Transaction</h3>";
    
    // All-or-nothing batch insert: anything failing rolls back the whole group
    $pdo->beginTransaction();
    
    try {
        $sql3 = "INSERT INTO grocery_items (item_name, brand_name, category, price_aud, stock_quantity) 
                 VALUES (?, ?, ?, ?, ?)";
        $stmt3 = $pdo->prepare($sql3);
        
        $batchItems = [
            ['Bundaberg Root Beer 375ml', 'Bundaberg', 'Beverages', 2.80, 120],
            ['Milo Nuggets 300g', 'Nestlé Australia', 'Snacks', 4.50, 80],
            ['Shapes Salt & Vinegar', 'Arnott\'s', 'Snacks', 3.00, 150],
            ['Coon Cheese Tasty 250g', 'Bega', 'Dairy', 4.80, 90],
            ['Golden Circle Pineapple 440g', 'Golden Circle', 'Pantry', 2.80, 200]
        ];
        
        $insertedCount = 0;
        foreach ($batchItems as $item) {
            if ($stmt3->execute($item)) {
                $insertedCount++;
            }
        }
        
        $pdo->commit();
        
        echo "<div style='color: green;'>";
        echo "<p><strong>Transaction Committed!</strong> $insertedCount records inserted successfully.</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div style='color: red;'>";
        echo "<p><strong>Transaction Rolled Back!</strong> Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
    
    echo "<h3>Verification: Recently Inserted Records</h3>";
    
    // Narrow to just the rows this page added rather than dumping the whole table
    $verifySql = "SELECT item_id, item_name, brand_name, category, price_aud, stock_quantity 
                  FROM grocery_items 
                  WHERE item_id > (SELECT MAX(item_id) FROM grocery_items) - 7
                  ORDER BY item_id";
    
    $verifyStmt = $pdo->prepare($verifySql);
    $verifyStmt->execute();
    $newRecords = $verifyStmt->fetchAll();
    
    echo "<pre>";
    print_r($newRecords);
    echo "</pre>";
    
    echo "<h3>Example 5: INSERT with Error Handling</h3>";
    
    $duplicateSql = "INSERT INTO grocery_items (item_name, brand_name, category, price_aud, stock_quantity) 
                     VALUES (?, ?, ?, ?, ?)";
    $duplicateStmt = $pdo->prepare($duplicateSql);
    
    echo "<p>Error handling is implemented via try-catch blocks around execute().</p>";
    echo "<p>In production, check error codes: \$stmt->errorCode() and \$stmt->errorInfo()</p>";
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>";
    echo "<h3>Database Error:</h3>";
    echo "<pre>";
    print_r($e->getMessage());
    echo "</pre>";
    echo "</div>";
}