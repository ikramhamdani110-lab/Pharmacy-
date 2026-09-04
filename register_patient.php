<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Inscription patient pour le système de gestion de pharmacie">
    <meta name="keywords" content="pharmacie, inscription, patient, register, PharmaSanté, Algérie, compte">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Inscription Patient</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom    = trim($_POST['prenom']);
    $nom       = trim($_POST['nom']);
    $telephone = trim($_POST['telephone']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];
    $dob       = trim($_POST['date_naissance']);
    $adresse   = trim($_POST['adresse']);

    if (empty($prenom) || empty($nom) || empty($telephone) || empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format d'email invalide.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM patient WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $stmt2 = $conn->prepare("SELECT id FROM vendeur WHERE email = ?");
            $stmt2->bind_param("s", $email);
            $stmt2->execute();
            if ($stmt2->get_result()->num_rows > 0) {
                $error = "Cet email est déjà utilisé.";
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $dobVal = !empty($dob) ? $dob : null;
                $stmt3 = $conn->prepare("INSERT INTO patient (nom, prenom, telephone, email, password, adresse, date_naissance) VALUES (?,?,?,?,?,?,?)");
                $stmt3->bind_param("sssssss", $nom, $prenom, $telephone, $email, $hash, $adresse, $dobVal);
                if ($stmt3->execute()) {
                    $newId = $conn->insert_id;
                    session_regenerate_id(true);
                    $_SESSION['user_id']   = $newId;
                    $_SESSION['user_name'] = $prenom . ' ' . $nom;
                    $_SESSION['user_role'] = 'patient';
                    header("Location: patient_dashboard.php");
                    exit;
                } else {
                    $error = "Erreur lors de l'inscription. Veuillez réessayer.";
                }
            }
        }
    }
}
?>

<div class="auth-container auth-wide">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="fas fa-user-plus"></i>
            <h1>Créer un compte</h1>
            <p>Inscrivez-vous en tant que patient</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo h($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="register_patient.php" onsubmit="return validateRegistrationForm(this)">
            <div class="form-row">
                <div class="form-group">
                    <label for="prenom"><i class="fas fa-user"></i> Prénom <span class="required">*</span></label>
                    <input type="text" id="prenom" name="prenom" class="form-input" required value="<?php echo isset($_POST['prenom']) ? h($_POST['prenom']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="nom"><i class="fas fa-user"></i> Nom <span class="required">*</span></label>
                    <input type="text" id="nom" name="nom" class="form-input" required value="<?php echo isset($_POST['nom']) ? h($_POST['nom']) : ''; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="telephone"><i class="fas fa-phone"></i> Téléphone <span class="required">*</span></label>
                    <input type="text" id="telephone" name="telephone" class="form-input" placeholder="0551234567" required value="<?php echo isset($_POST['telephone']) ? h($_POST['telephone']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" class="form-input" required value="<?php echo isset($_POST['email']) ? h($_POST['email']) : ''; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Mot de passe <span class="required">*</span></label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Min. 6 caractères" required minlength="6" onkeyup="checkPasswordStrength(this.value)">
                    <div class="password-strength" id="passwordStrength"></div>
                </div>
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirmer <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required onkeyup="checkPasswordMatch('password','confirm_password','matchMsg')">
                    <span id="matchMsg" class="match-msg"></span>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="date_naissance"><i class="fas fa-calendar"></i> Date de naissance</label>
                    <input type="date" id="date_naissance" name="date_naissance" class="form-input" value="<?php echo isset($_POST['date_naissance']) ? h($_POST['date_naissance']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="adresse"><i class="fas fa-map-marker-alt"></i> Adresse</label>
                    <input type="text" id="adresse" name="adresse" class="form-input" value="<?php echo isset($_POST['adresse']) ? h($_POST['adresse']) : ''; ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-user-plus"></i> Créer mon compte
            </button>
        </form>

        <div class="auth-links">
            <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
            <p><a href="index.php"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a></p>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
