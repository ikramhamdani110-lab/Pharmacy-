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
        <div class="hero-copy">
            <div class="eyebrow"><i class="fas fa-heartbeat"></i> Santé digitale</div>
            <h1><i class="fas fa-pills"></i> Votre pharmacie, pensée pour mieux soigner.</h1>
            <p>Accédez à une expérience moderne pour retrouver les médicaments, suivre votre santé et gérer efficacement les besoins de votre famille.</p>
            <div class="hero-btns">
                <a href="register_patient.php" class="btn btn-white"><i class="fas fa-user-plus"></i> S'inscrire</a>
                <a href="login.php" class="btn btn-outline-white"><i class="fas fa-sign-in-alt"></i> Connexion</a>
            </div>
            <div class="hero-pills">
                <span><i class="fas fa-shield-alt"></i> Sécurité</span>
                <span><i class="fas fa-clock"></i> Service rapide</span>
                <span><i class="fas fa-stethoscope"></i> Conseils</span>
            </div>
        </div>
        <div class="hero-panel">
            <div class="hero-panel-card">
                <div class="section-card-header" style="margin-bottom: 0; border-bottom: 0; padding-bottom: 0;">
                    <h2 style="color: #fff; font-size: 1rem; margin: 0;">PharmaSanté</h2>
                    <span class="badge badge-success" style="background: rgba(16,185,129,0.18); color: #ccfbf1; border: 1px solid rgba(255,255,255,.08);">En ligne</span>
                </div>
                <div class="hero-stat-grid">
                    <div class="hero-stat">
                        <strong>250+</strong>
                        <span>Médicaments</span>
                    </div>
                    <div class="hero-stat">
                        <strong>24/7</strong>
                        <span>Disponibilité</span>
                    </div>
                    <div class="hero-stat">
                        <strong>3.8k</strong>
                        <span>Patients</span>
                    </div>
                    <div class="hero-stat">
                        <strong>98%</strong>
                        <span>Satisfaction</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="container">
    <section class="feature-strip">
        <div class="feature-item">
            <i class="fas fa-notes-medical"></i>
            <div>
                <strong>Prescription claire</strong>
                <span>Informations faciles à lire et exploiter</span>
            </div>
        </div>
        <div class="feature-item">
            <i class="fas fa-shield-heart"></i>
            <div>
                <strong>Produits de confiance</strong>
                <span>Un catalogue fiable et contrôlé</span>
            </div>
        </div>
        <div class="feature-item">
            <i class="fas fa-chart-line"></i>
            <div>
                <strong>Suivi intelligent</strong>
                <span>Des ventes et achats plus transparents</span>
            </div>
        </div>
    </section>

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
