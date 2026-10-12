<?php

// XAMPP defaults; DB_PASS is empty only for local development
define('DB_HOST', 'localhost');
define('DB_NAME', 'grocery_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Real prepared statements stop MySQL from doing client-side interpolation
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
];

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdoOptions);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Accessor so other files do not have to declare the global themselves
function getPDO() {
    global $pdo;
    return $pdo;
}