<?php
// index.php — Page d'accueil publique
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Medecin.php';

$medecinModel = new Medecin();
$specialites  = $medecinModel->getSpecialites();
$medecins     = $medecinModel->search('', '', '');
$totalMedecins = count($medecins);

$pageTitle = 'Accueil';
require_once __DIR__ . '/views/shared/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">🏥 Plateforme médicale en ligne</div>
        <h1 class="hero-title">
            Vos rendez-vous médicaux,<br>
            <em>simplifiés</em>
        </h1>
        <p class="hero-sub">
            Trouvez un médecin, prenez rendez-vous en ligne et gérez votre santé depuis chez vous.
            Simple, rapide, sécurisé.
        </p>
        <div class="hero-actions">
            <a href="<?= APP_URL ?>/views/patient/rechercher_medecin.php" class="btn-hero-primary">
                Trouver un médecin
            </a>
            <a href="<?= APP_URL ?>/register.php" class="btn-hero-secondary">
                Créer un compte gratuit
            </a>
        </div>
    </div>
    <div class="hero-visual">
        <div class="hero-card-float">
            <div class="hcf-icon">📅</div>
            <div>
                <strong>Rendez-vous confirmé</strong>
                <span>Dr Khalil Ben Ali · Cardiologie</span>
                <span>Demain à 10h00</span>
            </div>
        </div>
        <div class="hero-stats-row">
            <div class="hero-stat">
                <span class="hero-stat-num"><?= $totalMedecins ?>+</span>
                <span>Médecins</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-num">24/7</span>
                <span>Disponible</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-num">100%</span>
                <span>Gratuit</span>
            </div>
        </div>
    </div>
</section>

<!-- Spécialités -->
<section class="section">
    <div class="section-header">
        <h2>Consultez par spécialité</h2>
        <p>Tous les types de soins disponibles sur MedRDV</p>
    </div>
    <div class="specialites-grid">
        <?php
        $icons = [
            'Cardiologie' => '❤️', 'Dermatologie' => '🧴', 'Pédiatrie' => '👶',
            'Gynécologie' => '🌸', 'Neurologie' => '🧠', 'Orthopédie' => '🦴',
            'Médecine générale' => '🩺', 'ORL' => '👂', 'Ophtalmologie' => '👁',
            'Psychiatrie' => '🧘', 'Radiologie' => '📡', 'Urologie' => '🔬',
        ];
        foreach ($specialites as $s):
            $icon = $icons[$s] ?? '🏥';
        ?>
            <a href="<?= APP_URL ?>/views/patient/rechercher_medecin.php?specialite=<?= urlencode($s) ?>" class="spec-card">
                <span class="spec-icon"><?= $icon ?></span>
                <span class="spec-name"><?= htmlspecialchars($s) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Comment ça marche -->
<section class="section section--alt">
    <div class="section-header">
        <h2>Comment ça marche ?</h2>
        <p>Trois étapes pour prendre votre rendez-vous</p>
    </div>
    <div class="steps-grid">
        <div class="step-card">
            <div class="step-num">01</div>
            <div class="step-icon">🔍</div>
            <h3>Recherchez</h3>
            <p>Trouvez le médecin qu'il vous faut par spécialité, nom ou localité.</p>
        </div>
        <div class="step-card">
            <div class="step-num">02</div>
            <div class="step-icon">📅</div>
            <h3>Choisissez</h3>
            <p>Sélectionnez le jour et le créneau horaire qui vous convient.</p>
        </div>
        <div class="step-card">
            <div class="step-num">03</div>
            <div class="step-icon">✅</div>
            <h3>Confirmez</h3>
            <p>Votre rendez-vous est enregistré. Vous serez notifié de sa confirmation.</p>
        </div>
    </div>
</section>

<!-- Médecins récents -->
<?php if (!empty($medecins)): ?>
<section class="section">
    <div class="section-header">
        <h2>Médecins disponibles</h2>
        <a href="<?= APP_URL ?>/views/patient/rechercher_medecin.php" class="section-link">Voir tous →</a>
    </div>
    <div class="medecins-grid medecins-grid--home">
        <?php foreach (array_slice($medecins, 0, 4) as $m): ?>
            <div class="medecin-card">
                <div class="medecin-card-header">
                    <div class="medecin-avatar medecin-avatar--sm">
                        <?= strtoupper(substr($m['prenom'], 0, 1) . substr($m['nom'], 0, 1)) ?>
                    </div>
                    <div class="medecin-info">
                        <h3>Dr <?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?></h3>
                        <div class="medecin-spec"><?= htmlspecialchars($m['specialite']) ?></div>
                        <?php if ($m['ville']): ?><div class="medecin-ville">📍 <?= htmlspecialchars($m['ville']) ?></div><?php endif; ?>
                    </div>
                </div>
                <?php if ($m['nb_avis'] > 0): ?>
                    <div class="card-rating">
                        <span class="stars-sm"><?= str_repeat('★', round($m['note_moyenne'])) ?><?= str_repeat('☆', 5 - round($m['note_moyenne'])) ?></span>
                        <span><?= number_format($m['note_moyenne'], 1) ?> (<?= $m['nb_avis'] ?> avis)</span>
                    </div>
                <?php endif; ?>
                <?php if ($m['tarif']): ?><div class="card-tarif"><?= number_format($m['tarif'], 0) ?> DT / consultation</div><?php endif; ?>
                <div class="card-actions">
                    <a href="<?= APP_URL ?>/views/patient/prendre_rdv.php?medecin_id=<?= $m['id'] ?>" class="btn-primary">Prendre RDV</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/views/shared/footer.php'; ?>