<?php
// views/medecin/planning.php
$pageTitle = 'Planning Hebdomadaire';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/authController.php';
AuthController::requireAuth('medecin');
require_once __DIR__ . '/../../models/RendezVous.php';

$rdvModel  = new RendezVous();
$medecinId = $_SESSION['medecin_id'];

// Semaine courante
$semaine      = $_GET['semaine'] ?? date('Y-\WW');
[$annee, $sem] = explode('-W', $semaine);
$debutSemaine  = new DateTime();
$debutSemaine->setISODate((int)$annee, (int)$sem, 1);
$finSemaine    = (clone $debutSemaine)->modify('+6 days');

$rdvs  = $rdvModel->findByMedecin($medecinId, $debutSemaine->format('Y-m-d'), $finSemaine->format('Y-m-d'));
$stats = $rdvModel->statsMedecin($medecinId);

// Indexer par date
$rdvParJour = [];
foreach ($rdvs as $rdv) {
    $rdvParJour[$rdv['date_rdv']][] = $rdv;
}

// Navigation semaines
$semPrev = (clone $debutSemaine)->modify('-7 days')->format('Y-\WW');
$semNext = (clone $debutSemaine)->modify('+7 days')->format('Y-\WW');

$jours = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

require_once __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <h1>Planning de la semaine</h1>
    <a href="<?= APP_URL ?>/views/medecin/rdv.php" class="btn-outline">Voir liste</a>
</div>

<!-- Navigation semaine -->
<div class="week-nav">
    <a href="?semaine=<?= $semPrev ?>" class="week-nav-btn">← Semaine précédente</a>
    <div class="week-nav-title">
        <strong><?= $debutSemaine->format('d/m') ?> — <?= $finSemaine->format('d/m/Y') ?></strong>
        <span class="week-num">Semaine <?= $sem ?></span>
    </div>
    <a href="?semaine=<?= $semNext ?>" class="week-nav-btn">Semaine suivante →</a>
</div>

<!-- Grille planning -->
<div class="planning-grid">
    <?php
    $today = date('Y-m-d');
    for ($i = 0; $i < 7; $i++):
        $jourDate = (clone $debutSemaine)->modify("+$i days");
        $dateStr  = $jourDate->format('Y-m-d');
        $isToday  = ($dateStr === $today);
        $joursRdv = $rdvParJour[$dateStr] ?? [];
    ?>
        <div class="planning-day <?= $isToday ? 'planning-day--today' : '' ?>">
            <div class="planning-day-header">
                <span class="day-name"><?= $jours[$i] ?></span>
                <span class="day-date"><?= $jourDate->format('d/m') ?></span>
                <?php if ($isToday): ?><span class="today-badge">Aujourd'hui</span><?php endif; ?>
            </div>
            <div class="planning-day-body">
                <?php if (empty($joursRdv)): ?>
                    <div class="planning-empty">Aucun RDV</div>
                <?php else: ?>
                    <?php foreach ($joursRdv as $rdv): ?>
                        <div class="planning-rdv planning-rdv--<?= $rdv['statut'] ?>">
                            <span class="planning-heure"><?= substr($rdv['heure_rdv'], 0, 5) ?></span>
                            <span class="planning-patient">
                                <?= htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']) ?>
                            </span>
                            <span class="planning-statut badge badge--<?= $rdv['statut'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $rdv['statut'])) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endfor; ?>
</div>

<!-- Récap semaine -->
<div class="week-summary">
    <h3>Récapitulatif — <?= count($rdvs) ?> rendez-vous cette semaine</h3>
    <div class="week-summary-grid">
        <div class="summary-item">
            <span class="summary-count"><?= count(array_filter($rdvs, fn($r) => $r['statut'] === 'confirme')) ?></span>
            <span>Confirmés</span>
        </div>
        <div class="summary-item">
            <span class="summary-count"><?= count(array_filter($rdvs, fn($r) => $r['statut'] === 'en_attente')) ?></span>
            <span>En attente</span>
        </div>
        <div class="summary-item">
            <span class="summary-count"><?= count(array_filter($rdvs, fn($r) => $r['statut'] === 'annule')) ?></span>
            <span>Annulés</span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>