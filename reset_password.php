<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isLoggedIn()) { header("Location: index.php"); exit; }

$token   = trim($_GET['token'] ?? '');
$error   = '';
$success = '';
$tokenData = null;

if (empty($token)) {
    $error = "Lien invalide ou manquant.";
} else {
    $st = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $st->bind_param("s", $token);
    $st->execute();
    $tokenData = $st->get_result()->fetch_assoc();
    if (!$tokenData) {
        $error = "Ce lien est invalide ou a expiré. Veuillez en demander un nouveau.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenData) {
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (empty($password) || empty($confirm)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $hash  = password_hash($password, PASSWORD_BCRYPT);
        $email = $tokenData['email'];
        $table = $tokenData['table_source'];

        if ($table === 'vendeur') {
            $upd = $conn->prepare("UPDATE vendeur SET password = ? WHERE email = ?");
        } else {
            $upd = $conn->prepare("UPDATE patient SET password = ? WHERE email = ?");
        }
        $upd->bind_param("ss", $hash, $email);

        if ($upd->execute() && $upd->affected_rows > 0) {
            $del = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
            $del->bind_param("s", $token);
            $del->execute();
            $success   = "Mot de passe réinitialisé avec succès ! Vous pouvez maintenant vous connecter.";
            $tokenData = null;
        } else {
            $error = "Erreur lors de la mise à jour. Veuillez réessayer.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaSanté - Réinitialisation du mot de passe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="fas fa-unlock-alt"></i>
            <h1>Nouveau mot de passe</h1>
            <p>Choisissez un nouveau mot de passe sécurisé</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo h($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo h($success); ?></div>
        <div style="text-align:center;margin-top:16px;">
            <a href="login.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Se connecter maintenant
            </a>
        </div>
        <?php elseif ($tokenData): ?>
        <div class="alert alert-info" style="margin-bottom:16px;">
            <i class="fas fa-envelope"></i> Réinitialisation pour : <strong><?php echo h($tokenData['email']); ?></strong>
        </div>
        <form method="POST" action="reset_password.php?token=<?php echo h($token); ?>"
              onsubmit="return validateResetForm(this)">
            <div class="form-group" style="margin-bottom:16px;">
                <label for="password"><i class="fas fa-lock"></i> Nouveau mot de passe <span class="required">*</span></label>
                <div class="input-icon-right">
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="Min. 6 caractères" required minlength="6"
                           onkeyup="checkPasswordStrength(this.value)">
                    <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('password')"></i>
                </div>
                <div class="password-strength" id="passwordStrength"></div>
            </div>
            <div class="form-group" style="margin-bottom:20px;">
                <label for="confirm_password"><i class="fas fa-lock"></i> Confirmer le mot de passe <span class="required">*</span></label>
                <div class="input-icon-right">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                           placeholder="Répétez le mot de passe" required
                           onkeyup="checkPasswordMatch('password','confirm_password','matchMsg')">
                    <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('confirm_password')"></i>
                </div>
                <span id="matchMsg" class="match-msg"></span>
            </div>
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-save"></i> Enregistrer le nouveau mot de passe
            </button>
        </form>
        <?php elseif (!$success): ?>
        <div style="text-align:center;margin-top:8px;">
            <a href="forgot_password.php" class="btn btn-primary">
                <i class="fas fa-redo"></i> Demander un nouveau lien
            </a>
        </div>
        <?php endif; ?>

        <div class="auth-links">
            <p><a href="login.php"><i class="fas fa-arrow-left"></i> Retour à la connexion</a></p>
        </div>
    </div>
</div>

<script src="script.js"></script>
<script>
function validateResetForm(form) {
    var p1 = document.getElementById('password');
    var p2 = document.getElementById('confirm_password');
    if (p1.value.length < 6) {
        showToast('Le mot de passe doit contenir au moins 6 caractères.', 'warning');
        return false;
    }
    if (p1.value !== p2.value) {
        showToast('Les mots de passe ne correspondent pas.', 'warning');
        return false;
    }
    return true;
}
</script>
</body>
</html>
