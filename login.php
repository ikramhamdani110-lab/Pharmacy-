<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Connexion au système de gestion de pharmacie">
    <meta name="keywords" content="pharmacie, connexion, login, PharmaSanté, Algérie, vendeur, patient">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Connexion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    $role = $_SESSION['user_role'];
    if ($role === 'admin') header("Location: admin_dashboard.php");
    elseif ($role === 'vendeur') header("Location: vendeur_dashboard.php");
    else header("Location: patient_dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $conn->prepare("SELECT id, nom, email, password, role FROM vendeur WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $vendeur = $result->fetch_assoc();

        if ($vendeur && password_verify($password, $vendeur['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $vendeur['id'];
            $_SESSION['user_name'] = $vendeur['nom'];
            $_SESSION['user_role'] = $vendeur['role'];
            if ($vendeur['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: vendeur_dashboard.php");
            }
            exit;
        }

        $stmt2 = $conn->prepare("SELECT id, nom, prenom, email, password FROM patient WHERE email = ?");
        $stmt2->bind_param("s", $email);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $patient = $result2->fetch_assoc();

        if ($patient && password_verify($password, $patient['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $patient['id'];
            $_SESSION['user_name'] = $patient['prenom'] . ' ' . $patient['nom'];
            $_SESSION['user_role'] = 'patient';
            header("Location: patient_dashboard.php");
            exit;
        }

        $error = "Email ou mot de passe incorrect.";
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="fas fa-pills"></i>
            <h1>PharmaSanté</h1>
            <p>Connectez-vous à votre espace</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo h($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" onsubmit="return validateForm(this)">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Adresse Email</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="votre@email.dz" required value="<?php echo isset($_POST['email']) ? h($_POST['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
                <div class="input-icon-right">
                    <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('password')"></i>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>

        <div class="auth-links">
            <p><a href="forgot_password.php"><i class="fas fa-key"></i> Mot de passe oublié ?</a></p>
            <p>Pas encore de compte ? <a href="register_patient.php">Créer un compte patient</a></p>
            <p><a href="index.php"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a></p>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
