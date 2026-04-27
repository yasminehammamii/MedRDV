<?php
// views/patient/prendre_rdv.php
$pageTitle = 'Prendre un Rendez-vous';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/authController.php';
AuthController::requireAuth('patient');
require_once __DIR__ . '/../../models/RendezVous.php';
require_once __DIR__ . '/../../models/Medecin.php';
require_once __DIR__ . '/../../models/Avis.php';

$rdvModel     = new RendezVous();
$medecinModel = new Medecin();
$avisModel    = new Avis();

$medecinId = (int)($_GET['medecin_id'] ?? 0);
$medecin   = $medecinModel->findById($medecinId);
if (!$medecin) { header('Location: ' . APP_URL . '/views/patient/rechercher_medecin.php'); exit; }

$date     = $_GET['date'] ?? date('Y-m-d');
$creneaux = $rdvModel->getCreneauxDisponibles($medecinId, $date);
$statsAvis = $avisModel->getStats($medecinId);
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motif   = trim($_POST['motif'] ?? '');
    $heure   = $_POST['heure'] ?? '';
    $dateRdv = $_POST['date_rdv'] ?? '';

    if (empty($heure))   $errors[] = "Veuillez choisir un créneau horaire.";
    if (empty($dateRdv)) $errors[] = "Date invalide.";
    if (!empty($dateRdv) && strtotime($dateRdv) < strtotime('today')) $errors[] = "La date ne peut pas être dans le passé.";

    if (empty($errors)) {
        try {
            $rdvModel->create([
                'patient_id' => $_SESSION['patient_id'],
                'medecin_id' => $medecinId,
                'date_rdv'   => $dateRdv,
                'heure_rdv'  => $heure,
                'motif'      => $motif,
            ]);
            $_SESSION['flash'] = "Votre demande de rendez-vous a été envoyée avec succès !";
            header('Location: ' . APP_URL . '/views/patient/mes_rdv.php'); exit;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../shared/header.php';
?>

<div class="rdv-layout">
    <!-- Profil médecin -->
    <aside class="rdv-sidebar">
        <div class="medecin-profile-card">
            <div class="medecin-avatar">
                <?= strtoupper(substr($medecin['prenom'], 0, 1) . substr($medecin['nom'], 0, 1)) ?>
            </div>
            <h2>Dr <?= htmlspecialchars($medecin['prenom'] . ' ' . $medecin['nom']) ?></h2>
            <div class="medecin-spec"><?= htmlspecialchars($medecin['specialite']) ?></div>

            <?php if ($statsAvis['total'] > 0): ?>
                <div class="rating-display">
                    <span class="stars"><?= str_repeat('★', round($statsAvis['moyenne'])) ?><?= str_repeat('☆', 5 - round($statsAvis['moyenne'])) ?></span>
                    <span class="rating-score"><?= number_format($statsAvis['moyenne'], 1) ?>/5</span>
                    <span class="rating-count">(<?= $statsAvis['total'] ?> avis)</span>
                </div>
            <?php endif; ?>

            <?php if ($medecin['tarif']): ?>
                <div class="tarif-badge">Consultation : <?= number_format($medecin['tarif'], 0) ?> DT</div>
            <?php endif; ?>

            <?php if ($medecin['adresse']): ?>
                <div class="medecin-adresse">
                    📍 <?= htmlspecialchars($medecin['adresse'] . ', ' . $medecin['ville']) ?>
                </div>
            <?php endif; ?>

            <?php if ($medecin['bio']): ?>
                <p class="medecin-bio"><?= htmlspecialchars($medecin['bio']) ?></p>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Formulaire de prise de RDV -->
    <div class="rdv-main">
        <div class="section-title">
            <h1>Choisissez votre créneau</h1>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): echo "<p>" . htmlspecialchars($e) . "</p>"; endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="rdv-form" id="rdvForm">
            <div class="date-picker-section">
                <label for="date_rdv">Choisir une date</label>
                <input type="date" id="date_rdv" name="date_rdv"
                       value="<?= htmlspecialchars($date) ?>"
                       min="<?= date('Y-m-d') ?>"
                       onchange="loadCreneaux(this.value)"
                       required>
            </div>

            <div class="creneaux-section">
                <h3>Créneaux disponibles</h3>
                <div class="creneaux-grid" id="creneauxGrid">
                    <?php if (empty($creneaux)): ?>
                        <p class="no-creneaux">Aucun créneau disponible pour cette date. Essayez une autre date.</p>
                    <?php else: ?>
                        <?php foreach ($creneaux as $c): ?>
                            <label class="creneau-option">
                                <input type="radio" name="heure" value="<?= $c ?>" required>
                                <span><?= $c ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="motif">Motif de consultation</label>
                <textarea id="motif" name="motif" rows="3"
                          placeholder="Décrivez brièvement votre motif de consultation..."><?= htmlspecialchars($_POST['motif'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn-primary btn-large" id="submitBtn">
                Confirmer le rendez-vous
            </button>
        </form>
    </div>
</div>

<script>
function loadCreneaux(date) {
    const grid = document.getElementById('creneauxGrid');
    const medecinId = <?= $medecinId ?>;
    grid.innerHTML = '<div class="loading">Chargement...</div>';

    fetch(`<?= APP_URL ?>/controllers/rdvController.php?action=creneaux&medecin_id=${medecinId}&date=${date}`)
        .then(r => r.json())
        .then(creneaux => {
            if (creneaux.length === 0) {
                grid.innerHTML = '<p class="no-creneaux">Aucun créneau disponible pour cette date.</p>';
            } else {
                grid.innerHTML = creneaux.map(c =>
                    `<label class="creneau-option">
                        <input type="radio" name="heure" value="${c}" required>
                        <span>${c}</span>
                    </label>`
                ).join('');
            }
        });
}
</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>