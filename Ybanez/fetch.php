<?php

require_once 'dbconfig.php';
require_once 'navbar.php';

try {
    $pdo = getPDO();
    
    $sql = "SELECT item_id, item_name, brand_name, category, price_aud, stock_quantity 
            FROM grocery_items 
            WHERE category = 'Biscuits' 
            ORDER BY item_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    echo "<h2>PDO fetch() Demonstration</h2>";
    echo "<p>Fetching rows ONE AT A TIME using fetch() in a loop</p>";
    
    $rowCount = 0;
    
    echo "<h3>Looping through results with fetch():</h3>";
    echo "<pre>";
    
    // fetch() advances the cursor one row at a time; it returns false when exhausted
    while ($row = $stmt->fetch()) {
        $rowCount++;
        echo "--- Row $rowCount ---\n";
        print_r($row);
        echo "\n";
    }
    
    echo "</pre>";
    echo "<p><strong>Total rows fetched: $rowCount</strong></p>";
    
    echo "<h3>Different fetch() Modes:</h3>";
    
    $stmt2 = $pdo->prepare("SELECT item_name, brand_name, price_aud FROM grocery_items WHERE category = 'Chocolate' LIMIT 3");
    $stmt2->execute();
    echo "<h4>FETCH_ASSOC (default):</h4>";
    echo "<pre>";
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
    
    $stmt3 = $pdo->prepare("SELECT item_name, brand_name, price_aud FROM grocery_items WHERE category = 'Chocolate' LIMIT 3");
    $stmt3->execute();
    echo "<h4>FETCH_NUM (numeric indices):</h4>";
    echo "<pre>";
    while ($row = $stmt3->fetch(PDO::FETCH_NUM)) {
        print_r($row);
    }
    echo "</pre>";
    
    $stmt4 = $pdo->prepare("SELECT item_name, brand_name, price_aud FROM grocery_items WHERE category = 'Chocolate' LIMIT 3");
    $stmt4->execute();
    echo "<h4>FETCH_OBJ (stdClass object):</h4>";
    echo "<pre>";
    while ($obj = $stmt4->fetch(PDO::FETCH_OBJ)) {
        print_r($obj);
    }
    echo "</pre>";
    
    $stmt5 = $pdo->prepare("SELECT item_name, brand_name, price_aud FROM grocery_items WHERE category = 'Chocolate' LIMIT 2");
    $stmt5->execute();
    echo "<h4>FETCH_BOTH (both associative and numeric):</h4>";
    echo "<pre>";
    while ($row = $stmt5->fetch(PDO::FETCH_BOTH)) {
        print_r($row);
    }
    echo "</pre>";
    
    echo "<h3>fetch() with Prepared Statement Parameters:</h3>";
    $category = 'Beverages';
    $minPrice = 2.00;
    
    $stmt6 = $pdo->prepare("SELECT item_name, brand_name, price_aud FROM grocery_items WHERE category = ? AND price_aud >= ? ORDER BY price_aud");
    $stmt6->execute([$category, $minPrice]);
    
    echo "<p>Fetching Beverages with price >= AUD $minPrice:</p>";
    echo "<pre>";
    while ($row = $stmt6->fetch()) {
        print_r($row);
    }
    echo "</pre>";
    
    echo "<h3>fetchColumn() - Single Column from Next Row:</h3>";
    $stmt7 = $pdo->prepare("SELECT item_name FROM grocery_items WHERE category = 'Dairy' LIMIT 5");
    $stmt7->execute();
    
    echo "<pre>";
    $colIndex = 0;
    // Stops on falsy values, so it is not reliable past the first gap in data
    while ($name = $stmt7->fetchColumn($colIndex)) {
        echo "Column $colIndex: $name\n";
    }
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>";
    echo "<h3>Database Error:</h3>";
    echo "<pre>";
    print_r($e->getMessage());
    echo "</pre>";
    echo "</div>";
}