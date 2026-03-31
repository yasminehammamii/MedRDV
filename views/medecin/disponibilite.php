<?php
// views/medecin/disponibilite.php
$pageTitle = 'Mes Disponibilités';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/authController.php';
AuthController::requireAuth('medecin');
require_once __DIR__ . '/../../models/Disponibilite.php';

$dispoModel = new Disponibilite();
$medecinId  = $_SESSION['medecin_id'];
$dispos     = $dispoModel->findByMedecin($medecinId);

// Grouper par jour
$dispoParJour = [];
foreach ($dispos as $d) {
    $dispoParJour[$d['jour_semaine']][] = $d;
}

require_once __DIR__ . '/../shared/header.php';
?>

<div class="page-header">
    <h1>Mes Disponibilités</h1>
    <button class="btn-primary" onclick="document.getElementById('addDispoModal').classList.remove('hidden')">
        + Ajouter un créneau
    </button>
</div>

<div class="info-banner">
    <span>ℹ️</span>
    <p>Définissez vos plages horaires par jour de la semaine. Les patients pourront réserver des créneaux dans ces plages.</p>
</div>

<!-- Grille des disponibilités par jour -->
<div class="dispo-week">
    <?php foreach (Disponibilite::JOURS as $idx => $jour): ?>
        <div class="dispo-day">
            <div class="dispo-day-header">
                <h3><?= $jour ?></h3>
                <span class="dispo-count">
                    <?= count($dispoParJour[$idx] ?? []) ?> plage<?= count($dispoParJour[$idx] ?? []) > 1 ? 's' : '' ?>
                </span>
            </div>
            <div class="dispo-day-body">
                <?php if (empty($dispoParJour[$idx])): ?>
                    <div class="dispo-empty">Repos / Fermé</div>
                <?php else: ?>
                    <?php foreach ($dispoParJour[$idx] as $d): ?>
                        <div class="dispo-slot <?= !$d['actif'] ? 'dispo-slot--inactive' : '' ?>">
                            <div class="dispo-slot-time">
                                <?= substr($d['heure_debut'], 0, 5) ?> — <?= substr($d['heure_fin'], 0, 5) ?>
                            </div>
                            <div class="dispo-slot-meta">
                                ⏱ <?= $d['duree_rdv'] ?> min / consultation
                            </div>
                            <div class="dispo-slot-actions">
                                <form method="POST" action="<?= APP_URL ?>/controllers/planningActions.php?action=supprimer"
                                      onsubmit="return confirm('Supprimer cette disponibilité ?')" style="display:inline">
                                    <input type="hidden" name="dispo_id" value="<?= $d['id'] ?>">
                                    <button type="submit" class="btn-icon-danger" title="Supprimer">🗑</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal ajout disponibilité -->
<div id="addDispoModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ajouter une disponibilité</h3>
            <button onclick="document.getElementById('addDispoModal').classList.add('hidden')" class="modal-close">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/controllers/planningActions.php?action=ajouter">
            <div class="form-group">
                <label for="jour_semaine">Jour de la semaine</label>
                <select id="jour_semaine" name="jour_semaine" required>
                    <option value="">Choisir un jour...</option>
                    <?php foreach (Disponibilite::JOURS as $idx => $jour): ?>
                        <option value="<?= $idx ?>"><?= $jour ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="heure_debut">Heure de début</label>
                    <input type="time" id="heure_debut" name="heure_debut" value="09:00" required>
                </div>
                <div class="form-group">
                    <label for="heure_fin">Heure de fin</label>
                    <input type="time" id="heure_fin" name="heure_fin" value="13:00" required>
                </div>
            </div>
            <div class="form-group">
                <label for="duree_rdv">Durée par consultation</label>
                <select id="duree_rdv" name="duree_rdv" required>
                    <option value="15">15 minutes</option>
                    <option value="20">20 minutes</option>
                    <option value="30" selected>30 minutes</option>
                    <option value="45">45 minutes</option>
                    <option value="60">1 heure</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Enregistrer la disponibilité</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>