<?php
// views/medecin/rdv.php
$pageTitle = 'Gestion des Rendez-vous';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/authController.php';
AuthController::requireAuth('medecin');
require_once __DIR__ . '/../../models/RendezVous.php';

$rdvModel  = new RendezVous();
$medecinId = $_SESSION['medecin_id'];
$statut    = $_GET['statut'] ?? '';
$rdvs      = $rdvModel->findByMedecin($medecinId);
$stats     = $rdvModel->statsMedecin($medecinId);

if ($statut) {
    $rdvs = array_filter($rdvs, fn($r) => $r['statut'] === $statut);
}

require_once __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <h1>Rendez-vous</h1>
    <span class="badge-today">Aujourd'hui : <?= $stats['aujourd_hui'] ?? 0 ?> RDV</span>
</div>

<!-- Stats bar -->
<div class="stats-grid">
    <div class="stat-card"><span class="stat-number"><?= $stats['total'] ?></span><span class="stat-label">Total</span></div>
    <div class="stat-card stat-card--yellow"><span class="stat-number"><?= $stats['en_attente'] ?></span><span class="stat-label">En attente</span></div>
    <div class="stat-card stat-card--green"><span class="stat-number"><?= $stats['confirmes'] ?></span><span class="stat-label">Confirmés</span></div>
    <div class="stat-card stat-card--blue"><span class="stat-number"><?= $stats['termines'] ?></span><span class="stat-label">Terminés</span></div>
</div>

<!-- Filtres -->
<div class="filter-tabs">
    <a href="?statut=" class="filter-tab <?= !$statut ? 'active' : '' ?>">Tous</a>
    <a href="?statut=en_attente" class="filter-tab <?= $statut === 'en_attente' ? 'active' : '' ?>">En attente</a>
    <a href="?statut=confirme" class="filter-tab <?= $statut === 'confirme' ? 'active' : '' ?>">Confirmés</a>
    <a href="?statut=termine" class="filter-tab <?= $statut === 'termine' ? 'active' : '' ?>">Terminés</a>
    <a href="?statut=annule" class="filter-tab <?= $statut === 'annule' ? 'active' : '' ?>">Annulés</a>
</div>

<?php if (empty($rdvs)): ?>
    <div class="empty-state">
        <div class="empty-icon">📋</div>
        <h2>Aucun rendez-vous</h2>
        <p>Vous n'avez aucun rendez-vous dans cette catégorie.</p>
    </div>
<?php else: ?>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date & Heure</th>
                    <th>Patient</th>
                    <th>Téléphone</th>
                    <th>Motif</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rdvs as $rdv): ?>
                    <tr>
                        <td>
                            <strong><?= date('d/m/Y', strtotime($rdv['date_rdv'])) ?></strong><br>
                            <span class="text-muted"><?= substr($rdv['heure_rdv'], 0, 5) ?></span>
                        </td>
                        <td>
                            <?= htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']) ?>
                        </td>
                        <td><?= htmlspecialchars($rdv['patient_tel'] ?? '—') ?></td>
                        <td>
                            <span class="text-truncate" title="<?= htmlspecialchars($rdv['motif'] ?? '') ?>">
                                <?= htmlspecialchars(substr($rdv['motif'] ?? '—', 0, 40)) ?>
                            </span>
                        </td>
                        <td><span class="badge badge--<?= $rdv['statut'] ?>"><?= ucfirst(str_replace('_', ' ', $rdv['statut'])) ?></span></td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($rdv['statut'] === 'en_attente'): ?>
                                    <form method="POST" action="<?= APP_URL ?>/controllers/rdvController.php?action=confirmer" style="display:inline">
                                        <input type="hidden" name="rdv_id" value="<?= $rdv['id'] ?>">
                                        <button type="submit" class="btn-sm btn-green">✓ Confirmer</button>
                                    </form>
                                    <form method="POST" action="<?= APP_URL ?>/controllers/rdvController.php?action=annuler"
                                          onsubmit="return confirm('Annuler ce RDV ?')" style="display:inline">
                                        <input type="hidden" name="rdv_id" value="<?= $rdv['id'] ?>">
                                        <button type="submit" class="btn-sm btn-red">✗ Annuler</button>
                                    </form>
                                <?php elseif ($rdv['statut'] === 'confirme'): ?>
                                    <button class="btn-sm btn-blue"
                                            onclick="openNotesModal(<?= $rdv['id'] ?>, '<?= htmlspecialchars(addslashes($rdv['notes_medecin'] ?? ''), ENT_QUOTES) ?>')">
                                        ✓ Terminer
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Modal notes médecin -->
<div id="notesModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Clôturer le rendez-vous</h3>
            <button onclick="document.getElementById('notesModal').classList.add('hidden')" class="modal-close">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/controllers/rdvController.php?action=terminer">
            <input type="hidden" name="rdv_id" id="notes_rdv_id">
            <div class="form-group">
                <label for="notes">Notes / Compte-rendu (optionnel)</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Observations, prescriptions, suivi..."></textarea>
            </div>
            <button type="submit" class="btn-primary">Marquer comme terminé</button>
        </form>
    </div>
</div>

<script>
function openNotesModal(rdvId, notes) {
    document.getElementById('notes_rdv_id').value = rdvId;
    document.getElementById('notes').value = notes;
    document.getElementById('notesModal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>