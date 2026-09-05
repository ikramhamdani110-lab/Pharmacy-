<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
requireAdmin();

$profile = getSiteProfile($conn);
$message = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $errors = array();

    if ($fullName === '' || mb_strlen($fullName) > 150) {
        $errors[] = 'Le nom complet est obligatoire et doit contenir 150 caractères maximum.';
    }
    if ($phone === '' || !preg_match('/^[0-9+() .-]{7,30}$/', $phone)) {
        $errors[] = 'Le numéro de téléphone est invalide.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
        $errors[] = 'L’adresse email est invalide.';
    }
    if ($location === '' || mb_strlen($location) > 200) {
        $errors[] = 'La ville ou localisation est obligatoire et doit contenir 200 caractères maximum.';
    }
    if ($bio === '' || mb_strlen($bio) > 500) {
        $errors[] = 'La description est obligatoire et doit contenir 500 caractères maximum.';
    }

    $photoPath = $profile['photo_path'];
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK || $_FILES['profile_photo']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'La photo doit faire 2 Mo maximum.';
        } else {
            $imageInfo = getimagesize($_FILES['profile_photo']['tmp_name']);
            $mime = $imageInfo ? $imageInfo['mime'] : '';
            $allowedMimes = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
            if (!isset($allowedMimes[$mime])) {
                $errors[] = 'La photo doit être au format JPG, PNG ou WebP.';
            } else {
                $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profile';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                    $errors[] = 'Le dossier de stockage de la photo est indisponible.';
                } else {
                    $fileName = 'profile-' . bin2hex(random_bytes(16)) . '.' . $allowedMimes[$mime];
                    if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $fileName)) {
                        $errors[] = 'La photo n’a pas pu être enregistrée.';
                    } else {
                        $photoPath = 'uploads/profile/' . $fileName;
                    }
                }
            }
        }
    }

    if (!$errors) {
        $stmt = $conn->prepare("UPDATE site_profile SET full_name=?, phone=?, email=?, location=?, bio=?, photo_path=? WHERE id=1");
        if ($stmt) {
            $stmt->bind_param("ssssss", $fullName, $phone, $email, $location, $bio, $photoPath);
            if ($stmt->execute()) {
                $profile = array(
                    'full_name' => $fullName,
                    'phone' => $phone,
                    'email' => $email,
                    'location' => $location,
                    'bio' => $bio,
                    'photo_path' => $photoPath
                );
                $message = 'Les informations publiques ont été enregistrées.';
                $msgType = 'success';
            } else {
                $message = 'Impossible d’enregistrer les informations pour le moment.';
                $msgType = 'danger';
            }
        } else {
            $message = 'Impossible de préparer l’enregistrement des informations.';
            $msgType = 'danger';
        }
    } else {
        $message = implode(' ', $errors);
        $msgType = 'warning';
        $profile = array_merge($profile, array(
            'full_name' => $fullName,
            'phone' => $phone,
            'email' => $email,
            'location' => $location,
            'bio' => $bio,
            'photo_path' => $photoPath
        ));
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaSanté - Profil public</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-page">
<?php include 'header.php'; ?>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fas fa-cog"></i> Administration</div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="gestion_vendeur.php"><i class="fas fa-user-tie"></i> Gérer les Vendeurs</a>
            <a href="gestion_medicament.php"><i class="fas fa-pills"></i> Gérer les Médicaments</a>
            <a href="gestion_caisse.php"><i class="fas fa-cash-register"></i> Gérer les Caisses</a>
            <a href="admin_profile.php" class="active"><i class="fas fa-address-card"></i> Profil / Informations</a>
            <a href="liste_vente.php"><i class="fas fa-receipt"></i> Toutes les Ventes</a>
            <a href="admin_patients.php"><i class="fas fa-users"></i> Voir les Patients</a>
            <a href="rapport_ventes.php"><i class="fas fa-chart-bar"></i> Rapport des Ventes</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>
    <main class="dashboard-main">
        <div class="page-header">
            <h1><i class="fas fa-address-card"></i> Profil / Informations publiques</h1>
            <p>Ces informations sont affichées dans le pied de page du site.</p>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-<?php echo h($msgType); ?>"><?php echo h($message); ?></div>
        <?php endif; ?>
        <div class="section-card profile-editor-card">
            <div class="profile-editor-preview">
                <?php if (!empty($profile['photo_path'])): ?>
                    <img src="<?php echo h($profile['photo_path']); ?>" alt="Photo de profil" class="profile-photo">
                <?php else: ?>
                    <div class="profile-photo profile-photo-placeholder"><i class="fas fa-user"></i></div>
                <?php endif; ?>
                <strong><?php echo h($profile['full_name']); ?></strong>
                <span><?php echo h($profile['location']); ?></span>
            </div>
            <form method="post" enctype="multipart/form-data" class="profile-editor-form">
                <?php echo csrfField(); ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="full_name">Nom complet</label>
                        <input id="full_name" name="full_name" class="form-input" maxlength="150" required value="<?php echo h($profile['full_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <input id="phone" name="phone" class="form-input" maxlength="30" required value="<?php echo h($profile['phone']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email public</label>
                        <input id="email" type="email" name="email" class="form-input" maxlength="150" required value="<?php echo h($profile['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="location">Ville / localisation</label>
                        <input id="location" name="location" class="form-input" maxlength="200" required value="<?php echo h($profile['location']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="bio">Description courte</label>
                    <textarea id="bio" name="bio" class="form-input" maxlength="500" rows="4" required><?php echo h($profile['bio']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="profile_photo">Photo de profil</label>
                    <input id="profile_photo" type="file" name="profile_photo" class="form-input" accept="image/jpeg,image/png,image/webp">
                    <small class="form-help">JPG, PNG ou WebP, 2 Mo maximum.</small>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer les informations</button>
            </form>
        </div>
    </main>
</div>
<?php include 'footer.php'; ?>
<script src="script.js"></script>
</body>
</html>
