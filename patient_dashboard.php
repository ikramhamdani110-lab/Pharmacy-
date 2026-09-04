<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Portail patient de la pharmacie">
    <meta name="keywords" content="pharmacie, patient, tableau de bord, historique, PharmaSanté, Algérie">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Espace Patient</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-page">
<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
requirePatient();
include 'header.php';

$userId = $_SESSION['user_id'];

$stPat = $conn->prepare("SELECT * FROM patient WHERE id=?");
$stPat->bind_param("i", $userId);
$stPat->execute();
$patient = $stPat->get_result()->fetch_assoc();

$stStats = $conn->prepare("SELECT COUNT(*) AS total_achats, COALESCE(SUM(prix_total),0) AS total_depense,
    COUNT(DISTINCT medicament_id) AS meds_distincts, MAX(date_vente) AS dernier_achat
    FROM vente WHERE patient_id=?");
$stStats->bind_param("i", $userId);
$stStats->execute();
$stats = $stStats->get_result()->fetch_assoc();

$stRecent = $conn->prepare("SELECT v.date_vente, m.nom AS mnom, v.quantite, v.prix_total, vd.nom AS vendeur_nom
    FROM vente v JOIN medicament m ON v.medicament_id=m.id JOIN vendeur vd ON v.vendeur_id=vd.id
    WHERE v.patient_id=? ORDER BY v.date_vente DESC LIMIT 5");
$stRecent->bind_param("i", $userId);
$stRecent->execute();
$recentPurchases = $stRecent->get_result();
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="patient-avatar"><i class="fas fa-user-circle"></i></div>
            <div class="patient-name"><?php echo h($patient['prenom'] . ' ' . $patient['nom']); ?></div>
        </div>
        <nav class="sidebar-nav">
            <a href="patient_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="patient_profile.php"><i class="fas fa-user"></i> Mon Profil</a>
            <a href="patient_historique.php"><i class="fas fa-history"></i> Mes Médicaments</a>
            <a href="index.php"><i class="fas fa-pills"></i> Voir les Médicaments</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="page-header">
            <h1><i class="fas fa-hand-sparkles"></i> Bienvenue, <?php echo h($patient['prenom'] . ' ' . $patient['nom']); ?> !</h1>
            <p>Votre espace santé personnel — <?php echo date('d/m/Y'); ?></p>
        </div>

        <div class="stats-grid">
            <div class="stat-card stat-green">
                <div class="stat-icon"><i class="fas fa-shopping-basket"></i></div>
                <div class="stat-info">
                    <span class="stat-value" data-target="<?php echo $stats['total_achats']; ?>">0</span>
                    <span class="stat-label">Total achats</span>
                </div>
            </div>
            <div class="stat-card stat-blue">
                <div class="stat-icon"><i class="fas fa-coins"></i></div>
                <div class="stat-info">
                    <span class="stat-value-text"><?php echo number_format($stats['total_depense'], 2, ',', ' '); ?> DA</span>
                    <span class="stat-label">Total dépensé</span>
                </div>
            </div>
            <div class="stat-card stat-purple">
                <div class="stat-icon"><i class="fas fa-pills"></i></div>
                <div class="stat-info">
                    <span class="stat-value" data-target="<?php echo $stats['meds_distincts']; ?>">0</span>
                    <span class="stat-label">Médicaments distincts</span>
                </div>
            </div>
            <div class="stat-card stat-orange">
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-info">
                    <span class="stat-value-text"><?php echo $stats['dernier_achat'] ? formatDate($stats['dernier_achat']) : '-'; ?></span>
                    <span class="stat-label">Dernier achat</span>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-card-header">
                <h2><i class="fas fa-history"></i> Achats récents</h2>
                <a href="patient_historique.php" class="btn btn-sm btn-outline">Voir tout l'historique</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Date</th><th>Médicament</th><th>Qté</th><th>Total (DA)</th><th>Vendeur</th></tr></thead>
                    <tbody>
                    <?php $hasRows = false; while ($r = $recentPurchases->fetch_assoc()): $hasRows = true; ?>
                        <tr>
                            <td><?php echo formatDate($r['date_vente']); ?></td>
                            <td><?php echo h($r['mnom']); ?></td>
                            <td><?php echo $r['quantite']; ?></td>
                            <td><strong><?php echo number_format($r['prix_total'], 2, ',', ' '); ?> DA</strong></td>
                            <td><?php echo h($r['vendeur_nom']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if (!$hasRows): ?>
                    <tr><td colspan="5" class="text-center">
                        <div class="empty-state-inline">
                            <i class="fas fa-shopping-basket"></i>
                            <p>Aucun achat enregistré. <a href="index.php">Voir les médicaments</a></p>
                        </div>
                    </td></tr>
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
