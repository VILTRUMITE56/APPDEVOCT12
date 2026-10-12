<?php

require_once 'dbconfig.php';
require_once 'navbar.php';

try {
    $pdo = getPDO();
    
    $sql = "SELECT item_id, item_name, brand_name, category, price_aud, stock_quantity 
            FROM grocery_items 
            ORDER BY item_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $results = $stmt->fetchAll();
    
    echo "<h2>PDO fetchAll() Demonstration</h2>";
    echo "<p>Total records retrieved: " . count($results) . "</p>";
    echo "<pre>";
    print_r($results);
    echo "</pre>";
    
    echo "<h3>Alternative fetchAll() Modes:</h3>";
    
    $stmt2 = $pdo->prepare("SELECT item_name FROM grocery_items LIMIT 5");
    $stmt2->execute();
    $names = $stmt2->fetchAll(PDO::FETCH_COLUMN);
    echo "<h4>FETCH_COLUMN (item_name only):</h4>";
    echo "<pre>";
    print_r($names);
    echo "</pre>";
    
    $stmt3 = $pdo->prepare("SELECT item_id, item_name FROM grocery_items LIMIT 5");
    $stmt3->execute();
    $keyPairs = $stmt3->fetchAll(PDO::FETCH_KEY_PAIR);
    echo "<h4>FETCH_KEY_PAIR (item_id => item_name):</h4>";
    echo "<pre>";
    print_r($keyPairs);
    echo "</pre>";
    
    class GroceryItem {
        public $item_id;
        public $item_name;
        public $brand_name;
        public $category;
        public $price_aud;
        public $stock_quantity;
        
        public function __toString() {
            return "{$this->item_name} ({$this->brand_name}) - AUD \${$this->price_aud}";
        }
    }
    
    // FETCH_CLASS maps columns onto these public properties by name
    $stmt4 = $pdo->prepare("SELECT * FROM grocery_items LIMIT 3");
    $stmt4->execute();
    $objects = $stmt4->fetchAll(PDO::FETCH_CLASS, 'GroceryItem');
    echo "<h4>FETCH_CLASS (GroceryItem objects):</h4>";
    echo "<pre>";
    print_r($objects);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>";
    echo "<h3>Database Error:</h3>";
    echo "<pre>";
    print_r($e->getMessage());
    echo "</pre>";
    echo "</div>";
}