<?php
// Template for local database configuration.
// Copy this file to dbconfig.php and fill in your own values:
//     cp dbconfig.example.php dbconfig.php
// dbconfig.php itself is gitignored so real credentials are never committed.

define('DB_HOST', 'localhost');
define('DB_NAME', 'grocery_db');
define('DB_USER', 'your_db_user_here');
define('DB_PASS', 'your_db_password_here');
define('DB_CHARSET', 'utf8mb4');

$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
];

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdoOptions);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

function getPDO() {
    global $pdo;
    return $pdo;
}
