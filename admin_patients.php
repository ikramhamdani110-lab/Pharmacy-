<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Liste de tous les patients inscrits">
    <meta name="keywords" content="pharmacie, patients, admin, gestion, PharmaSanté, Algérie">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Tous les Patients</title>
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM patient WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) { $message = "Patient supprimé."; $msgType = 'success'; }
    else { $message = "Erreur lors de la suppression."; $msgType = 'danger'; }
}

$search = trim($_GET['search'] ?? '');
if ($search) {
    $like = '%' . $search . '%';
    $stmt = $conn->prepare("SELECT * FROM patient WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR telephone LIKE ? ORDER BY date_creation DESC");
    $stmt->bind_param("ssss", $like, $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = mysqli_query($conn, "SELECT * FROM patient ORDER BY date_creation DESC");
}
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fas fa-cog"></i> Administration</div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="gestion_vendeur.php"><i class="fas fa-user-tie"></i> Gérer les Vendeurs</a>
            <a href="gestion_medicament.php"><i class="fas fa-pills"></i> Gérer les Médicaments</a>
            <a href="gestion_caisse.php"><i class="fas fa-cash-register"></i> Gérer les Caisses</a>
            <a href="liste_vente.php"><i class="fas fa-receipt"></i> Toutes les Ventes</a>
            <a href="admin_patients.php" class="active"><i class="fas fa-users"></i> Voir les Patients</a>
            <a href="rapport_ventes.php"><i class="fas fa-chart-bar"></i> Rapport des Ventes</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="page-header"><h1><i class="fas fa-users"></i> Tous les Patients</h1></div>

        <?php if ($message): echo alert($msgType, $message); endif; ?>

        <div class="section-card">
            <div class="section-card-header">
                <h2><i class="fas fa-list"></i> Liste des patients</h2>
                <form method="GET" class="search-form">
                    <input type="text" name="search" class="form-input" placeholder="Nom, email, téléphone..." value="<?php echo h($search); ?>">
                    <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-search"></i></button>
                    <?php if ($search): ?><a href="admin_patients.php" class="btn btn-sm btn-outline">Réinitialiser</a><?php endif; ?>
                </form>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>ID</th><th>Nom complet</th><th>Email</th><th>Téléphone</th><th>Adresse</th><th>Naissance</th><th>Inscrit le</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php $hasRows = false; while ($p = $result->fetch_assoc()): $hasRows = true; ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><strong><?php echo h($p['prenom'] . ' ' . $p['nom']); ?></strong></td>
                            <td><?php echo h($p['email']); ?></td>
                            <td><?php echo h($p['telephone']); ?></td>
                            <td><?php echo h(truncate($p['adresse'], 40)); ?></td>
                            <td><?php echo formatDateOnly($p['date_naissance']); ?></td>
                            <td><?php echo formatDate($p['date_creation']); ?></td>
                            <td class="actions">
                                <a href="liste_vente.php?patient_id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline" title="Voir historique"><i class="fas fa-history"></i></a>
                                <button class="btn btn-sm btn-danger" onclick="deleteConfirmation(<?php echo $p['id']; ?>, 'delete-form-patient')"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if (!$hasRows): ?><tr><td colspan="8" class="text-center text-muted">Aucun patient trouvé.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<form method="POST" id="delete-form-patient" style="display:none">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete-id-patient">
</form>

<div class="modal-overlay" id="confirmModal">
    <div class="modal modal-sm">
        <div class="modal-header"><h3><i class="fas fa-exclamation-triangle"></i> Confirmation</h3></div>
        <div class="modal-body"><p>Supprimer ce patient et toutes ses données ?</p></div>
        <div class="modal-footer">
            <button onclick="closeModal('confirmModal')" class="btn btn-outline">Annuler</button>
            <button onclick="confirmDelete()" class="btn btn-danger">Supprimer</button>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
</body>
</html>
