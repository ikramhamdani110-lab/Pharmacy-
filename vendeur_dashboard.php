<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Tableau de bord vendeur de la pharmacie">
    <meta name="keywords" content="pharmacie, vendeur, tableau de bord, caisse, vente, PharmaSanté, Algérie">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Tableau de bord Vendeur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-page">
<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
requireVendeur();
include 'header.php';

$userId = $_SESSION['user_id'];

$openCaisse = getOpenCaisseForVendeur($conn, $userId);
$totalPatients = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM patient"))[0];

$stToday = $conn->prepare("SELECT COUNT(*) FROM vente WHERE vendeur_id=? AND DATE(date_vente)=CURDATE()");
$stToday->bind_param("i", $userId);
$stToday->execute();
$todaySales = $stToday->get_result()->fetch_row()[0];

$stRev = $conn->prepare("SELECT COALESCE(SUM(prix_total),0) FROM vente WHERE vendeur_id=? AND DATE(date_vente)=CURDATE()");
$stRev->bind_param("i", $userId);
$stRev->execute();
$todayRevenue = $stRev->get_result()->fetch_row()[0];

$stRecent = $conn->prepare("SELECT v.date_vente, p.nom AS pnom, p.prenom AS pprenom, m.nom AS mnom, v.quantite, v.prix_total
    FROM vente v
    JOIN patient p ON v.patient_id=p.id
    JOIN medicament m ON v.medicament_id=m.id
    WHERE v.vendeur_id=? ORDER BY v.date_vente DESC LIMIT 5");
$stRecent->bind_param("i", $userId);
$stRecent->execute();
$recentSales = $stRecent->get_result();
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fas fa-store"></i> Vendeur</div>
        <nav class="sidebar-nav">
            <a href="vendeur_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="caisse_operation.php"><i class="fas fa-cash-register"></i> Gestion Caisse</a>
            <a href="gestion_patient.php"><i class="fas fa-users"></i> Gérer les Patients</a>
            <a href="ajouter_vente.php"><i class="fas fa-shopping-cart"></i> Nouvelle Vente</a>
            <a href="mes_ventes.php"><i class="fas fa-receipt"></i> Mes Ventes</a>
            <a href="recherche_medicament.php"><i class="fas fa-search"></i> Rechercher Médicament</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="page-header">
            <h1><i class="fas fa-tachometer-alt"></i> Tableau de bord</h1>
            <p>Bienvenue, <strong><?php echo h($_SESSION['user_name']); ?></strong> — <?php echo date('d/m/Y'); ?></p>
        </div>

        <!-- Cash register status -->
        <?php if ($openCaisse): ?>
        <div class="caisse-status caisse-open">
            <div class="caisse-status-icon"><i class="fas fa-cash-register"></i></div>
            <div class="caisse-status-info">
                <strong><?php echo h($openCaisse['nom']); ?></strong>
                <span class="badge badge-success">OUVERTE</span>
                <p>Ouverte le <?php echo formatDate($openCaisse['heure_ouverture']); ?> — Montant: <?php echo number_format($openCaisse['montant_ouverture'], 2, ',', ' '); ?> DA</p>
                <span id="timerDisplay" class="caisse-timer"></span>
            </div>
            <a href="caisse_operation.php" class="btn btn-danger">Fermer la caisse</a>
        </div>
        <?php else: ?>
        <div class="caisse-status caisse-closed">
            <div class="caisse-status-icon"><i class="fas fa-cash-register"></i></div>
            <div class="caisse-status-info">
                <strong>Aucune caisse ouverte</strong>
                <span class="badge badge-danger">FERMÉE</span>
                <p>Ouvrez une caisse pour enregistrer des ventes.</p>
            </div>
            <a href="caisse_operation.php" class="btn btn-primary">Ouvrir une caisse</a>
        </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card stat-purple">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <span class="stat-value" data-target="<?php echo $totalPatients; ?>">0</span>
                    <span class="stat-label">Patients inscrits</span>
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
        </div>

        <div class="section-card">
            <div class="section-card-header">
                <h2><i class="fas fa-history"></i> Mes ventes récentes</h2>
                <a href="mes_ventes.php" class="btn btn-sm btn-outline">Voir tout</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Date</th><th>Patient</th><th>Médicament</th><th>Qté</th><th>Total (DA)</th></tr></thead>
                    <tbody>
                    <?php $hasRows = false; while ($s = $recentSales->fetch_assoc()): $hasRows = true; ?>
                        <tr>
                            <td><?php echo formatDate($s['date_vente']); ?></td>
                            <td><?php echo h($s['pprenom'] . ' ' . $s['pnom']); ?></td>
                            <td><?php echo h($s['mnom']); ?></td>
                            <td><?php echo $s['quantite']; ?></td>
                            <td><strong><?php echo number_format($s['prix_total'], 2, ',', ' '); ?> DA</strong></td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if (!$hasRows): ?><tr><td colspan="5" class="text-center text-muted">Aucune vente enregistrée.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
<script>
<?php if ($openCaisse): ?>
var openTime = new Date('<?php echo $openCaisse['heure_ouverture']; ?>');
updateTimer(openTime);
setInterval(function(){ updateTimer(openTime); }, 1000);
<?php endif; ?>
document.addEventListener('DOMContentLoaded', function(){ countUp(); });
</script>
</body>
</html>
