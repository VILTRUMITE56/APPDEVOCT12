<?php
/**
 * Insert Record Demonstration
 * 
 * This file demonstrates inserting new records into the database
 * using PDO prepared statements for security against SQL injection.
 * 
 * @package GroceryItemsPDO
 * @author Student
 * @version 1.0
 */

// Include database configuration
require_once 'dbconfig.php';

try {
    // Get PDO instance
    $pdo = getPDO();
    
    echo "<h2>PDO Insert Record Demonstration</h2>";
    
    // Example 1: Basic INSERT with prepared statement using named placeholders
    echo "<h3>Example 1: INSERT with Named Placeholders</h3>";
    
    // SQL with named placeholders (:placeholder_name)
    $sql = "INSERT INTO grocery_items (item_name, brand_name, category, price_aud, stock_quantity) 
            VALUES (:item_name, :brand_name, :category, :price_aud, :stock_quantity)";
    
    // Prepare the statement
    $stmt = $pdo->prepare($sql);
    
    // Data to insert - using Australian brand
    $newItem = [
        ':item_name'     => 'Tim Tam Chewy Caramel',
        ':brand_name'    => 'Arnott\'s',
        ':category'      => 'Biscuits',
        ':price_aud'     => 3.90,
        ':stock_quantity'=> 100
    ];
    
    // Execute with array of values - automatically binds by key name
    $result = $stmt->execute($newItem);
    
    if ($result) {
        // lastInsertId() returns the auto-increment ID of the inserted row
        $newId = $pdo->lastInsertId();
        echo "<div style='color: green;'>";
        echo "<p><strong>Success!</strong> New record inserted with ID: $newId</p>";
        echo "</div>";
    } else {
        echo "<div style='color: red;'><p>Insert failed</p></div>";
    }
    
    // Example 2: INSERT with positional placeholders (?)
    echo "<h3>Example 2: INSERT with Positional Placeholders</h3>";
    
    $sql2 = "INSERT INTO grocery_items (item_name, brand_name, category, price_aud, stock_quantity) 
             VALUES (?, ?, ?, ?, ?)";
    
    $stmt2 = $pdo->prepare($sql2);
    
    // Data for another Australian product
    $anotherItem = [
        'Vegemite 400g',
        'Bega',
        'Spreads',
        9.50,
        50
    ];
    
    // Execute with positional array (order matters!)
    $result2 = $stmt2->execute($anotherItem);
    
    if ($result2) {
        $newId2 = $pdo->lastInsertId();
        echo "<div style='color: green;'>";
        echo "<p><strong>Success!</strong> New record inserted with ID: $newId2</p>";
        echo "</div>";
    } else {
        echo "<div style='color: red;'><p>Insert failed</p></div>";
    }
    
    // Example 3: INSERT multiple records in a transaction
    echo "<h3>Example 3: Multiple INSERTs in a Transaction</h3>";
    
    // Begin transaction - all queries succeed or all fail (atomicity)
    $pdo->beginTransaction();
    
    try {
        $sql3 = "INSERT INTO grocery_items (item_name, brand_name, category, price_aud, stock_quantity) 
                 VALUES (?, ?, ?, ?, ?)";
        $stmt3 = $pdo->prepare($sql3);
        
        // Batch of new Australian products to insert
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
        
        // Commit transaction - make all changes permanent
        $pdo->commit();
        
        echo "<div style='color: green;'>";
        echo "<p><strong>Transaction Committed!</strong> $insertedCount records inserted successfully.</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        // Rollback transaction on error - undo all changes
        $pdo->rollBack();
        echo "<div style='color: red;'>";
        echo "<p><strong>Transaction Rolled Back!</strong> Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
    
    // Example 4: Verify inserts by querying the new records
    echo "<h3>Verification: Recently Inserted Records</h3>";
    
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
    
    // Example 5: INSERT with error handling for duplicates
    echo "<h3>Example 5: INSERT with Error Handling</h3>";
    
    // Try to insert a duplicate (will fail if unique constraint exists, or just show error handling)
    $duplicateSql = "INSERT INTO grocery_items (item_name, brand_name, category, price_aud, stock_quantity) 
                     VALUES (?, ?, ?, ?, ?)";
    $duplicateStmt = $pdo->prepare($duplicateSql);
    
    // Intentionally cause an error (e.g., invalid price format) to show error handling
    // Uncomment next line to test error handling:
    // $duplicateStmt->execute(['Test Item', 'Test Brand', 'Test', 'invalid_price', 10]);
    
    echo "<p>Error handling is implemented via try-catch blocks around execute().</p>";
    echo "<p>In production, check error codes: \$stmt->errorCode() and \$stmt->errorInfo()</p>";
    
} catch (PDOException $e) {
    // Handle any database errors
    echo "<div style='color: red;'>";
    echo "<h3>Database Error:</h3>";
    echo "<pre>";
    print_r($e->getMessage());
    echo "</pre>";
    echo "</div>";
}