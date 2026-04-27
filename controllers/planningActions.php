<?php
// controllers/planningActions.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/planningController.php';

$controller = new PlanningController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'ajouter':
        $controller->ajouter();
        break;
    
    case 'supprimer':
        $controller->supprimer();
        break;
    
    default:
        $controller->index();
        break;
}
?>