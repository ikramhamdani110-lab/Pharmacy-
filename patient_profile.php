<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Profil et informations personnelles du patient">
    <meta name="keywords" content="pharmacie, patient, profil, informations, PharmaSanté, Algérie">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Mon Profil</title>
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
$message = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update') {
        $prenom  = trim($_POST['prenom']);
        $nom     = trim($_POST['nom']);
        $tel     = trim($_POST['telephone']);
        $email   = trim($_POST['email']);
        $adresse = trim($_POST['adresse']);
        $dob     = trim($_POST['date_naissance']);
        $dobVal  = $dob ?: null;
        $stmt = $conn->prepare("UPDATE patient SET prenom=?, nom=?, telephone=?, email=?, adresse=?, date_naissance=? WHERE id=?");
        $stmt->bind_param("ssssssi", $prenom, $nom, $tel, $email, $adresse, $dobVal, $userId);
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $prenom . ' ' . $nom;
            $message = "Profil mis à jour."; $msgType = 'success';
        } else {
            $message = "Erreur: " . $conn->error; $msgType = 'danger';
        }
    }
    if ($action === 'password') {
        $current = $_POST['current_password'];
        $new     = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        $stCheck = $conn->prepare("SELECT password FROM patient WHERE id=?");
        $stCheck->bind_param("i", $userId);
        $stCheck->execute();
        $hash = $stCheck->get_result()->fetch_row()[0];

        if (!password_verify($current, $hash)) {
            $message = "Mot de passe actuel incorrect."; $msgType = 'danger';
        } elseif (strlen($new) < 8) {
            $message = "Le nouveau mot de passe doit contenir au moins 8 caractères."; $msgType = 'warning';
        } elseif ($new !== $confirm) {
            $message = "Les mots de passe ne correspondent pas."; $msgType = 'warning';
        } else {
            $newHash = password_hash($new, PASSWORD_BCRYPT);
            $stUp = $conn->prepare("UPDATE patient SET password=? WHERE id=?");
            $stUp->bind_param("si", $newHash, $userId);
            if ($stUp->execute()) { $message = "Mot de passe changé avec succès."; $msgType = 'success'; }
            else { $message = "Erreur."; $msgType = 'danger'; }
        }
    }
}

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
            <a href="patient_profile.php" class="active"><i class="fas fa-user"></i> Mon Profil</a>
            <a href="patient_historique.php"><i class="fas fa-history"></i> Mes Médicaments</a>
            <a href="index.php"><i class="fas fa-pills"></i> Voir les Médicaments</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="page-header"><h1><i class="fas fa-user-edit"></i> Mon Profil</h1></div>
        <?php if ($message): echo alert($msgType, $message); endif; ?>

        <div class="section-card">
            <div class="section-card-header"><h2><i class="fas fa-id-card"></i> Informations personnelles</h2></div>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <div class="form-row">
                    <div class="form-group"><label>Prénom</label><input type="text" name="prenom" class="form-input" value="<?php echo h($patient['prenom']); ?>" required></div>
                    <div class="form-group"><label>Nom</label><input type="text" name="nom" class="form-input" value="<?php echo h($patient['nom']); ?>" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Téléphone</label><input type="text" name="telephone" class="form-input" value="<?php echo h($patient['telephone']); ?>" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-input" value="<?php echo h($patient['email']); ?>" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Date de naissance</label><input type="date" name="date_naissance" class="form-input" value="<?php echo h($patient['date_naissance'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Adresse</label><input type="text" name="adresse" class="form-input" value="<?php echo h($patient['adresse'] ?? ''); ?>"></div>
                </div>
                <div class="form-group">
                    <label class="text-muted"><i class="fas fa-calendar"></i> Membre depuis: <?php echo formatDate($patient['date_creation']); ?></label>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer les modifications</button>
            </form>
        </div>

        <div class="section-card">
            <div class="section-card-header"><h2><i class="fas fa-key"></i> Changer le mot de passe</h2></div>
            <form method="POST">
                <input type="hidden" name="action" value="password">
                <div class="form-group"><label>Mot de passe actuel</label><input type="password" name="current_password" class="form-input" required></div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nouveau mot de passe (min. 8 caractères)</label>
                        <input type="password" name="new_password" id="new_pass" class="form-input" required onkeyup="checkPasswordStrength(this.value)">
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>
                    <div class="form-group">
                        <label>Confirmer le nouveau mot de passe</label>
                        <input type="password" name="confirm_password" class="form-input" required onkeyup="checkPasswordMatch('new_pass','confirm_password','pwMatchMsg')">
                        <span id="pwMatchMsg" class="match-msg"></span>
                    </div>
                </div>
                <button type="submit" class="btn btn-outline"><i class="fas fa-key"></i> Changer le mot de passe</button>
            </form>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
</body>
</html>
