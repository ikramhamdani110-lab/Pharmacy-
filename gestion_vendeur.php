<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Gestion des vendeurs de la pharmacie">
    <meta name="keywords" content="pharmacie, vendeur, gestion, admin, PharmaSanté, Algérie">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Gestion des Vendeurs</title>
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

$message = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nom   = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];
    $role  = $_POST['role'];
    if ($nom && $email && $pass) {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO vendeur (nom, email, password, role) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $nom, $email, $hash, $role);
        if ($stmt->execute()) {
            $message = "Vendeur ajouté avec succès."; $msgType = 'success';
        } else {
            $message = "Erreur: " . $conn->error; $msgType = 'danger';
        }
    } else {
        $message = "Tous les champs sont obligatoires."; $msgType = 'warning';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id    = intval($_POST['id']);
    $nom   = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];
    if ($nom && $email) {
        if (!empty($pass)) {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE vendeur SET nom=?, email=?, password=? WHERE id=?");
            $stmt->bind_param("sssi", $nom, $email, $hash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE vendeur SET nom=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $nom, $email, $id);
        }
        if ($stmt->execute()) {
            $message = "Vendeur modifié."; $msgType = 'success';
        } else {
            $message = "Erreur: " . $conn->error; $msgType = 'danger';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['id']);
    if ($id === $_SESSION['user_id']) {
        $message = "Vous ne pouvez pas supprimer votre propre compte."; $msgType = 'warning';
    } else {
        $stmt = $conn->prepare("DELETE FROM vendeur WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Vendeur supprimé."; $msgType = 'success';
        } else {
            $message = "Erreur: " . $conn->error; $msgType = 'danger';
        }
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$offset  = ($page - 1) * $perPage;

if ($search) {
    $like = '%' . $search . '%';
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM vendeur WHERE nom LIKE ? OR email LIKE ?");
    $countStmt->bind_param("ss", $like, $like);
    $countStmt->execute();
    $totalRows = $countStmt->get_result()->fetch_row()[0];
    $stmt = $conn->prepare("SELECT * FROM vendeur WHERE nom LIKE ? OR email LIKE ? ORDER BY date_creation DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ssii", $like, $like, $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $totalRows = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM vendeur"))[0];
    $stmt = $conn->prepare("SELECT * FROM vendeur ORDER BY date_creation DESC LIMIT ? OFFSET ?");
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
            <a href="gestion_vendeur.php" class="active"><i class="fas fa-user-tie"></i> Gérer les Vendeurs</a>
            <a href="gestion_medicament.php"><i class="fas fa-pills"></i> Gérer les Médicaments</a>
            <a href="gestion_caisse.php"><i class="fas fa-cash-register"></i> Gérer les Caisses</a>
            <a href="liste_vente.php"><i class="fas fa-receipt"></i> Toutes les Ventes</a>
            <a href="admin_patients.php"><i class="fas fa-users"></i> Voir les Patients</a>
            <a href="rapport_ventes.php"><i class="fas fa-chart-bar"></i> Rapport des Ventes</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="page-header">
            <h1><i class="fas fa-user-tie"></i> Gestion des Vendeurs</h1>
        </div>

        <?php if ($message): echo alert($msgType, $message); endif; ?>

        <!-- Add form -->
        <div class="section-card">
            <div class="section-card-header"><h2><i class="fas fa-user-plus"></i> Ajouter un vendeur</h2></div>
            <form method="POST" class="inline-form">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group"><label>Nom</label><input type="text" name="nom" class="form-input" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-input" required></div>
                    <div class="form-group"><label>Mot de passe</label><input type="password" name="password" class="form-input" required></div>
                    <div class="form-group">
                        <label>Rôle</label>
                        <select name="role" class="form-input">
                            <option value="vendeur">Vendeur</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group form-group-btn">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Search + Table -->
        <div class="section-card">
            <div class="section-card-header">
                <h2><i class="fas fa-list"></i> Liste des vendeurs (<?php echo $totalRows; ?>)</h2>
                <form method="GET" class="search-form">
                    <input type="text" name="search" class="form-input" placeholder="Rechercher..." value="<?php echo h($search); ?>">
                    <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-search"></i></button>
                    <?php if ($search): ?><a href="gestion_vendeur.php" class="btn btn-sm btn-outline">Réinitialiser</a><?php endif; ?>
                </form>
            </div>
            <div class="table-responsive">
                <table class="data-table" id="vendeurTable">
                    <thead>
                        <tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Inscrit le</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php $hasRows = false; while ($v = $result->fetch_assoc()): $hasRows = true; ?>
                        <tr>
                            <td><?php echo $v['id']; ?></td>
                            <td><?php echo h($v['nom']); ?></td>
                            <td><?php echo h($v['email']); ?></td>
                            <td><span class="badge <?php echo $v['role']==='admin'?'badge-primary':'badge-info'; ?>"><?php echo h($v['role']); ?></span></td>
                            <td><?php echo formatDate($v['date_creation']); ?></td>
                            <td class="actions">
                                <button class="btn btn-sm btn-outline" onclick="openEditModal(<?php echo $v['id']; ?>,'<?php echo h(addslashes($v['nom'])); ?>','<?php echo h(addslashes($v['email'])); ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($v['id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-sm btn-danger" onclick="deleteConfirmation(<?php echo $v['id']; ?>, 'delete-form-vendeur')">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if (!$hasRows): ?>
                        <tr><td colspan="6" class="text-center text-muted">Aucun vendeur trouvé.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
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

<!-- Delete form -->
<form method="POST" id="delete-form-vendeur" style="display:none">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete-id-vendeur">
</form>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header"><h3><i class="fas fa-edit"></i> Modifier le vendeur</h3><button onclick="closeModal('editModal')" class="modal-close">&times;</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            <div class="form-group"><label>Nom</label><input type="text" name="nom" id="edit-nom" class="form-input" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" id="edit-email" class="form-input" required></div>
            <div class="form-group"><label>Nouveau mot de passe (laisser vide pour garder)</label><input type="password" name="password" class="form-input"></div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('editModal')" class="btn btn-outline">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal modal-sm">
        <div class="modal-header"><h3><i class="fas fa-exclamation-triangle"></i> Confirmation</h3></div>
        <div class="modal-body"><p>Êtes-vous sûr de vouloir supprimer ce vendeur ?</p></div>
        <div class="modal-footer">
            <button onclick="closeModal('confirmModal')" class="btn btn-outline">Annuler</button>
            <button onclick="confirmDelete()" class="btn btn-danger">Supprimer</button>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
<script>
function openEditModal(id, nom, email) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-nom').value = nom;
    document.getElementById('edit-email').value = email;
    document.getElementById('editModal').classList.add('active');
}
</script>
</body>
</html>
