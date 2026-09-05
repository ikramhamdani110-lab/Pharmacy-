<?php $siteProfile = getSiteProfile($conn); ?>
<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <?php if (!empty($siteProfile['photo_path'])): ?>
                <img src="<?php echo h($siteProfile['photo_path']); ?>" alt="Photo de <?php echo h($siteProfile['full_name']); ?>" class="footer-profile-photo">
            <?php endif; ?>
            <h3><i class="fas fa-pills"></i> <?php echo h($siteProfile['full_name']); ?></h3>
            <p><?php echo h($siteProfile['bio']); ?></p>
        </div>
        <div class="footer-links">
            <h4>Liens rapides</h4>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Connexion</a></li>
                <li><a href="register_patient.php"><i class="fas fa-user-plus"></i> S'inscrire</a></li>
            </ul>
        </div>
        <div class="footer-contact">
            <h4>Contact</h4>
            <p><i class="fas fa-map-marker-alt"></i> <?php echo h($siteProfile['location']); ?></p>
            <p><i class="fas fa-phone"></i> <?php echo h($siteProfile['phone']); ?></p>
            <p><i class="fas fa-envelope"></i> <?php echo h($siteProfile['email']); ?></p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2026 PharmaSanté. Tous droits réservés. TP Final DAW L2 INF G 04</p>
    </div>
</footer>
