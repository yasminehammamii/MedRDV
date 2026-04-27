<?php
// views/patient/rechercher_medecin.php
$pageTitle = 'Rechercher un Médecin';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Medecin.php';

$medecinModel = new Medecin();
$query      = trim($_GET['q'] ?? '');
$specialite = $_GET['specialite'] ?? '';
$ville      = trim($_GET['ville'] ?? '');
$medecins   = $medecinModel->search($query, $specialite, $ville);
$specialites = $medecinModel->getSpecialites();
$villes     = $medecinModel->getVilles();

require_once __DIR__ . '/../shared/header.php';
?>

<div class="search-hero">
    <h1>Trouver un médecin</h1>
    <p>Recherchez parmi nos praticiens et prenez rendez-vous en ligne</p>
</div>

<form method="GET" class="search-form">
    <div class="search-bar">
        <input type="text" name="q" value="<?= htmlspecialchars($query) ?>"
               placeholder="Nom du médecin ou spécialité..." class="search-input">
        <select name="specialite" class="search-select">
            <option value="">Toutes spécialités</option>
            <?php foreach ($specialites as $s): ?>
                <option value="<?= htmlspecialchars($s) ?>" <?= $specialite === $s ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="ville" class="search-select">
            <option value="">Toutes les villes</option>
            <?php foreach ($villes as $v): ?>
                <option value="<?= htmlspecialchars($v) ?>" <?= $ville === $v ? 'selected' : '' ?>>
                    <?= htmlspecialchars($v) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-search">Rechercher</button>
    </div>
</form>

<div class="results-header">
    <span class="results-count"><?= count($medecins) ?> médecin<?= count($medecins) > 1 ? 's' : '' ?> trouvé<?= count($medecins) > 1 ? 's' : '' ?></span>
    <?php if ($query || $specialite || $ville): ?>
        <a href="<?= APP_URL ?>/views/patient/rechercher_medecin.php" class="clear-filters">Effacer les filtres</a>
    <?php endif; ?>
</div>

<?php if (empty($medecins)): ?>
    <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <h2>Aucun résultat</h2>
        <p>Essayez des termes de recherche différents.</p>
    </div>
<?php else: ?>
    <div class="medecins-grid">
        <?php foreach ($medecins as $m): ?>
            <div class="medecin-card">
                <div class="medecin-card-header">
                    <div class="medecin-avatar medecin-avatar--sm">
                        <?= strtoupper(substr($m['prenom'], 0, 1) . substr($m['nom'], 0, 1)) ?>
                    </div>
                    <div class="medecin-info">
                        <h3>Dr <?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?></h3>
                        <div class="medecin-spec"><?= htmlspecialchars($m['specialite']) ?></div>
                        <?php if ($m['ville']): ?>
                            <div class="medecin-ville">📍 <?= htmlspecialchars($m['ville']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($m['nb_avis'] > 0): ?>
                    <div class="card-rating">
                        <span class="stars-sm"><?= str_repeat('★', round($m['note_moyenne'])) ?><?= str_repeat('☆', 5 - round($m['note_moyenne'])) ?></span>
                        <span><?= number_format($m['note_moyenne'], 1) ?> (<?= $m['nb_avis'] ?> avis)</span>
                    </div>
                <?php endif; ?>

                <?php if ($m['tarif']): ?>
                    <div class="card-tarif"><?= number_format($m['tarif'], 0) ?> DT / consultation</div>
                <?php endif; ?>

                <?php if ($m['bio']): ?>
                    <p class="card-bio"><?= htmlspecialchars(substr($m['bio'], 0, 100)) ?>...</p>
                <?php endif; ?>

                <div class="card-actions">
                    <?php if (!empty($_SESSION['user_id']) && $_SESSION['user_role'] === 'patient'): ?>
                        <a href="<?= APP_URL ?>/views/patient/prendre_rdv.php?medecin_id=<?= $m['id'] ?>" class="btn-primary">
                            Prendre RDV
                        </a>
                    <?php else: ?>
                        <a href="<?= APP_URL ?>/login.php" class="btn-primary">
                            Connexion pour RDV
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>