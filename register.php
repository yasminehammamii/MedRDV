<?php
// register.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/authController.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/dashboard.php'); exit; }
$ctrl = new AuthController();
$ctrl->register();