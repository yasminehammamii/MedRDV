<?php
// views/shared/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';

$flash      = $_SESSION['flash']       ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_error']);

$role     = $_SESSION['user_role'] ?? null;
$userName = $_SESSION['user_nom']  ?? null;
$isLogged = !empty($_SESSION['user_id']);

$currentFile = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'MedRDV' ?> — MedRDV</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="<?= APP_URL ?>" class="navbar-brand">
        <span class="brand-icon">⚕</span>
        <span>Med<strong>RDV</strong></span>
    </a>

    <div class="navbar-links">
        <?php if ($isLogged): ?>
            <?php if ($role === 'patient'): ?>
                <a href="<?= APP_URL ?>/views/patient/rechercher_medecin.php" class="nav-link">Rechercher</a>
                <a href="<?= APP_URL ?>/views/patient/mes_rdv.php" class="nav-link">Mes RDV</a>
            <?php elseif ($role === 'medecin'): ?>
                <a href="<?= APP_URL ?>/views/medecin/rdv.php" class="nav-link">Rendez-vous</a>
                <a href="<?= APP_URL ?>/views/medecin/planning.php" class="nav-link">Planning</a>
                <a href="<?= APP_URL ?>/views/medecin/disponibilite.php" class="nav-link">Disponibilités</a>
            <?php endif; ?>
            <a href="<?= APP_URL ?>/dashboard.php" class="nav-link">Tableau de bord</a>
            <div class="nav-user">
                <span class="user-name"><?= htmlspecialchars($userName) ?></span>
                <a href="<?= APP_URL ?>/logout.php" class="btn-logout">Déconnexion</a>
            </div>
        <?php else: ?>
            <a href="<?= APP_URL ?>/login.php" class="nav-link">Connexion</a>
            <a href="<?= APP_URL ?>/register.php" class="btn-primary-sm">S'inscrire</a>
        <?php endif; ?>
    </div>
</nav>

<main class="main-content">
<?php if ($flash): ?>
    <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>