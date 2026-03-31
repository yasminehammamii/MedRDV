<?php
// views/patient/mes_rdv.php
$pageTitle = 'Mes Rendez-vous';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/authController.php';
AuthController::requireAuth('patient');
require_once __DIR__ . '/../../models/RendezVous.php';
require_once __DIR__ . '/../../models/Avis.php';

$rdvModel  = new RendezVous();
$avisModel = new Avis();
$rdvs      = $rdvModel->findByPatient($_SESSION['patient_id']);
$stats     = $rdvModel->statsPatient($_SESSION['patient_id']);

require_once __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <h1>Mes Rendez-vous</h1>
    <a href="<?= APP_URL ?>/views/patient/rechercher_medecin.php" class="btn-primary">+ Nouveau RDV</a>
</div>

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-number"><?= $stats['total'] ?></span>
        <span class="stat-label">Total</span>
    </div>
    <div class="stat-card stat-card--green">
        <span class="stat-number"><?= $stats['confirmes'] ?></span>
        <span class="stat-label">Confirmés</span>
    </div>
    <div class="stat-card stat-card--yellow">
        <span class="stat-number"><?= $stats['en_attente'] ?></span>
        <span class="stat-label">En attente</span>
    </div>
    <div class="stat-card stat-card--red">
        <span class="stat-number"><?= $stats['annules'] ?></span>
        <span class="stat-label">Annulés</span>
    </div>
</div>

<!-- Filtres rapides -->
<div class="filter-tabs">
    <button class="filter-tab active" data-filter="all">Tous</button>
    <button class="filter-tab" data-filter="en_attente">En attente</button>
    <button class="filter-tab" data-filter="confirme">Confirmés</button>
    <button class="filter-tab" data-filter="termine">Terminés</button>
    <button class="filter-tab" data-filter="annule">Annulés</button>
</div>

<?php if (empty($rdvs)): ?>
    <div class="empty-state">
        <div class="empty-icon">📅</div>
        <h2>Aucun rendez-vous</h2>
        <p>Prenez votre premier rendez-vous médical en ligne.</p>
        <a href="<?= APP_URL ?>/views/patient/rechercher_medecin.php" class="btn-primary">Trouver un médecin</a>
    </div>
<?php else: ?>
    <div class="rdv-list" id="rdvList">
        <?php foreach ($rdvs as $rdv): ?>
            <?php
            $isPast    = strtotime($rdv['date_rdv']) < strtotime('today');
            $canCancel = in_array($rdv['statut'], ['en_attente', 'confirme']) && !$isPast;
            $canAvis   = $rdv['statut'] === 'termine' && !$avisModel->existsForRdv($rdv['id']);
            ?>
            <div class="rdv-card" data-statut="<?= $rdv['statut'] ?>">
                <div class="rdv-card-left">
                    <div class="rdv-date">
                        <span class="rdv-day"><?= date('d', strtotime($rdv['date_rdv'])) ?></span>
                        <span class="rdv-month"><?= strftime('%b', strtotime($rdv['date_rdv'])) ?></span>
                    </div>
                    <div class="rdv-time"><?= substr($rdv['heure_rdv'], 0, 5) ?></div>
                </div>
                <div class="rdv-card-body">
                    <div class="rdv-medecin">
                        Dr <?= htmlspecialchars($rdv['medecin_prenom'] . ' ' . $rdv['medecin_nom']) ?>
                    </div>
                    <div class="rdv-spec"><?= htmlspecialchars($rdv['specialite']) ?></div>
                    <?php if ($rdv['motif']): ?>
                        <div class="rdv-motif">Motif : <?= htmlspecialchars($rdv['motif']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="rdv-card-right">
                    <span class="badge badge--<?= $rdv['statut'] ?>"><?= ucfirst(str_replace('_', ' ', $rdv['statut'])) ?></span>
                    <div class="rdv-actions">
                        <?php if ($canCancel): ?>
                            <form method="POST" action="<?= APP_URL ?>/controllers/rdvController.php?action=annuler"
                                  onsubmit="return confirm('Annuler ce rendez-vous ?')">
                                <input type="hidden" name="rdv_id" value="<?= $rdv['id'] ?>">
                                <button type="submit" class="btn-danger-sm">Annuler</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($canAvis): ?>
                            <button class="btn-outline-sm" onclick="openAvisModal(<?= $rdv['id'] ?>, <?= $rdv['medecin_id'] ?>)">
                                ⭐ Laisser un avis
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal Avis -->
<div id="avisModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Laisser un avis</h3>
            <button onclick="closeAvisModal()" class="modal-close">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/controllers/rdvController.php?action=laisserAvis">
            <input type="hidden" name="rdv_id" id="avis_rdv_id">
            <input type="hidden" name="medecin_id" id="avis_medecin_id">
            <div class="star-rating">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" name="note" id="star<?= $i ?>" value="<?= $i ?>">
                    <label for="star<?= $i ?>">★</label>
                <?php endfor; ?>
            </div>
            <div class="form-group">
                <label for="commentaire">Commentaire (optionnel)</label>
                <textarea id="commentaire" name="commentaire" rows="3" placeholder="Votre expérience..."></textarea>
            </div>
            <button type="submit" class="btn-primary">Envoyer l'avis</button>
        </form>
    </div>
</div>

<script>
// Filtres
document.querySelectorAll('.filter-tab').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('.rdv-card').forEach(card => {
            card.style.display = (filter === 'all' || card.dataset.statut === filter) ? '' : 'none';
        });
    });
});

function openAvisModal(rdvId, medecinId) {
    document.getElementById('avis_rdv_id').value = rdvId;
    document.getElementById('avis_medecin_id').value = medecinId;
    document.getElementById('avisModal').classList.remove('hidden');
}
function closeAvisModal() {
    document.getElementById('avisModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>