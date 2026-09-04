<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Gestion des caisses enregistreuses">
    <meta name="keywords" content="pharmacie, caisse, gestion, admin, PharmaSanté, Algérie">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Gestion des Caisses</title>
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
        $nom = trim($_POST['nom']);
        if ($nom) {
            $stmt = $conn->prepare("INSERT INTO caisse (nom, statut) VALUES (?, 'fermee')");
            $stmt->bind_param("s", $nom);
            if ($stmt->execute()) { $message = "Caisse ajoutée."; $msgType = 'success'; }
            else { $message = "Erreur: " . $conn->error; $msgType = 'danger'; }
        }
    }
    if ($action === 'edit') {
        $id  = intval($_POST['id']);
        $nom = trim($_POST['nom']);
        $stmt = $conn->prepare("UPDATE caisse SET nom=? WHERE id=?");
        $stmt->bind_param("si", $nom, $id);
        if ($stmt->execute()) { $message = "Caisse modifiée."; $msgType = 'success'; }
        else { $message = "Erreur: " . $conn->error; $msgType = 'danger'; }
    }
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $check = $conn->prepare("SELECT statut FROM caisse WHERE id=?");
        $check->bind_param("i", $id);
        $check->execute();
        $caisse = $check->get_result()->fetch_assoc();
        if ($caisse && $caisse['statut'] === 'fermee') {
            $stmt = $conn->prepare("DELETE FROM caisse WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) { $message = "Caisse supprimée."; $msgType = 'success'; }
            else { $message = "Erreur: " . $conn->error; $msgType = 'danger'; }
        } else {
            $message = "Impossible de supprimer une caisse ouverte."; $msgType = 'warning';
        }
    }
}

$search = trim($_GET['search'] ?? '');
if ($search) {
    $like = '%' . $search . '%';
    $stmt = $conn->prepare("SELECT c.*, v.nom AS vendeur_nom FROM caisse c LEFT JOIN vendeur v ON c.vendeur_id=v.id WHERE c.nom LIKE ? ORDER BY c.id DESC");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = mysqli_query($conn, "SELECT c.*, v.nom AS vendeur_nom FROM caisse c LEFT JOIN vendeur v ON c.vendeur_id=v.id ORDER BY c.id DESC");
}
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fas fa-cog"></i> Administration</div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="gestion_vendeur.php"><i class="fas fa-user-tie"></i> Gérer les Vendeurs</a>
            <a href="gestion_medicament.php"><i class="fas fa-pills"></i> Gérer les Médicaments</a>
            <a href="gestion_caisse.php" class="active"><i class="fas fa-cash-register"></i> Gérer les Caisses</a>
            <a href="liste_vente.php"><i class="fas fa-receipt"></i> Toutes les Ventes</a>
            <a href="admin_patients.php"><i class="fas fa-users"></i> Voir les Patients</a>
            <a href="rapport_ventes.php"><i class="fas fa-chart-bar"></i> Rapport des Ventes</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="page-header"><h1><i class="fas fa-cash-register"></i> Gestion des Caisses</h1></div>

        <?php if ($message): echo alert($msgType, $message); endif; ?>

        <div class="section-card">
            <div class="section-card-header"><h2><i class="fas fa-plus"></i> Ajouter une caisse</h2></div>
            <form method="POST" class="inline-form">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group"><label>Nom de la caisse</label><input type="text" name="nom" class="form-input" placeholder="Ex: Caisse Principale" required></div>
                    <div class="form-group form-group-btn"><button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter</button></div>
                </div>
            </form>
        </div>

        <div class="section-card">
            <div class="section-card-header">
                <h2><i class="fas fa-list"></i> Liste des caisses</h2>
                <form method="GET" class="search-form">
                    <input type="text" name="search" class="form-input" placeholder="Rechercher..." value="<?php echo h($search); ?>">
                    <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-search"></i></button>
                    <?php if ($search): ?><a href="gestion_caisse.php" class="btn btn-sm btn-outline">Réinitialiser</a><?php endif; ?>
                </form>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>ID</th><th>Nom</th><th>Statut</th><th>Ouverture</th><th>Fermeture</th><th>Vendeur</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php $hasRows = false; while ($c = mysqli_fetch_assoc($result)): $hasRows = true; ?>
                        <tr>
                            <td><?php echo $c['id']; ?></td>
                            <td><?php echo h($c['nom']); ?></td>
                            <td><?php echo $c['statut'] === 'ouverte' ? '<span class="badge badge-success">Ouverte</span>' : '<span class="badge badge-danger">Fermée</span>'; ?></td>
                            <td><?php echo formatDate($c['heure_ouverture']); ?></td>
                            <td><?php echo formatDate($c['heure_fermeture']); ?></td>
                            <td><?php echo $c['vendeur_nom'] ? h($c['vendeur_nom']) : '-'; ?></td>
                            <td class="actions">
                                <button class="btn btn-sm btn-outline" onclick="openCaisseEdit(<?php echo $c['id']; ?>,'<?php echo h(addslashes($c['nom'])); ?>')"><i class="fas fa-edit"></i></button>
                                <?php if ($c['statut'] === 'fermee'): ?>
                                <button class="btn btn-sm btn-danger" onclick="deleteConfirmation(<?php echo $c['id']; ?>, 'delete-form-caisse')"><i class="fas fa-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if (!$hasRows): ?><tr><td colspan="7" class="text-center text-muted">Aucune caisse trouvée.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<form method="POST" id="delete-form-caisse" style="display:none">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete-id-caisse">
</form>

<div class="modal-overlay" id="editCaisseModal">
    <div class="modal modal-sm">
        <div class="modal-header"><h3><i class="fas fa-edit"></i> Modifier la caisse</h3><button onclick="closeModal('editCaisseModal')" class="modal-close">&times;</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="caisse-edit-id">
            <div class="form-group"><label>Nom</label><input type="text" name="nom" id="caisse-edit-nom" class="form-input" required></div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('editCaisseModal')" class="btn btn-outline">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="confirmModal">
    <div class="modal modal-sm">
        <div class="modal-header"><h3><i class="fas fa-exclamation-triangle"></i> Confirmation</h3></div>
        <div class="modal-body"><p>Supprimer cette caisse ?</p></div>
        <div class="modal-footer">
            <button onclick="closeModal('confirmModal')" class="btn btn-outline">Annuler</button>
            <button onclick="confirmDelete()" class="btn btn-danger">Supprimer</button>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
<script>
function openCaisseEdit(id, nom) {
    document.getElementById('caisse-edit-id').value = id;
    document.getElementById('caisse-edit-nom').value = nom;
    document.getElementById('editCaisseModal').classList.add('active');
}
</script>
</body>
</html>
