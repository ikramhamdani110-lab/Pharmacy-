<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Gestion des médicaments de la pharmacie">
    <meta name="keywords" content="pharmacie, médicament, gestion, stock, PharmaSanté, Algérie">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Gestion des Médicaments</title>
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

$message = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $nom    = trim($_POST['nom']);
        $desc   = trim($_POST['description']);
        $prix   = floatval($_POST['prix_dinar']);
        $stock  = intval($_POST['quantite_stock']);
        $cat    = trim($_POST['categorie']);
        if ($nom && $prix >= 0) {
            $stmt = $conn->prepare("INSERT INTO medicament (nom, description, prix_dinar, quantite_stock, categorie) VALUES (?,?,?,?,?)");
            $stmt->bind_param("ssdis", $nom, $desc, $prix, $stock, $cat);
            if ($stmt->execute()) { $message = "Médicament ajouté."; $msgType = 'success'; }
            else { $message = "Erreur: " . $conn->error; $msgType = 'danger'; }
        } else { $message = "Nom et prix sont obligatoires."; $msgType = 'warning'; }
    }

    if ($action === 'edit') {
        $id    = intval($_POST['id']);
        $nom   = trim($_POST['nom']);
        $desc  = trim($_POST['description']);
        $prix  = floatval($_POST['prix_dinar']);
        $stock = intval($_POST['quantite_stock']);
        $cat   = trim($_POST['categorie']);
        $stmt  = $conn->prepare("UPDATE medicament SET nom=?, description=?, prix_dinar=?, quantite_stock=?, categorie=? WHERE id=?");
        $stmt->bind_param("ssdiis", $nom, $desc, $prix, $stock, $cat, $id);
        if ($stmt->execute()) { $message = "Médicament modifié."; $msgType = 'success'; }
        else { $message = "Erreur: " . $conn->error; $msgType = 'danger'; }
    }

    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM medicament WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) { $message = "Médicament supprimé."; $msgType = 'success'; }
        else { $message = "Impossible de supprimer ce médicament (ventes liées)."; $msgType = 'danger'; }
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page   = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

if ($search) {
    $like = '%' . $search . '%';
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM medicament WHERE nom LIKE ? OR categorie LIKE ?");
    $countStmt->bind_param("ss", $like, $like);
    $countStmt->execute();
    $totalRows = $countStmt->get_result()->fetch_row()[0];
    $stmt = $conn->prepare("SELECT * FROM medicament WHERE nom LIKE ? OR categorie LIKE ? ORDER BY nom ASC LIMIT ? OFFSET ?");
    $stmt->bind_param("ssii", $like, $like, $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $totalRows = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM medicament"))[0];
    $stmt = $conn->prepare("SELECT * FROM medicament ORDER BY nom ASC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}
$totalPages = ceil($totalRows / $perPage);
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fas fa-cog"></i> Administration</div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="gestion_vendeur.php"><i class="fas fa-user-tie"></i> Gérer les Vendeurs</a>
            <a href="gestion_medicament.php" class="active"><i class="fas fa-pills"></i> Gérer les Médicaments</a>
            <a href="gestion_caisse.php"><i class="fas fa-cash-register"></i> Gérer les Caisses</a>
            <a href="liste_vente.php"><i class="fas fa-receipt"></i> Toutes les Ventes</a>
            <a href="admin_patients.php"><i class="fas fa-users"></i> Voir les Patients</a>
            <a href="rapport_ventes.php"><i class="fas fa-chart-bar"></i> Rapport des Ventes</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="page-header"><h1><i class="fas fa-pills"></i> Gestion des Médicaments</h1></div>

        <?php if ($message): echo alert($msgType, $message); endif; ?>

        <div class="section-card">
            <div class="section-card-header"><h2><i class="fas fa-plus"></i> Ajouter un médicament</h2></div>
            <form method="POST" class="inline-form">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group"><label>Nom</label><input type="text" name="nom" class="form-input" required></div>
                    <div class="form-group"><label>Catégorie</label>
                        <input type="text" name="categorie" class="form-input" list="cat-list">
                        <datalist id="cat-list">
                            <option value="Analgésiques"><option value="Antibiotiques">
                            <option value="Anti-inflammatoires"><option value="Vitamines">
                        </datalist>
                    </div>
                    <div class="form-group"><label>Prix (DA)</label><input type="number" name="prix_dinar" class="form-input" step="0.01" min="0" required></div>
                    <div class="form-group"><label>Stock</label><input type="number" name="quantite_stock" class="form-input" min="0" value="0"></div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-input" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter</button>
            </form>
        </div>

        <div class="section-card">
            <div class="section-card-header">
                <h2><i class="fas fa-list"></i> Liste (<?php echo $totalRows; ?>)</h2>
                <form method="GET" class="search-form">
                    <input type="text" name="search" class="form-input" placeholder="Rechercher..." value="<?php echo h($search); ?>">
                    <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-search"></i></button>
                    <?php if ($search): ?><a href="gestion_medicament.php" class="btn btn-sm btn-outline">Réinitialiser</a><?php endif; ?>
                </form>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>ID</th><th>Nom</th><th>Catégorie</th><th>Description</th><th>Prix (DA)</th><th>Stock</th><th>Ajouté le</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php $hasRows = false; while ($m = $result->fetch_assoc()): $hasRows = true; ?>
                        <tr>
                            <td><?php echo $m['id']; ?></td>
                            <td><strong><?php echo h($m['nom']); ?></strong></td>
                            <td><?php echo h($m['categorie']); ?></td>
                            <td><?php echo h(truncate($m['description'], 60)); ?></td>
                            <td><?php echo number_format($m['prix_dinar'], 2, ',', ' '); ?></td>
                            <td><?php echo getStockBadge($m['quantite_stock']); ?> <?php echo $m['quantite_stock']; ?></td>
                            <td><?php echo formatDate($m['date_ajout']); ?></td>
                            <td class="actions">
                                <button class="btn btn-sm btn-outline" onclick="openMedEditModal(<?php echo $m['id']; ?>,'<?php echo h(addslashes($m['nom'])); ?>','<?php echo h(addslashes($m['description'])); ?>',<?php echo $m['prix_dinar']; ?>,<?php echo $m['quantite_stock']; ?>,'<?php echo h(addslashes($m['categorie'])); ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteConfirmation(<?php echo $m['id']; ?>, 'delete-form-med')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if (!$hasRows): ?><tr><td colspan="8" class="text-center text-muted">Aucun médicament trouvé.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="page-link <?php echo $i==$page?'active':''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<form method="POST" id="delete-form-med" style="display:none">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete-id-med">
</form>

<div class="modal-overlay" id="editMedModal">
    <div class="modal">
        <div class="modal-header"><h3><i class="fas fa-edit"></i> Modifier le médicament</h3><button onclick="closeModal('editMedModal')" class="modal-close">&times;</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="med-edit-id">
            <div class="form-row">
                <div class="form-group"><label>Nom</label><input type="text" name="nom" id="med-edit-nom" class="form-input" required></div>
                <div class="form-group"><label>Catégorie</label><input type="text" name="categorie" id="med-edit-cat" class="form-input"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Prix (DA)</label><input type="number" name="prix_dinar" id="med-edit-prix" class="form-input" step="0.01" min="0"></div>
                <div class="form-group"><label>Stock</label><input type="number" name="quantite_stock" id="med-edit-stock" class="form-input" min="0"></div>
            </div>
            <div class="form-group"><label>Description</label><textarea name="description" id="med-edit-desc" class="form-input" rows="3"></textarea></div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('editMedModal')" class="btn btn-outline">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="confirmModal">
    <div class="modal modal-sm">
        <div class="modal-header"><h3><i class="fas fa-exclamation-triangle"></i> Confirmation</h3></div>
        <div class="modal-body"><p>Êtes-vous sûr de vouloir supprimer ce médicament ?</p></div>
        <div class="modal-footer">
            <button onclick="closeModal('confirmModal')" class="btn btn-outline">Annuler</button>
            <button onclick="confirmDelete()" class="btn btn-danger">Supprimer</button>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
<script>
function openMedEditModal(id, nom, desc, prix, stock, cat) {
    document.getElementById('med-edit-id').value = id;
    document.getElementById('med-edit-nom').value = nom;
    document.getElementById('med-edit-desc').value = desc;
    document.getElementById('med-edit-prix').value = prix;
    document.getElementById('med-edit-stock').value = stock;
    document.getElementById('med-edit-cat').value = cat;
    document.getElementById('editMedModal').classList.add('active');
}
</script>
</body>
</html>
