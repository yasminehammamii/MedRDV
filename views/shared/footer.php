<?php
// views/shared/footer.php
?>
</main>

<footer class="footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <span class="brand-icon">⚕</span>
            <span>Med<strong>RDV</strong></span>
            <p>Simplifiez la gestion de vos rendez-vous médicaux.</p>
        </div>
        <div class="footer-links">
            <div>
                <h4>Navigation</h4>
                <a href="<?= APP_URL ?>">Accueil</a>
                <a href="<?= APP_URL ?>/views/patient/rechercher_medecin.php">Trouver un médecin</a>
                <a href="<?= APP_URL ?>/register.php">Inscription</a>
            </div>
            <div>
                <h4>Contact</h4>
                <span>contact@medrdv.tn</span>
                <span>+216 71 000 000</span>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> MedRDV — Tous droits réservés.</p>
    </div>
</footer>

<script src="<?= APP_URL ?>/assets/js/app.js"></script>
</body>
</html>