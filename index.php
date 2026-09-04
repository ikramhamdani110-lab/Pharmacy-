<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$result = mysqli_query($conn, "SELECT * FROM medicament ORDER BY nom ASC");
$medicaments = array();
while ($row = mysqli_fetch_assoc($result)) {
    $medicaments[] = $row;
}

$catResult = mysqli_query($conn, "SELECT DISTINCT categorie FROM medicament WHERE categorie IS NOT NULL ORDER BY categorie");
$categories = array();
while ($row = mysqli_fetch_assoc($catResult)) {
    $categories[] = $row['categorie'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaSanté - Accueil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<section class="hero">
    <div class="hero-content">
        <h1><i class="fas fa-pills"></i> PharmaSanté</h1>
        <p>Votre santé, notre priorité</p>
        <div class="hero-btns">
            <a href="register_patient.php" class="btn btn-white"><i class="fas fa-user-plus"></i> S'inscrire</a>
            <a href="login.php" class="btn btn-outline-white"><i class="fas fa-sign-in-alt"></i> Connexion</a>
        </div>
    </div>
</section>

<main class="container">
    <section class="medicines-section">
        <h2 class="section-title"><i class="fas fa-capsules"></i> Nos Médicaments</h2>
        <div class="search-filter-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher un médicament..." onkeyup="liveMedicineSearch()" class="form-input">
            </div>
        </div>
        <div class="category-filters" id="categoryFilters">
            <button class="filter-btn active" onclick="filterByCategory('tous', this)">Tous</button>
            <?php foreach ($categories as $cat): ?>
            <button class="filter-btn" onclick="filterByCategory('<?php echo h($cat); ?>', this)"><?php echo h($cat); ?></button>
            <?php endforeach; ?>
        </div>
        <?php if (empty($medicaments)): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <p>Aucun médicament disponible pour le moment.</p>
        </div>
        <?php else: ?>
        <div class="medicine-grid" id="medicineGrid">
            <?php foreach ($medicaments as $med): ?>
            <div class="medicine-card" data-category="<?php echo h($med['categorie']); ?>" data-name="<?php echo strtolower(h($med['nom'])); ?>">
                <div class="medicine-card-header">
                    <span class="category-badge"><?php echo h($med['categorie']); ?></span>
                    <?php echo getStockBadge($med['quantite_stock']); ?>
                </div>
                <div class="medicine-card-body">
                    <h3><?php echo h($med['nom']); ?></h3>
                    <p class="medicine-desc"><?php echo h(truncate($med['description'], 100)); ?></p>
                </div>
                <div class="medicine-card-footer">
                    <span class="medicine-price"><i class="fas fa-tag"></i> <?php echo number_format($med['prix_dinar'], 2, ',', ' '); ?> DA</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
</main>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
</body>
</html>
