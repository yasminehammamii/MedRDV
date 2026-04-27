<?php
// views/auth/register.php
$pageTitle = 'Inscription';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../shared/header.php';
$errors = $data['errors'] ?? [];
$role   = $data['role']   ?? 'patient';
?>

<div class="auth-wrapper">
    <div class="auth-card auth-card--wide">
        <div class="auth-header">
            <div class="auth-icon">⚕</div>
            <h1>Créer un compte</h1>
            <p>Rejoignez MedRDV en quelques secondes</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                    <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Sélection du rôle -->
        <div class="role-picker">
            <label class="role-option <?= $role === 'patient' ? 'active' : '' ?>" onclick="setRole('patient')">
                <input type="radio" name="role_pick" value="patient" <?= $role === 'patient' ? 'checked' : '' ?>>
                <span class="role-icon">🏥</span>
                <span class="role-label">Je suis patient</span>
                <span class="role-desc">Prendre des rendez-vous</span>
            </label>
            <label class="role-option <?= $role === 'medecin' ? 'active' : '' ?>" onclick="setRole('medecin')">
                <input type="radio" name="role_pick" value="medecin" <?= $role === 'medecin' ? 'checked' : '' ?>>
                <span class="role-icon">👨‍⚕️</span>
                <span class="role-label">Je suis médecin</span>
                <span class="role-desc">Gérer mon cabinet</span>
            </label>
        </div>

        <form method="POST" action="<?= APP_URL ?>/register.php" class="auth-form" id="registerForm">
            <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($role) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($data['prenom'] ?? '') ?>"
                           placeholder="Khalil" required>
                </div>
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($data['nom'] ?? '') ?>"
                           placeholder="Ben Ali" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>"
                       placeholder="vous@exemple.com" required>
            </div>

            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($data['telephone'] ?? '') ?>"
                       placeholder="+216 XX XXX XXX">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Min. 6 caractères" required>
                </div>
                <div class="form-group">
                    <label for="password2">Confirmer</label>
                    <input type="password" id="password2" name="password2" placeholder="Répéter" required>
                </div>
            </div>

            <!-- Champs patient -->
            <div id="patient-fields" class="extra-fields <?= $role !== 'patient' ? 'hidden' : '' ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_naissance">Date de naissance</label>
                        <input type="date" id="date_naissance" name="date_naissance">
                    </div>
                    <div class="form-group">
                        <label for="sexe">Sexe</label>
                        <select id="sexe" name="sexe">
                            <option value="M">Homme</option>
                            <option value="F">Femme</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Champs médecin -->
            <div id="medecin-fields" class="extra-fields <?= $role !== 'medecin' ? 'hidden' : '' ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="specialite">Spécialité</label>
                        <select id="specialite" name="specialite">
                            <option value="">Choisir...</option>
                            <option value="Médecine générale">Médecine générale</option>
                            <option value="Cardiologie">Cardiologie</option>
                            <option value="Dermatologie">Dermatologie</option>
                            <option value="Pédiatrie">Pédiatrie</option>
                            <option value="Gynécologie">Gynécologie</option>
                            <option value="Orthopédie">Orthopédie</option>
                            <option value="Neurologie">Neurologie</option>
                            <option value="Ophtalmologie">Ophtalmologie</option>
                            <option value="ORL">ORL</option>
                            <option value="Psychiatrie">Psychiatrie</option>
                            <option value="Radiologie">Radiologie</option>
                            <option value="Urologie">Urologie</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="num_ordre">N° Ordre médical</label>
                        <input type="text" id="num_ordre" name="num_ordre" placeholder="MED-XXXX-XXX">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-auth">Créer mon compte</button>
        </form>

        <div class="auth-footer">
            <p>Déjà un compte ? <a href="<?= APP_URL ?>/login.php">Se connecter</a></p>
        </div>
    </div>
</div>

<script>
function setRole(role) {
    document.getElementById('roleInput').value = role;
    document.querySelectorAll('.role-option').forEach(el => el.classList.remove('active'));
    document.querySelector(`[onclick="setRole('${role}')"]`).classList.add('active');
    document.getElementById('patient-fields').classList.toggle('hidden', role !== 'patient');
    document.getElementById('medecin-fields').classList.toggle('hidden', role !== 'medecin');
}
</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>