<?php
/**
 * Fetch All Records Demonstration
 * 
 * This file demonstrates the PDO fetchAll() method which retrieves all rows
 * from a result set at once and returns them as an array.
 * 
 * Uses print_r() with <pre> tags for formatted output.
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
    
    // Prepare SQL query - select all records from grocery_items table
    // Using prepared statement for security (though no user input here)
    $sql = "SELECT item_id, item_name, brand_name, category, price_aud, stock_quantity 
            FROM grocery_items 
            ORDER BY item_id";
    
    // Prepare the statement
    $stmt = $pdo->prepare($sql);
    
    // Execute the query
    $stmt->execute();
    
    // fetchAll() retrieves ALL rows at once as an array of arrays
    // Each row is an associative array (due to FETCH_ASSOC default)
    $results = $stmt->fetchAll();
    
    // Display results using print_r() wrapped in <pre> tags for readable formatting
    // <pre> preserves whitespace and line breaks for clean output
    echo "<h2>PDO fetchAll() Demonstration</h2>";
    echo "<p>Total records retrieved: " . count($results) . "</p>";
    echo "<pre>";
    print_r($results);
    echo "</pre>";
    
    // Alternative fetchAll() modes demonstration:
    echo "<h3>Alternative fetchAll() Modes:</h3>";
    
    // FETCH_COLUMN - returns a single column from all rows
    $stmt2 = $pdo->prepare("SELECT item_name FROM grocery_items LIMIT 5");
    $stmt2->execute();
    $names = $stmt2->fetchAll(PDO::FETCH_COLUMN);
    echo "<h4>FETCH_COLUMN (item_name only):</h4>";
    echo "<pre>";
    print_r($names);
    echo "</pre>";
    
    // FETCH_KEY_PAIR - returns key=>value pairs (first column = key, second = value)
    $stmt3 = $pdo->prepare("SELECT item_id, item_name FROM grocery_items LIMIT 5");
    $stmt3->execute();
    $keyPairs = $stmt3->fetchAll(PDO::FETCH_KEY_PAIR);
    echo "<h4>FETCH_KEY_PAIR (item_id => item_name):</h4>";
    echo "<pre>";
    print_r($keyPairs);
    echo "</pre>";
    
    // FETCH_CLASS - returns instances of a specified class
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
    
    $stmt4 = $pdo->prepare("SELECT * FROM grocery_items LIMIT 3");
    $stmt4->execute();
    $objects = $stmt4->fetchAll(PDO::FETCH_CLASS, 'GroceryItem');
    echo "<h4>FETCH_CLASS (GroceryItem objects):</h4>";
    echo "<pre>";
    print_r($objects);
    echo "</pre>";
    
} catch (PDOException $e) {
    // Handle any database errors
    echo "<div style='color: red;'>";
    echo "<h3>Database Error:</h3>";
    echo "<pre>";
    print_r($e->getMessage());
    echo "</pre>";
    echo "</div>";
}