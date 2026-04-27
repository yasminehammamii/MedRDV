<?php
// dashboard.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/authController.php';
AuthController::requireAuth();

require_once __DIR__ . '/models/RendezVous.php';
require_once __DIR__ . '/models/Medecin.php';
require_once __DIR__ . '/models/Patient.php';

$role      = $_SESSION['user_role'];
$userName  = $_SESSION['user_nom'];
$rdvModel  = new RendezVous();

$pageTitle = 'Tableau de bord';
require_once __DIR__ . '/views/shared/header.php';

// ====== DASHBOARD PATIENT ======
if ($role === 'patient'):
    $patientId = $_SESSION['patient_id'];
    $rdvs      = $rdvModel->findByPatient($patientId);
    $stats     = $rdvModel->statsPatient($patientId);
    $prochains = array_filter($rdvs, fn($r) => $r['date_rdv'] >= date('Y-m-d') && in_array($r['statut'], ['confirme', 'en_attente']));
    usort($prochains, fn($a, $b) => strcmp($a['date_rdv'] . $a['heure_rdv'], $b['date_rdv'] . $b['heure_rdv']));
?>

<div class="dashboard-welcome">
    <h1>Bonjour, <?= htmlspecialchars($userName) ?> 👋</h1>
    <p>Bienvenue sur votre espace patient.</p>
    <a href="<?= APP_URL ?>/views/patient/rechercher_medecin.php" class="btn-primary">+ Nouveau rendez-vous</a>
</div>

<div class="stats-grid">
    <div class="stat-card"><span class="stat-number"><?= $stats['total'] ?></span><span class="stat-label">Total RDV</span></div>
    <div class="stat-card stat-card--green"><span class="stat-number"><?= $stats['confirmes'] ?></span><span class="stat-label">Confirmés</span></div>
    <div class="stat-card stat-card--yellow"><span class="stat-number"><?= $stats['en_attente'] ?></span><span class="stat-label">En attente</span></div>
    <div class="stat-card stat-card--blue"><span class="stat-number"><?= $stats['termines'] ?></span><span class="stat-label">Terminés</span></div>
</div>

<div class="dashboard-section">
    <div class="dashboard-section-header">
        <h2>Prochains rendez-vous</h2>
        <a href="<?= APP_URL ?>/views/patient/mes_rdv.php">Voir tous →</a>
    </div>
    <?php if (empty($prochains)): ?>
        <div class="empty-state-inline">
            <p>Aucun rendez-vous à venir. <a href="<?= APP_URL ?>/views/patient/rechercher_medecin.php">Prendre un rendez-vous</a></p>
        </div>
    <?php else: ?>
        <div class="rdv-list">
            <?php foreach (array_slice($prochains, 0, 3) as $rdv): ?>
                <div class="rdv-card">
                    <div class="rdv-card-left">
                        <div class="rdv-date">
                            <span class="rdv-day"><?= date('d', strtotime($rdv['date_rdv'])) ?></span>
                            <span class="rdv-month"><?= date('M', strtotime($rdv['date_rdv'])) ?></span>
                        </div>
                        <div class="rdv-time"><?= substr($rdv['heure_rdv'], 0, 5) ?></div>
                    </div>
                    <div class="rdv-card-body">
                        <div class="rdv-medecin">Dr <?= htmlspecialchars($rdv['medecin_prenom'] . ' ' . $rdv['medecin_nom']) ?></div>
                        <div class="rdv-spec"><?= htmlspecialchars($rdv['specialite']) ?></div>
                        <?php if ($rdv['motif']): ?><div class="rdv-motif"><?= htmlspecialchars($rdv['motif']) ?></div><?php endif; ?>
                    </div>
                    <div class="rdv-card-right">
                        <span class="badge badge--<?= $rdv['statut'] ?>"><?= ucfirst(str_replace('_', ' ', $rdv['statut'])) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="dashboard-section">
    <div class="dashboard-section-header">
        <h2>Actions rapides</h2>
    </div>
    <div class="quick-actions">
        <a href="<?= APP_URL ?>/views/patient/rechercher_medecin.php" class="quick-action-card">
            <span class="qa-icon">🔍</span><span>Trouver un médecin</span>
        </a>
        <a href="<?= APP_URL ?>/views/patient/mes_rdv.php" class="quick-action-card">
            <span class="qa-icon">📋</span><span>Mes rendez-vous</span>
        </a>
    </div>
</div>

<?php
// ====== DASHBOARD MÉDECIN ======
elseif ($role === 'medecin'):
    $medecinId = $_SESSION['medecin_id'];
    $stats     = $rdvModel->statsMedecin($medecinId);
    $rdvsAujourdhui = $rdvModel->findByMedecin($medecinId, date('Y-m-d'), date('Y-m-d'));
    $enAttente      = $rdvModel->findByMedecin($medecinId);
    $enAttente      = array_filter($enAttente, fn($r) => $r['statut'] === 'en_attente');
?>

<div class="dashboard-welcome">
    <h1>Bonjour, <?= htmlspecialchars($userName) ?> 👨‍⚕️</h1>
    <p>Bienvenue sur votre espace médecin.</p>
</div>

<div class="stats-grid">
    <div class="stat-card stat-card--accent"><span class="stat-number"><?= $stats['aujourd_hui'] ?? 0 ?></span><span class="stat-label">Aujourd'hui</span></div>
    <div class="stat-card stat-card--yellow"><span class="stat-number"><?= $stats['en_attente'] ?></span><span class="stat-label">En attente</span></div>
    <div class="stat-card stat-card--green"><span class="stat-number"><?= $stats['confirmes'] ?></span><span class="stat-label">Confirmés</span></div>
    <div class="stat-card"><span class="stat-number"><?= $stats['total'] ?></span><span class="stat-label">Total</span></div>
</div>

<div class="dashboard-cols">
    <div class="dashboard-section">
        <div class="dashboard-section-header">
            <h2>Programme du jour</h2>
            <a href="<?= APP_URL ?>/views/medecin/planning.php">Planning →</a>
        </div>
        <?php if (empty($rdvsAujourdhui)): ?>
            <div class="empty-state-inline"><p>Aucun rendez-vous aujourd'hui.</p></div>
        <?php else: ?>
            <div class="today-list">
                <?php foreach ($rdvsAujourdhui as $rdv): ?>
                    <div class="today-rdv">
                        <span class="today-heure"><?= substr($rdv['heure_rdv'], 0, 5) ?></span>
                        <span class="today-patient"><?= htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']) ?></span>
                        <span class="badge badge--<?= $rdv['statut'] ?>"><?= ucfirst(str_replace('_', ' ', $rdv['statut'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="dashboard-section">
        <div class="dashboard-section-header">
            <h2>En attente de confirmation</h2>
            <a href="<?= APP_URL ?>/views/medecin/rdv.php?statut=en_attente">Voir tous →</a>
        </div>
        <?php if (empty($enAttente)): ?>
            <div class="empty-state-inline"><p>Aucune demande en attente.</p></div>
        <?php else: ?>
            <?php foreach (array_slice($enAttente, 0, 5) as $rdv): ?>
                <div class="pending-rdv">
                    <div>
                        <strong><?= htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']) ?></strong>
                        <span><?= date('d/m/Y', strtotime($rdv['date_rdv'])) ?> à <?= substr($rdv['heure_rdv'], 0, 5) ?></span>
                    </div>
                    <form method="POST" action="<?= APP_URL ?>/controllers/rdvActions.php?action=confirmer" style="display:inline">
                        <input type="hidden" name="rdv_id" value="<?= $rdv['id'] ?>">
                        <button type="submit" class="btn-sm btn-green">Confirmer</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="dashboard-section">
    <div class="dashboard-section-header"><h2>Actions rapides</h2></div>
    <div class="quick-actions">
        <a href="<?= APP_URL ?>/views/medecin/rdv.php" class="quick-action-card"><span class="qa-icon">📋</span><span>Rendez-vous</span></a>
        <a href="<?= APP_URL ?>/views/medecin/planning.php" class="quick-action-card"><span class="qa-icon">📅</span><span>Planning</span></a>
        <a href="<?= APP_URL ?>/views/medecin/disponibilite.php" class="quick-action-card"><span class="qa-icon">⏰</span><span>Disponibilités</span></a>
    </div>
</div>

<?php
// ====== DASHBOARD ADMIN ======
elseif ($role === 'admin'):
?>
<div class="dashboard-welcome">
    <h1>Administration MedRDV</h1>
    <p>Panneau de gestion du système.</p>
</div>
<div class="info-banner">
    <span>🔧</span>
    <p>Interface d'administration en cours de développement.</p>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/views/shared/footer.php'; ?>