<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Tableau de bord administrateur">
    <meta name="keywords" content="pharmacie, admin, tableau de bord, gestion, PharmaSanté, Algérie">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Tableau de bord Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-page">
<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
requireAdmin();
include 'header.php';

$totalVendeurs   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM vendeur WHERE role='vendeur'"))[0];
$totalMeds       = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM medicament"))[0];
$totalPatients   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM patient"))[0];
$todaySales      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM vente WHERE DATE(date_vente)=CURDATE()"))[0];
$todayRevenue    = mysqli_fetch_row(mysqli_query($conn, "SELECT COALESCE(SUM(prix_total),0) FROM vente WHERE DATE(date_vente)=CURDATE()"))[0];
$outOfStock      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM medicament WHERE quantite_stock=0"))[0];
$recentSales = mysqli_query($conn, "
    SELECT v.date_vente, vd.nom AS vendeur, p.nom AS pnom, p.prenom AS pprenom,
           m.nom AS medicament, v.quantite, v.prix_total
    FROM vente v
    JOIN vendeur vd ON v.vendeur_id = vd.id
    JOIN patient p ON v.patient_id = p.id
    JOIN medicament m ON v.medicament_id = m.id
    ORDER BY v.date_vente DESC LIMIT 5
");
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fas fa-cog"></i> Administration</div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="gestion_vendeur.php"><i class="fas fa-user-tie"></i> Gérer les Vendeurs</a>
            <a href="gestion_medicament.php"><i class="fas fa-pills"></i> Gérer les Médicaments</a>
            <a href="gestion_caisse.php"><i class="fas fa-cash-register"></i> Gérer les Caisses</a>
            <a href="admin_profile.php"><i class="fas fa-address-card"></i> Profil / Informations</a>
            <a href="liste_vente.php"><i class="fas fa-receipt"></i> Toutes les Ventes</a>
            <a href="admin_patients.php"><i class="fas fa-users"></i> Voir les Patients</a>
            <a href="vendeur_dashboard.php"><i class="fas fa-eye"></i> Vue Vendeur</a>
            <a href="rapport_ventes.php"><i class="fas fa-chart-bar"></i> Rapport des Ventes</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="page-header">
            <h1><i class="fas fa-tachometer-alt"></i> Tableau de bord</h1>
            <p>Bienvenue, <strong><?php echo h($_SESSION['user_name']); ?></strong> — <?php echo date('d/m/Y'); ?></p>
        </div>

        <div class="stats-grid">
            <div class="stat-card stat-blue">
                <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                <div class="stat-info">
                    <span class="stat-value" data-target="<?php echo $totalVendeurs; ?>">0</span>
                    <span class="stat-label">Vendeurs</span>
                </div>
            </div>
            <div class="stat-card stat-green">
                <div class="stat-icon"><i class="fas fa-pills"></i></div>
                <div class="stat-info">
                    <span class="stat-value" data-target="<?php echo $totalMeds; ?>">0</span>
                    <span class="stat-label">Médicaments</span>
                </div>
            </div>
            <div class="stat-card stat-purple">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <span class="stat-value" data-target="<?php echo $totalPatients; ?>">0</span>
                    <span class="stat-label">Patients</span>
                </div>
            </div>
            <div class="stat-card stat-orange">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-info">
                    <span class="stat-value" data-target="<?php echo $todaySales; ?>">0</span>
                    <span class="stat-label">Ventes aujourd'hui</span>
                </div>
            </div>
            <div class="stat-card stat-teal">
                <div class="stat-icon"><i class="fas fa-coins"></i></div>
                <div class="stat-info">
                    <span class="stat-value-text"><?php echo number_format($todayRevenue, 2, ',', ' '); ?> DA</span>
                    <span class="stat-label">Recette du jour</span>
                </div>
            </div>
            <div class="stat-card stat-red">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-info">
                    <span class="stat-value" data-target="<?php echo $outOfStock; ?>">0</span>
                    <span class="stat-label">Médicaments épuisés</span>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-card-header">
                <h2><i class="fas fa-history"></i> Ventes récentes</h2>
                <a href="liste_vente.php" class="btn btn-sm btn-outline">Voir tout</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Vendeur</th>
                            <th>Patient</th>
                            <th>Médicament</th>
                            <th>Qté</th>
                            <th>Total (DA)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $hasRows = false;
                    while ($sale = mysqli_fetch_assoc($recentSales)):
                        $hasRows = true;
                    ?>
                        <tr>
                            <td><?php echo formatDate($sale['date_vente']); ?></td>
                            <td><?php echo h($sale['vendeur']); ?></td>
                            <td><?php echo h($sale['pprenom'] . ' ' . $sale['pnom']); ?></td>
                            <td><?php echo h($sale['medicament']); ?></td>
                            <td><?php echo $sale['quantite']; ?></td>
                            <td><strong><?php echo number_format($sale['prix_total'], 2, ',', ' '); ?> DA</strong></td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if (!$hasRows): ?>
                        <tr><td colspan="6" class="text-center text-muted">Aucune vente enregistrée.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
<script>document.addEventListener('DOMContentLoaded', function(){ countUp(); });</script>
</body>
</html>
