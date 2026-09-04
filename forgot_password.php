<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isLoggedIn()) { header("Location: index.php"); exit; }

$error   = '';
$success = '';
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez entrer une adresse email valide.";
    } else {
        $tableSource = null;
        $userName    = '';

        $st = $conn->prepare("SELECT id, nom FROM vendeur WHERE email = ?");
        $st->bind_param("s", $email);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        if ($row) { $tableSource = 'vendeur'; $userName = $row['nom']; }

        if (!$tableSource) {
            $st2 = $conn->prepare("SELECT id, CONCAT(prenom,' ',nom) AS nom FROM patient WHERE email = ?");
            $st2->bind_param("s", $email);
            $st2->execute();
            $row2 = $st2->get_result()->fetch_assoc();
            if ($row2) { $tableSource = 'patient'; $userName = $row2['nom']; }
        }

        if (!$tableSource) {
            $success = "Si cet email est enregistré, un lien de réinitialisation apparaîtra ci-dessous.";
        } else {
            $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $del->bind_param("s", $email);
            $del->execute();

            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $ins = $conn->prepare("INSERT INTO password_resets (email, token, table_source, expires_at) VALUES (?,?,?,?)");
            $ins->bind_param("ssss", $email, $token, $tableSource, $expires);
            $ins->execute();

            $resetLink = 'reset_password.php?token=' . $token;
            $success   = "Bonjour <strong>" . h($userName) . "</strong> ! Votre lien de réinitialisation est prêt.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaSanté - Mot de passe oublié</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="fas fa-key"></i>
            <h1>Mot de passe oublié</h1>
            <p>Entrez votre email pour réinitialiser votre mot de passe</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo h($error); ?></div>
        <?php endif; ?>

        <?php if ($success && !$resetLink): ?>
        <div class="alert alert-info"><i class="fas fa-info-circle"></i> <?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($resetLink): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?><br>
            <div style="margin-top:12px;padding:12px;background:#fff;border-radius:6px;border:1px dashed var(--primary);">
                <small style="color:var(--text-mid);display:block;margin-bottom:6px;">
                    <i class="fas fa-link"></i> Votre lien de réinitialisation :
                </small>
                <a href="<?php echo h($resetLink); ?>" class="btn btn-primary" style="width:100%;justify-content:center;">
                    <i class="fas fa-unlock-alt"></i> Réinitialiser mon mot de passe
                </a>
                <small style="color:var(--text-light);display:block;margin-top:8px;text-align:center;">
                    <i class="fas fa-clock"></i> Ce lien expire dans 1 heure.
                </small>
            </div>
        </div>
        <?php else: ?>
        <form method="POST" action="forgot_password.php">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Adresse Email</label>
                <input type="email" id="email" name="email" class="form-input"
                       placeholder="votre@email.dz" required
                       value="<?php echo isset($_POST['email']) ? h($_POST['email']) : ''; ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-paper-plane"></i> Obtenir un lien de réinitialisation
            </button>
        </form>
        <?php endif; ?>

        <div class="auth-links">
            <p><a href="login.php"><i class="fas fa-arrow-left"></i> Retour à la connexion</a></p>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
