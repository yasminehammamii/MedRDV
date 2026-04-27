<?php
// views/auth/login.php
$pageTitle = 'Connexion';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../shared/header.php';
$errors = $data['errors'] ?? [];
$email  = htmlspecialchars($data['email'] ?? '');
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">⚕</div>
            <h1>Bon retour</h1>
            <p>Connectez-vous à votre espace MedRDV</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                    <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/login.php" class="auth-form">
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" value="<?= $email ?>"
                       placeholder="vous@exemple.com" required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-password">
                    <input type="password" id="password" name="password"
                           placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" class="toggle-pwd" onclick="togglePassword('password')">👁</button>
                </div>
            </div>
            <button type="submit" class="btn-auth">Se connecter</button>
        </form>

        <div class="auth-footer">
            <p>Pas encore de compte ? <a href="<?= APP_URL ?>/register.php">S'inscrire</a></p>
        </div>

        <div class="demo-credentials">
            <p><strong>Comptes démo :</strong></p>
            <code>Patient: mehdi.bouazizi@medrdv.tn</code>
            <code>Médecin: khalil.benali@medrdv.tn</code>
            <code>Mot de passe: Password123!</code>
        </div>
    </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>