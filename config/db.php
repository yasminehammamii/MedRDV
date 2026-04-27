<?php
// Application URL
define('APP_URL', 'http://localhost/MedRDV-main');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'medrdv');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create PDO connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Function to get database connection
function getDB() {
    global $pdo;
    return $pdo;
}
?>