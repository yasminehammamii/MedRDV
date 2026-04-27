<?php
// controllers/rdvActions.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/rdvController.php';

$controller = new RdvController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'annuler':
        $controller->annuler();
        break;
    
    case 'confirmer':
        $controller->confirmer();
        break;
    
    case 'terminer':
        $controller->terminer();
        break;
    
    case 'creneaux':
        $controller->getCrenaux();
        break;
    
    case 'laisser_avis':
        $controller->laisserAvis();
        break;
    
    default:
        http_response_code(404);
        echo "Action non trouvée";
        break;
}
?>