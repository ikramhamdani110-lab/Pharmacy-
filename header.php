<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/includes/functions.php';
$role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'visitor';
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>
<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="logo"><i class="fas fa-pills"></i> PharmaSanté</a>
        <button class="hamburger" onclick="toggleMobileMenu()" aria-label="Menu">
            <i class="fas fa-bars"></i>
        </button>
        <nav class="main-nav" id="mainNav">
            <?php if ($role === 'visitor'): ?>
                <a href="index.php"><i class="fas fa-home"></i> Accueil</a>
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                <a href="register_patient.php" class="btn-nav"><i class="fas fa-user-plus"></i> S'inscrire</a>
            <?php elseif ($role === 'patient'): ?>
                <a href="index.php"><i class="fas fa-home"></i> Accueil</a>
                <a href="patient_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
                <a href="patient_profile.php"><i class="fas fa-user"></i> Mon Profil</a>
                <a href="patient_historique.php"><i class="fas fa-history"></i> Mes Médicaments</a>
                <a href="logout.php" class="btn-nav btn-danger-nav"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            <?php elseif ($role === 'vendeur'): ?>
                <a href="vendeur_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
                <a href="gestion_patient.php"><i class="fas fa-users"></i> Patients</a>
                <a href="ajouter_vente.php"><i class="fas fa-shopping-cart"></i> Nouvelle Vente</a>
                <a href="mes_ventes.php"><i class="fas fa-list"></i> Mes Ventes</a>
                <a href="caisse_operation.php"><i class="fas fa-cash-register"></i> Caisse</a>
                <a href="recherche_medicament.php"><i class="fas fa-search"></i> Recherche</a>
                <a href="logout.php" class="btn-nav btn-danger-nav"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            <?php elseif ($role === 'admin'): ?>
                <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
                <a href="gestion_vendeur.php"><i class="fas fa-user-tie"></i> Vendeurs</a>
                <a href="gestion_medicament.php"><i class="fas fa-pills"></i> Médicaments</a>
                <a href="gestion_caisse.php"><i class="fas fa-cash-register"></i> Caisses</a>
                <a href="liste_vente.php"><i class="fas fa-receipt"></i> Ventes</a>
                <a href="admin_patients.php"><i class="fas fa-users"></i> Patients</a>
                <a href="logout.php" class="btn-nav btn-danger-nav"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            <?php endif; ?>
        </nav>
        <?php if ($role !== 'visitor'): ?>
        <div class="user-badge">
            <i class="fas fa-user-circle"></i> <?php echo h($userName); ?>
        </div>
        <?php endif; ?>
    </div>
</header>
