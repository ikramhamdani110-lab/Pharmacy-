<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Recherche de médicaments par le vendeur">
    <meta name="keywords" content="pharmacie, médicament, recherche, vendeur, stock, PharmaSanté, Algérie">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Recherche Médicaments</title>
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

$search = trim($_GET['search'] ?? '');
$catFilter = trim($_GET['categorie'] ?? '');

$where = array("1=1");
$params = array(); $types = '';
if ($search) { $like = '%' . $search . '%'; $where[] = "nom LIKE ?"; $params[] = $like; $types .= 's'; }
if ($catFilter) { $where[] = "categorie = ?"; $params[] = $catFilter; $types .= 's'; }

$whereStr = implode(' AND ', $where);
$sql = "SELECT * FROM medicament WHERE $whereStr ORDER BY nom ASC";
if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = mysqli_query($conn, $sql);
}

$cats = mysqli_query($conn, "SELECT DISTINCT categorie FROM medicament WHERE categorie IS NOT NULL ORDER BY categorie");
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fas fa-store"></i> Vendeur</div>
        <nav class="sidebar-nav">
            <a href="vendeur_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="caisse_operation.php"><i class="fas fa-cash-register"></i> Gestion Caisse</a>
            <a href="gestion_patient.php"><i class="fas fa-users"></i> Gérer les Patients</a>
            <a href="ajouter_vente.php"><i class="fas fa-shopping-cart"></i> Nouvelle Vente</a>
            <a href="mes_ventes.php"><i class="fas fa-receipt"></i> Mes Ventes</a>
            <a href="recherche_medicament.php" class="active"><i class="fas fa-search"></i> Rechercher Médicament</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="page-header"><h1><i class="fas fa-search"></i> Recherche de Médicaments</h1></div>

        <div class="section-card">
            <form method="GET" class="search-big-form">
                <div class="search-box search-box-lg">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-input" placeholder="Nom du médicament..." value="<?php echo h($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Rechercher</button>
                <?php if ($search || $catFilter): ?><a href="recherche_medicament.php" class="btn btn-outline">Réinitialiser</a><?php endif; ?>
            </form>
        </div>

        <div class="category-filters">
            <a href="recherche_medicament.php?search=<?php echo urlencode($search); ?>" class="filter-btn <?php echo !$catFilter?'active':''; ?>">Tous</a>
            <?php while ($c = mysqli_fetch_assoc($cats)): ?>
            <a href="?search=<?php echo urlencode($search); ?>&categorie=<?php echo urlencode($c['categorie']); ?>" class="filter-btn <?php echo $catFilter === $c['categorie'] ? 'active' : ''; ?>"><?php echo h($c['categorie']); ?></a>
            <?php endwhile; ?>
        </div>

        <div class="medicine-grid">
        <?php
        $hasRows = false;
        $res = ($result instanceof mysqli_result) ? $result : $result->get_result();
        while ($m = $res->fetch_assoc()): $hasRows = true; ?>
            <div class="medicine-card">
                <div class="medicine-card-header">
                    <span class="category-badge"><?php echo h($m['categorie']); ?></span>
                    <?php echo getStockBadge($m['quantite_stock']); ?>
                </div>
                <div class="medicine-card-body">
                    <h3><?php echo h($m['nom']); ?></h3>
                    <p class="medicine-desc"><?php echo h(truncate($m['description'], 80)); ?></p>
                    <p class="text-muted">Stock: <?php echo $m['quantite_stock']; ?> unités</p>
                </div>
                <div class="medicine-card-footer">
                    <span class="medicine-price"><i class="fas fa-tag"></i> <?php echo number_format($m['prix_dinar'], 2, ',', ' '); ?> DA</span>
                    <?php if ($m['quantite_stock'] > 0): ?>
                    <a href="ajouter_vente.php?medicament_id=<?php echo $m['id']; ?>" class="btn btn-sm btn-primary">Sélectionner pour vente</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
        <?php if (!$hasRows): ?>
        <div class="empty-state" style="grid-column:1/-1">
            <i class="fas fa-box-open"></i>
            <p>Aucun médicament trouvé pour "<?php echo h($search); ?>".</p>
        </div>
        <?php endif; ?>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
</body>
</html>
