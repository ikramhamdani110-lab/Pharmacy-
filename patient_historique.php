<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Historique d'achats du patient">
    <meta name="keywords" content="pharmacie, historique, achats, patient, médicament, PharmaSanté, Algérie">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Historique d'Achats</title>
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
$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$where = array("v.patient_id = ?");
$params = array($userId);
$types  = 'i';

switch ($filter) {
    case 'today': $where[] = "DATE(v.date_vente) = CURDATE()"; break;
    case 'week':  $where[] = "YEARWEEK(v.date_vente, 1) = YEARWEEK(CURDATE(), 1)"; break;
    case 'month': $where[] = "MONTH(v.date_vente) = MONTH(CURDATE()) AND YEAR(v.date_vente) = YEAR(CURDATE())"; break;
}
if ($search) {
    $like = '%' . $search . '%';
    $where[] = "m.nom LIKE ?";
    $params[] = $like;
    $types .= 's';
}

$whereStr = implode(' AND ', $where);
$sql = "SELECT v.*, m.nom AS mnom, m.categorie, vd.nom AS vendeur_nom
        FROM vente v
        JOIN medicament m ON v.medicament_id=m.id
        JOIN vendeur vd ON v.vendeur_id=vd.id
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

$stSummary = $conn->prepare("SELECT COUNT(*) AS total, COALESCE(SUM(prix_total),0) AS depense,
    MIN(date_vente) AS premier, MAX(date_vente) AS dernier
    FROM vente WHERE patient_id=?");
$stSummary->bind_param("i", $userId);
$stSummary->execute();
$summary = $stSummary->get_result()->fetch_assoc();

$stBest = $conn->prepare("SELECT m.nom, COUNT(*) AS cnt FROM vente v JOIN medicament m ON v.medicament_id=m.id WHERE v.patient_id=? GROUP BY v.medicament_id ORDER BY cnt DESC LIMIT 1");
$stBest->bind_param("i", $userId);
$stBest->execute();
$bestMed = $stBest->get_result()->fetch_assoc();

$stPat = $conn->prepare("SELECT * FROM patient WHERE id=?");
$stPat->bind_param("i", $userId);
$stPat->execute();
$patient = $stPat->get_result()->fetch_assoc();
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="patient-avatar"><i class="fas fa-user-circle"></i></div>
            <div class="patient-name"><?php echo h($patient['prenom'] . ' ' . $patient['nom']); ?></div>
        </div>
        <nav class="sidebar-nav">
            <a href="patient_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="patient_profile.php"><i class="fas fa-user"></i> Mon Profil</a>
            <a href="patient_historique.php" class="active"><i class="fas fa-history"></i> Mes Médicaments</a>
            <a href="index.php"><i class="fas fa-pills"></i> Voir les Médicaments</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="page-header">
            <h1><i class="fas fa-history"></i> Historique d'Achats</h1>
            <button onclick="printList()" class="btn btn-outline btn-sm no-print"><i class="fas fa-print"></i> Imprimer le reçu</button>
        </div>

        <div class="filter-tabs no-print">
            <a href="?filter=today" class="filter-tab <?php echo $filter==='today'?'active':''; ?>"><i class="fas fa-calendar-day"></i> Aujourd'hui</a>
            <a href="?filter=week"  class="filter-tab <?php echo $filter==='week'?'active':''; ?>"><i class="fas fa-calendar-week"></i> Cette semaine</a>
            <a href="?filter=month" class="filter-tab <?php echo $filter==='month'?'active':''; ?>"><i class="fas fa-calendar-alt"></i> Ce mois</a>
            <a href="?filter=all"   class="filter-tab <?php echo $filter==='all'?'active':''; ?>"><i class="fas fa-infinity"></i> Tout</a>
        </div>

        <div class="section-card no-print">
            <form method="GET" class="search-form">
                <input type="hidden" name="filter" value="<?php echo h($filter); ?>">
                <input type="text" name="search" class="form-input" placeholder="Rechercher un médicament..." value="<?php echo h($search); ?>">
                <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-search"></i></button>
                <?php if ($search): ?><a href="?filter=<?php echo h($filter); ?>" class="btn btn-sm btn-outline">Réinitialiser</a><?php endif; ?>
            </form>
        </div>

        <div class="section-card">
            <div class="section-card-header"><h2><i class="fas fa-list"></i> Mes achats (<?php echo count($rows); ?>)</h2></div>
            <?php if (empty($rows)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-basket"></i>
                <p>Aucun achat trouvé pour cette période.</p>
                <a href="index.php" class="btn btn-primary">Voir les médicaments</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>#</th><th>Date et heure</th><th>Médicament</th><th>Catégorie</th><th>Qté</th><th>Prix unit.</th><th>Total (DA)</th><th>Servi par</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $i => $r): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo formatDate($r['date_vente']); ?></td>
                            <td><?php echo h($r['mnom']); ?></td>
                            <td><?php echo h($r['categorie']); ?></td>
                            <td><?php echo $r['quantite']; ?></td>
                            <td><?php echo number_format($r['prix_unitaire'], 2, ',', ' '); ?> DA</td>
                            <td><strong><?php echo number_format($r['prix_total'], 2, ',', ' '); ?> DA</strong></td>
                            <td><?php echo h($r['vendeur_nom']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-total">
                            <td colspan="6"><strong>TOTAL</strong></td>
                            <td><strong><?php echo number_format($grandTotal, 2, ',', ' '); ?> DA</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="summary-grid">
                <div class="summary-item"><span>Total achats (global)</span><strong><?php echo $summary['total']; ?> fois</strong></div>
                <div class="summary-item"><span>Total dépensé (global)</span><strong><?php echo number_format($summary['depense'], 2, ',', ' '); ?> DA</strong></div>
                <div class="summary-item"><span>Médicament le + acheté</span><strong><?php echo $bestMed ? h($bestMed['nom']) : '-'; ?></strong></div>
                <div class="summary-item"><span>Premier achat</span><strong><?php echo $summary['premier'] ? formatDate($summary['premier']) : '-'; ?></strong></div>
                <div class="summary-item"><span>Dernier achat</span><strong><?php echo $summary['dernier'] ? formatDate($summary['dernier']) : '-'; ?></strong></div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
</body>
</html>
