<?php
// controllers/medecinController.php

require_once __DIR__ . '/../models/Medecin.php';
require_once __DIR__ . '/../models/RendezVous.php';
require_once __DIR__ . '/../models/Avis.php';
require_once __DIR__ . '/../controllers/authController.php';

class MedecinController {
    private Medecin $medecinModel;
    private RendezVous $rdvModel;
    private Avis $avisModel;

    public function __construct() {
        $this->medecinModel = new Medecin();
        $this->rdvModel     = new RendezVous();
        $this->avisModel    = new Avis();
    }

    public function rechercher(): void {
        $query     = trim($_GET['q'] ?? '');
        $specialite = $_GET['specialite'] ?? '';
        $ville     = trim($_GET['ville'] ?? '');
        $medecins  = $this->medecinModel->search($query, $specialite, $ville);
        $specialites = $this->medecinModel->getSpecialites();
        $villes    = $this->medecinModel->getVilles();
        include __DIR__ . '/../views/patient/rechercher_medecin.php';
    }

    public function profil(): void {
        $id      = (int)($_GET['id'] ?? 0);
        $medecin = $this->medecinModel->findById($id);
        if (!$medecin) { header('Location: ' . APP_URL . '/dashboard.php'); exit; }
        $avis    = $this->avisModel->findByMedecin($id);
        $statsAvis = $this->avisModel->getStats($id);
        include __DIR__ . '/../views/patient/profil_medecin.php';
    }

    public function planning(): void {
        AuthController::requireAuth('medecin');
        $medecinId = $_SESSION['medecin_id'];
        $semaine   = $_GET['semaine'] ?? date('Y-W');
        [$annee, $sem] = explode('-', $semaine);
        $debutSemaine  = new DateTime();
        $debutSemaine->setISODate((int)$annee, (int)$sem, 1);
        $rdvs = $this->rdvModel->findByMedecin(
            $medecinId,
            $debutSemaine->format('Y-m-d'),
            (clone $debutSemaine)->modify('+6 days')->format('Y-m-d')
        );
        $stats = $this->rdvModel->statsMedecin($medecinId);
        include __DIR__ . '/../views/medecin/planning.php';
    }

    public function rdvMedecin(): void {
        AuthController::requireAuth('medecin');
        $medecinId = $_SESSION['medecin_id'];
        $statut    = $_GET['statut'] ?? '';
        $rdvs      = $this->rdvModel->findByMedecin($medecinId);
        if ($statut) {
            $rdvs = array_filter($rdvs, fn($r) => $r['statut'] === $statut);
        }
        $stats = $this->rdvModel->statsMedecin($medecinId);
        include __DIR__ . '/../views/medecin/rdv.php';
    }
}