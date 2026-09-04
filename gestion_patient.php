<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Gestion des patients par le vendeur">
    <meta name="keywords" content="pharmacie, patients, vendeur, gestion, PharmaSanté, Algérie">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Gestion des Patients</title>
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

$message = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $nom   = trim($_POST['nom']);
        $prenom= trim($_POST['prenom']);
        $tel   = trim($_POST['telephone']);
        $email = trim($_POST['email']);
        $dob   = trim($_POST['date_naissance']);
        $adresse= trim($_POST['adresse']);
        $pass  = password_hash('patient123', PASSWORD_BCRYPT);
        if ($nom && $prenom && $tel && $email) {
            $dobVal = $dob ?: null;
            $stmt = $conn->prepare("INSERT INTO patient (nom, prenom, telephone, email, password, adresse, date_naissance) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssss", $nom, $prenom, $tel, $email, $pass, $adresse, $dobVal);
            if ($stmt->execute()) { $message = "Patient ajouté. Mot de passe par défaut: patient123"; $msgType = 'success'; }
            else { $message = "Erreur (email existant?): " . $conn->error; $msgType = 'danger'; }
        } else { $message = "Champs obligatoires manquants."; $msgType = 'warning'; }
    }
    if ($action === 'edit') {
        $id   = intval($_POST['id']);
        $nom  = trim($_POST['nom']); $prenom = trim($_POST['prenom']);
        $tel  = trim($_POST['telephone']); $email = trim($_POST['email']);
        $dob  = trim($_POST['date_naissance']); $adresse = trim($_POST['adresse']);
        $dobVal = $dob ?: null;
        $stmt = $conn->prepare("UPDATE patient SET nom=?, prenom=?, telephone=?, email=?, adresse=?, date_naissance=? WHERE id=?");
        $stmt->bind_param("ssssssi", $nom, $prenom, $tel, $email, $adresse, $dobVal, $id);
        if ($stmt->execute()) { $message = "Patient modifié."; $msgType = 'success'; }
        else { $message = "Erreur: " . $conn->error; $msgType = 'danger'; }
    }
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM patient WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) { $message = "Patient supprimé."; $msgType = 'success'; }
        else { $message = "Impossible (ventes liées)."; $msgType = 'danger'; }
    }
}

$search = trim($_GET['search'] ?? '');
if ($search) {
    $like = '%' . $search . '%';
    $stmt = $conn->prepare("SELECT * FROM patient WHERE nom LIKE ? OR prenom LIKE ? OR telephone LIKE ? ORDER BY date_creation DESC");
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = mysqli_query($conn, "SELECT * FROM patient ORDER BY date_creation DESC");
}
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fas fa-store"></i> Vendeur</div>
        <nav class="sidebar-nav">
            <a href="vendeur_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="caisse_operation.php"><i class="fas fa-cash-register"></i> Gestion Caisse</a>
            <a href="gestion_patient.php" class="active"><i class="fas fa-users"></i> Gérer les Patients</a>
            <a href="ajouter_vente.php"><i class="fas fa-shopping-cart"></i> Nouvelle Vente</a>
            <a href="mes_ventes.php"><i class="fas fa-receipt"></i> Mes Ventes</a>
            <a href="recherche_medicament.php"><i class="fas fa-search"></i> Rechercher Médicament</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="page-header"><h1><i class="fas fa-users"></i> Gestion des Patients</h1></div>
        <?php if ($message): echo alert($msgType, $message); endif; ?>

        <div class="section-card">
            <div class="section-card-header"><h2><i class="fas fa-user-plus"></i> Ajouter un patient</h2></div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group"><label>Prénom *</label><input type="text" name="prenom" class="form-input" required></div>
                    <div class="form-group"><label>Nom *</label><input type="text" name="nom" class="form-input" required></div>
                    <div class="form-group"><label>Téléphone *</label><input type="text" name="telephone" class="form-input" required></div>
                    <div class="form-group"><label>Email *</label><input type="email" name="email" class="form-input" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Date de naissance</label><input type="date" name="date_naissance" class="form-input"></div>
                    <div class="form-group"><label>Adresse</label><input type="text" name="adresse" class="form-input"></div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter</button>
            </form>
        </div>

        <div class="section-card">
            <div class="section-card-header">
                <h2><i class="fas fa-list"></i> Liste des patients</h2>
                <div class="header-actions">
                    <form method="GET" class="search-form">
                        <input type="text" name="search" class="form-input" placeholder="Nom, prénom, téléphone..." value="<?php echo h($search); ?>">
                        <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-search"></i></button>
                        <?php if ($search): ?><a href="gestion_patient.php" class="btn btn-sm btn-outline">Réinitialiser</a><?php endif; ?>
                    </form>
                    <button onclick="printList()" class="btn btn-outline btn-sm no-print"><i class="fas fa-print"></i> Imprimer</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>ID</th><th>Nom complet</th><th>Téléphone</th><th>Email</th><th>Inscrit le</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php
                    $patients = array();
                    $res = ($result instanceof mysqli_result) ? $result : $result->get_result();
                    while ($p = $res->fetch_assoc()): $patients[] = $p; ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><strong><?php echo h($p['prenom'] . ' ' . $p['nom']); ?></strong></td>
                            <td><?php echo h($p['telephone']); ?></td>
                            <td><?php echo h($p['email']); ?></td>
                            <td><?php echo formatDate($p['date_creation']); ?></td>
                            <td class="actions">
                                <a href="ajouter_vente.php?patient_id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary" title="Vente"><i class="fas fa-shopping-cart"></i></a>
                                <button class="btn btn-sm btn-outline" onclick="openPatientEdit(<?php echo $p['id']; ?>,'<?php echo h(addslashes($p['nom'])); ?>','<?php echo h(addslashes($p['prenom'])); ?>','<?php echo h(addslashes($p['telephone'])); ?>','<?php echo h(addslashes($p['email'])); ?>','<?php echo h(addslashes($p['adresse'])); ?>','<?php echo h($p['date_naissance']); ?>')"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger" onclick="deleteConfirmation(<?php echo $p['id']; ?>, 'delete-form-pat')"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if (empty($patients)): ?><tr><td colspan="6" class="text-center text-muted">Aucun patient trouvé.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<form method="POST" id="delete-form-pat" style="display:none">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete-id-pat">
</form>

<div class="modal-overlay" id="editPatModal">
    <div class="modal">
        <div class="modal-header"><h3><i class="fas fa-edit"></i> Modifier le patient</h3><button onclick="closeModal('editPatModal')" class="modal-close">&times;</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="pat-edit-id">
            <div class="form-row">
                <div class="form-group"><label>Prénom</label><input type="text" name="prenom" id="pat-edit-prenom" class="form-input" required></div>
                <div class="form-group"><label>Nom</label><input type="text" name="nom" id="pat-edit-nom" class="form-input" required></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Téléphone</label><input type="text" name="telephone" id="pat-edit-tel" class="form-input" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" id="pat-edit-email" class="form-input" required></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Date de naissance</label><input type="date" name="date_naissance" id="pat-edit-dob" class="form-input"></div>
                <div class="form-group"><label>Adresse</label><input type="text" name="adresse" id="pat-edit-adresse" class="form-input"></div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('editPatModal')" class="btn btn-outline">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="confirmModal">
    <div class="modal modal-sm">
        <div class="modal-header"><h3><i class="fas fa-exclamation-triangle"></i> Confirmation</h3></div>
        <div class="modal-body"><p>Supprimer ce patient ?</p></div>
        <div class="modal-footer">
            <button onclick="closeModal('confirmModal')" class="btn btn-outline">Annuler</button>
            <button onclick="confirmDelete()" class="btn btn-danger">Supprimer</button>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
<script>
function openPatientEdit(id, nom, prenom, tel, email, adresse, dob) {
    document.getElementById('pat-edit-id').value = id;
    document.getElementById('pat-edit-nom').value = nom;
    document.getElementById('pat-edit-prenom').value = prenom;
    document.getElementById('pat-edit-tel').value = tel;
    document.getElementById('pat-edit-email').value = email;
    document.getElementById('pat-edit-adresse').value = adresse;
    document.getElementById('pat-edit-dob').value = dob;
    document.getElementById('editPatModal').classList.add('active');
}
</script>
</body>
</html>
