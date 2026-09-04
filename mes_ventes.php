<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Mes ventes enregistrées par le vendeur">
    <meta name="keywords" content="pharmacie, ventes, vendeur, historique, PharmaSanté, Algérie">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Mes Ventes</title>
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
$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo   = trim($_GET['date_to'] ?? '');

$where = array("v.vendeur_id = ?");
$params = array($userId);
$types  = 'i';

switch ($filter) {
    case 'today': $where[] = "DATE(v.date_vente) = CURDATE()"; break;
    case 'week':  $where[] = "YEARWEEK(v.date_vente, 1) = YEARWEEK(CURDATE(), 1)"; break;
    case 'month': $where[] = "MONTH(v.date_vente) = MONTH(CURDATE()) AND YEAR(v.date_vente) = YEAR(CURDATE())"; break;
}
if ($dateFrom) { $where[] = "DATE(v.date_vente) >= ?"; $params[] = $dateFrom; $types .= 's'; }
if ($dateTo)   { $where[] = "DATE(v.date_vente) <= ?"; $params[] = $dateTo; $types .= 's'; }
if ($search) {
    $like = '%' . $search . '%';
    $where[] = "(p.nom LIKE ? OR p.prenom LIKE ? OR m.nom LIKE ?)";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'sss';
}

$whereStr = implode(' AND ', $where);

$sql = "SELECT v.*, p.nom AS pnom, p.prenom AS pprenom, m.nom AS mnom, m.categorie
        FROM vente v
        JOIN patient p ON v.patient_id=p.id
        JOIN medicament m ON v.medicament_id=m.id
        WHERE $whereStr ORDER BY v.date_vente DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$rows = array();
$grandTotal = 0;
while ($r = $result->fetch_assoc()) {
    $rows[] = $r;
    $grandTotal += $r['prix_total'];
}
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fas fa-store"></i> Vendeur</div>
        <nav class="sidebar-nav">
            <a href="vendeur_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="caisse_operation.php"><i class="fas fa-cash-register"></i> Gestion Caisse</a>
            <a href="gestion_patient.php"><i class="fas fa-users"></i> Gérer les Patients</a>
            <a href="ajouter_vente.php"><i class="fas fa-shopping-cart"></i> Nouvelle Vente</a>
            <a href="mes_ventes.php" class="active"><i class="fas fa-receipt"></i> Mes Ventes</a>
            <a href="recherche_medicament.php"><i class="fas fa-search"></i> Rechercher Médicament</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="page-header"><h1><i class="fas fa-receipt"></i> Mes Ventes</h1></div>

        <div class="filter-tabs no-print">
            <a href="?filter=today" class="filter-tab <?php echo $filter==='today'?'active':''; ?>"><i class="fas fa-calendar-day"></i> Aujourd'hui</a>
            <a href="?filter=week"  class="filter-tab <?php echo $filter==='week'?'active':''; ?>"><i class="fas fa-calendar-week"></i> Cette semaine</a>
            <a href="?filter=month" class="filter-tab <?php echo $filter==='month'?'active':''; ?>"><i class="fas fa-calendar-alt"></i> Ce mois</a>
            <a href="?filter=all"   class="filter-tab <?php echo $filter==='all'?'active':''; ?>"><i class="fas fa-infinity"></i> Tout</a>
        </div>

        <div class="section-card no-print">
            <form method="GET" class="filter-bar">
                <input type="hidden" name="filter" value="<?php echo h($filter); ?>">
                <input type="text" name="search" class="form-input form-input-sm" placeholder="Patient ou médicament..." value="<?php echo h($search); ?>">
                <input type="date" name="date_from" class="form-input form-input-sm" value="<?php echo h($dateFrom); ?>">
                <input type="date" name="date_to" class="form-input form-input-sm" value="<?php echo h($dateTo); ?>">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filtrer</button>
                <a href="mes_ventes.php" class="btn btn-outline btn-sm">Réinitialiser</a>
            </form>
        </div>

        <div class="section-card">
            <div class="section-card-header">
                <h2><i class="fas fa-list"></i> Résultats (<?php echo count($rows); ?> ventes)</h2>
                <button onclick="printList()" class="btn btn-outline btn-sm no-print"><i class="fas fa-print"></i> Imprimer</button>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Date</th><th>Patient</th><th>Médicament</th><th>Catégorie</th><th>Qté</th><th>Prix unit.</th><th>Total (DA)</th><th>Notes</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?php echo formatDate($r['date_vente']); ?></td>
                            <td><?php echo h($r['pprenom'] . ' ' . $r['pnom']); ?></td>
                            <td><?php echo h($r['mnom']); ?></td>
                            <td><?php echo h($r['categorie']); ?></td>
                            <td><?php echo $r['quantite']; ?></td>
                            <td><?php echo number_format($r['prix_unitaire'], 2, ',', ' '); ?> DA</td>
                            <td><strong><?php echo number_format($r['prix_total'], 2, ',', ' '); ?> DA</strong></td>
                            <td><?php echo h($r['notes'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rows)): ?><tr><td colspan="8" class="text-center text-muted">Aucune vente trouvée.</td></tr><?php endif; ?>
                    </tbody>
                    <?php if (!empty($rows)): ?>
                    <tfoot>
                        <tr class="table-total">
                            <td colspan="4"><strong>TOTAL — <?php echo count($rows); ?> transactions</strong></td>
                            <td colspan="3"><strong><?php echo number_format($grandTotal, 2, ',', ' '); ?> DA</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
</body>
</html>
