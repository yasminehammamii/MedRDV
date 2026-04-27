<?php
// controllers/rdvController.php

require_once __DIR__ . '/../models/RendezVous.php';
require_once __DIR__ . '/../models/Medecin.php';
require_once __DIR__ . '/../models/Avis.php';
require_once __DIR__ . '/../controllers/authController.php';

class RdvController {
    private RendezVous $rdvModel;
    private Medecin $medecinModel;
    private Avis $avisModel;

    public function __construct() {
        $this->rdvModel     = new RendezVous();
        $this->medecinModel = new Medecin();
        $this->avisModel    = new Avis();
    }

    public function prendrRdv(): void {
        AuthController::requireAuth('patient');
        $medecinId = (int)($_GET['medecin_id'] ?? 0);
        $medecin   = $this->medecinModel->findById($medecinId);
        if (!$medecin) { $_SESSION['flash_error'] = "Médecin introuvable."; header('Location: ' . APP_URL . '/views/patient/rechercher_medecin.php'); exit; }

        $date      = $_GET['date'] ?? date('Y-m-d');
        $creneaux  = $this->rdvModel->getCreneauxDisponibles($medecinId, $date);
        $errors    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $motif = trim($_POST['motif'] ?? '');
            $heure = $_POST['heure'] ?? '';
            $dateRdv = $_POST['date_rdv'] ?? '';

            if (empty($heure))   $errors[] = "Veuillez choisir un créneau.";
            if (empty($dateRdv)) $errors[] = "Date invalide.";
            if (strtotime($dateRdv) < strtotime('today')) $errors[] = "La date ne peut pas être dans le passé.";

            if (empty($errors)) {
                try {
                    $this->rdvModel->create([
                        'patient_id' => $_SESSION['patient_id'],
                        'medecin_id' => $medecinId,
                        'date_rdv'   => $dateRdv,
                        'heure_rdv'  => $heure,
                        'motif'      => $motif,
                    ]);
                    $_SESSION['flash'] = "Rendez-vous demandé avec succès !";
                    header('Location: ' . APP_URL . '/views/patient/mes_rdv.php');
                    exit;
                } catch (RuntimeException $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        include __DIR__ . '/../views/patient/prendre_rdv.php';
    }

    public function mesRdv(): void {
        AuthController::requireAuth('patient');
        $rdvs  = $this->rdvModel->findByPatient($_SESSION['patient_id']);
        $stats = $this->rdvModel->statsPatient($_SESSION['patient_id']);
        include __DIR__ . '/../views/patient/mes_rdv.php';
    }

    public function annuler(): void {
        AuthController::requireAuth('patient', 'medecin');
        $id = (int)($_POST['rdv_id'] ?? 0);
        $this->rdvModel->annuler($id, $_SESSION['user_id'], $_SESSION['user_role']);
        $_SESSION['flash'] = "Rendez-vous annulé.";
        header('Location: ' . APP_URL . '/dashboard.php');
        exit;
    }

    public function confirmer(): void {
        AuthController::requireAuth('medecin');
        $id = (int)($_POST['rdv_id'] ?? 0);
        $this->rdvModel->updateStatut($id, 'confirme');
        $_SESSION['flash'] = "Rendez-vous confirmé.";
        header('Location: ' . APP_URL . '/views/medecin/rdv.php');
        exit;
    }

    public function terminer(): void {
        AuthController::requireAuth('medecin');
        $id    = (int)($_POST['rdv_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $this->rdvModel->updateStatut($id, 'termine', $notes);
        $_SESSION['flash'] = "Rendez-vous marqué comme terminé.";
        header('Location: ' . APP_URL . '/views/medecin/rdv.php');
        exit;
    }

    public function getCrenaux(): void {
        // API AJAX: retourne les créneaux dispos en JSON
        header('Content-Type: application/json');
        $medecinId = (int)($_GET['medecin_id'] ?? 0);
        $date      = $_GET['date'] ?? date('Y-m-d');
        echo json_encode($this->rdvModel->getCreneauxDisponibles($medecinId, $date));
    }

    public function laisserAvis(): void {
        AuthController::requireAuth('patient');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . '/dashboard.php'); exit; }
        try {
            $this->avisModel->create([
                'patient_id'  => $_SESSION['patient_id'],
                'medecin_id'  => (int)$_POST['medecin_id'],
                'rdv_id'      => (int)$_POST['rdv_id'] ?: null,
                'note'        => (int)$_POST['note'],
                'commentaire' => trim($_POST['commentaire'] ?? ''),
            ]);
            $_SESSION['flash'] = "Merci pour votre avis !";
        } catch (RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }
        header('Location: ' . APP_URL . '/views/patient/mes_rdv.php');
        exit;
    }
}