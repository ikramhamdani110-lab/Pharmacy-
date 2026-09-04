<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Liste de toutes les ventes de la pharmacie">
    <meta name="keywords" content="pharmacie, ventes, admin, historique, PharmaSanté, Algérie">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Toutes les Ventes</title>
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

$filterVendeur = intval($_GET['vendeur_id'] ?? 0);
$filterPatient = intval($_GET['patient_id'] ?? 0);
$dateFrom      = trim($_GET['date_from'] ?? '');
$dateTo        = trim($_GET['date_to'] ?? '');
$search        = trim($_GET['search'] ?? '');

$where = array("1=1");
$params = array();
$types  = '';

if ($filterVendeur > 0) { $where[] = "v.vendeur_id = ?"; $params[] = $filterVendeur; $types .= 'i'; }
if ($filterPatient > 0) { $where[] = "v.patient_id = ?"; $params[] = $filterPatient; $types .= 'i'; }
if ($dateFrom) { $where[] = "DATE(v.date_vente) >= ?"; $params[] = $dateFrom; $types .= 's'; }
if ($dateTo)   { $where[] = "DATE(v.date_vente) <= ?"; $params[] = $dateTo; $types .= 's'; }
if ($search) {
    $like = '%' . $search . '%';
    $where[] = "(p.nom LIKE ? OR p.prenom LIKE ? OR m.nom LIKE ?)";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'sss';
}

$whereStr = implode(' AND ', $where);

$sql = "SELECT v.*, p.nom AS pnom, p.prenom AS pprenom, m.nom AS mnom, m.categorie,
               vd.nom AS vendeur_nom, c.nom AS caisse_nom
        FROM vente v
        JOIN patient p ON v.patient_id = p.id
        JOIN medicament m ON v.medicament_id = m.id
        JOIN vendeur vd ON v.vendeur_id = vd.id
        JOIN caisse c ON v.caisse_id = c.id
        WHERE $whereStr ORDER BY v.date_vente DESC";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = mysqli_query($conn, $sql);
}

$grandTotal = 0;
$rows = array();
$res = ($result instanceof mysqli_result) ? $result : $result->get_result();
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
    $grandTotal += $r['prix_total'];
}

$vendeurs = mysqli_query($conn, "SELECT id, nom FROM vendeur WHERE role='vendeur' ORDER BY nom");
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fas fa-cog"></i> Administration</div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="gestion_vendeur.php"><i class="fas fa-user-tie"></i> Gérer les Vendeurs</a>
            <a href="gestion_medicament.php"><i class="fas fa-pills"></i> Gérer les Médicaments</a>
            <a href="gestion_caisse.php"><i class="fas fa-cash-register"></i> Gérer les Caisses</a>
            <a href="liste_vente.php" class="active"><i class="fas fa-receipt"></i> Toutes les Ventes</a>
            <a href="admin_patients.php"><i class="fas fa-users"></i> Voir les Patients</a>
            <a href="rapport_ventes.php"><i class="fas fa-chart-bar"></i> Rapport des Ventes</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="page-header"><h1><i class="fas fa-receipt"></i> Toutes les Ventes</h1></div>

        <div class="section-card">
            <form method="GET" class="filter-bar">
                <select name="vendeur_id" class="form-input form-input-sm">
                    <option value="">Tous les vendeurs</option>
                    <?php while ($vd = mysqli_fetch_assoc($vendeurs)): ?>
                    <option value="<?php echo $vd['id']; ?>" <?php echo $filterVendeur == $vd['id'] ? 'selected' : ''; ?>><?php echo h($vd['nom']); ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="date" name="date_from" class="form-input form-input-sm" value="<?php echo h($dateFrom); ?>" placeholder="Du">
                <input type="date" name="date_to" class="form-input form-input-sm" value="<?php echo h($dateTo); ?>" placeholder="Au">
                <input type="text" name="search" class="form-input form-input-sm" placeholder="Patient ou médicament..." value="<?php echo h($search); ?>">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrer</button>
                <a href="liste_vente.php" class="btn btn-outline btn-sm">Réinitialiser</a>
            </form>
        </div>

        <div class="section-card">
            <div class="section-card-header">
                <h2><i class="fas fa-list"></i> Résultats (<?php echo count($rows); ?> ventes)</h2>
                <button onclick="printList()" class="btn btn-outline btn-sm no-print"><i class="fas fa-print"></i> Imprimer</button>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>Date</th><th>Vendeur</th><th>Patient</th><th>Médicament</th><th>Catégorie</th><th>Qté</th><th>P.U (DA)</th><th>Total (DA)</th><th>Caisse</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $sale): ?>
                        <tr>
                            <td><?php echo formatDate($sale['date_vente']); ?></td>
                            <td><?php echo h($sale['vendeur_nom']); ?></td>
                            <td><?php echo h($sale['pprenom'] . ' ' . $sale['pnom']); ?></td>
                            <td><?php echo h($sale['mnom']); ?></td>
                            <td><?php echo h($sale['categorie']); ?></td>
                            <td><?php echo $sale['quantite']; ?></td>
                            <td><?php echo number_format($sale['prix_unitaire'], 2, ',', ' '); ?></td>
                            <td><strong><?php echo number_format($sale['prix_total'], 2, ',', ' '); ?></strong></td>
                            <td><?php echo h($sale['caisse_nom']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="9" class="text-center text-muted">Aucune vente trouvée.</td></tr>
                    <?php endif; ?>
                    </tbody>
                    <?php if (!empty($rows)): ?>
                    <tfoot>
                        <tr class="table-total">
                            <td colspan="7"><strong>TOTAL GÉNÉRAL</strong></td>
                            <td><strong><?php echo number_format($grandTotal, 2, ',', ' '); ?> DA</strong></td>
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
