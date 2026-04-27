<?php
// controllers/planningController.php

require_once __DIR__ . '/../models/Disponibilite.php';
require_once __DIR__ . '/../controllers/authController.php';

class PlanningController {
    private Disponibilite $dispoModel;

    public function __construct() {
        $this->dispoModel = new Disponibilite();
    }

    public function index(): void {
        AuthController::requireAuth('medecin');
        $medecinId     = $_SESSION['medecin_id'];
        $disponibilites = $this->dispoModel->findByMedecin($medecinId);
        include __DIR__ . '/../views/medecin/disponibilite.php';
    }

    public function ajouter(): void {
        AuthController::requireAuth('medecin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->index(); return; }

        $errors = [];
        $jourSemaine = (int)($_POST['jour_semaine'] ?? -1);
        $heureDebut  = $_POST['heure_debut'] ?? '';
        $heureFin    = $_POST['heure_fin'] ?? '';
        $dureeRdv    = (int)($_POST['duree_rdv'] ?? 30);

        if ($jourSemaine < 0 || $jourSemaine > 6) $errors[] = "Jour invalide.";
        if (empty($heureDebut) || empty($heureFin)) $errors[] = "Heures requises.";
        if ($heureDebut >= $heureFin) $errors[] = "L'heure de fin doit être après l'heure de début.";
        if (!in_array($dureeRdv, [15, 20, 30, 45, 60])) $errors[] = "Durée invalide.";

        if (empty($errors)) {
            $this->dispoModel->create([
                'medecin_id'   => $_SESSION['medecin_id'],
                'jour_semaine' => $jourSemaine,
                'heure_debut'  => $heureDebut,
                'heure_fin'    => $heureFin,
                'duree_rdv'    => $dureeRdv,
            ]);
            $_SESSION['flash'] = "Disponibilité ajoutée.";
        } else {
            $_SESSION['flash_error'] = implode(' ', $errors);
        }

        header('Location: ' . APP_URL . '/views/medecin/disponibilite.php');
        exit;
    }

    public function supprimer(): void {
        AuthController::requireAuth('medecin');
        $id = (int)($_POST['dispo_id'] ?? 0);
        $this->dispoModel->delete($id, $_SESSION['medecin_id']);
        $_SESSION['flash'] = "Disponibilité supprimée.";
        header('Location: ' . APP_URL . '/views/medecin/disponibilite.php');
        exit;
    }
}