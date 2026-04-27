<?php
// logout.php
require_once __DIR__ . '/config/db.php';
session_start();

// Destroy all session data
$_SESSION = [];

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to homepage
header('Location: ' . APP_URL . '/');
exit;
?>