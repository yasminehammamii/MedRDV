<?php
// config/db.php — Connexion PDO à la base de données

define('DB_HOST', 'localhost');
define('DB_NAME', 'medrdv');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'MedRDV');
define('APP_URL', 'http://localhost/medrdv');
define('APP_VERSION', '1.0.0');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Connexion base de données impossible: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}