<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaSanté — Installation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',sans-serif;background:#f0f4f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .card{background:#fff;border-radius:16px;padding:40px;max-width:620px;width:100%;box-shadow:0 4px 24px rgba(0,0,0,.12)}
        .logo{text-align:center;margin-bottom:28px}
        .logo i{font-size:48px;color:#2e7d32}
        .logo h1{color:#2e7d32;font-size:26px;margin-top:8px}
        .logo p{color:#777;font-size:13px;margin-top:4px}
        .step{display:flex;align-items:flex-start;gap:14px;padding:11px 0;border-bottom:1px solid #f0f0f0}
        .step:last-child{border-bottom:none}
        .ico{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;margin-top:1px}
        .ok{background:#e8f5e9;color:#2e7d32}
        .fail{background:#ffebee;color:#c62828}
        .info{background:#e3f2fd;color:#1565c0}
        .step-text strong{display:block;font-size:14px;color:#333}
        .step-text span{font-size:12px;color:#666;margin-top:2px;display:block}
        .btn{display:block;width:100%;padding:14px;background:#2e7d32;color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;text-align:center;text-decoration:none;margin-top:20px}
        .btn:hover{background:#1b5e20}
        .btn-out{background:#fff;color:#2e7d32;border:2px solid #2e7d32}
        .btn-out:hover{background:#f1f8e9}
        .alert{padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:13px;line-height:1.6}
        .a-ok{background:#e8f5e9;color:#2e7d32;border-left:4px solid #2e7d32}
        .a-err{background:#ffebee;color:#c62828;border-left:4px solid #c62828}
        .a-warn{background:#fff8e1;color:#e65100;border-left:4px solid #f9a825}
        .accounts{background:#f9f9f9;border-radius:10px;padding:16px;margin-top:20px}
        .accounts h3{font-size:13px;color:#555;margin-bottom:10px}
        .ar{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #eee;font-size:13px}
        .ar:last-child{border-bottom:none}
        .rb{padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700}
        .ra{background:#1565c0;color:#fff}
        .rv{background:#2e7d32;color:#fff}
        .rp{background:#6a1b9a;color:#fff}
        code{background:#f0f0f0;padding:2px 6px;border-radius:4px;font-size:12px;font-family:monospace}
        h2{font-size:16px;color:#333;margin-bottom:14px}
        hr{border:none;border-top:2px solid #f0f0f0;margin:20px 0}
        .warning-box{background:#fff3e0;border:2px solid #ff9800;border-radius:10px;padding:14px;margin-top:16px;font-size:13px;color:#e65100}
    </style>
</head>
<body>
<?php
$host   = 'localhost';
$user   = 'root';
$pass   = '';
$dbname = 'pharmacare_db';

$steps   = [];
$errors  = [];
$done    = false;

$checkConn = @new mysqli($host, $user, $pass, $dbname);
$alreadyOk = false;
if (!$checkConn->connect_error) {
    $r = $checkConn->query("SHOW COLUMNS FROM `medicament` LIKE 'categorie'");
    $alreadyOk = ($r && $r->num_rows > 0);
    $checkConn->close();
}

if (isset($_POST['install'])) {

    $conn = @new mysqli($host, $user, $pass);
    if ($conn->connect_error) {
        $errors[] = "Connexion MySQL impossible : " . $conn->connect_error;
    } else {
        $conn->set_charset("utf8mb4");
        $steps[] = ['ok', 'Connexion MySQL', 'Connecté à ' . $host];

        $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conn->select_db($dbname);
        $steps[] = ['ok', 'Base de données', "`$dbname` sélectionnée"];

        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        foreach (['vente','password_resets','caisse','medicament','patient','vendeur'] as $t) {
            $conn->query("DROP TABLE IF EXISTS `$t`");
        }
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $steps[] = ['ok', 'Nettoyage', 'Anciennes tables supprimées (corrige colonnes manquantes)'];

        $ddl = [
        "CREATE TABLE `vendeur` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `nom` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `role` ENUM('admin','vendeur') DEFAULT 'vendeur',
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`), UNIQUE KEY (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE `patient` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `nom` VARCHAR(100) NOT NULL,
            `prenom` VARCHAR(100) NOT NULL,
            `telephone` VARCHAR(20) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `adresse` TEXT, `date_naissance` DATE,
            `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`), UNIQUE KEY (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE `medicament` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `nom` VARCHAR(150) NOT NULL,
            `description` TEXT,
            `prix_dinar` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `quantite_stock` INT NOT NULL DEFAULT 0,
            `categorie` VARCHAR(100) DEFAULT NULL,
            `date_ajout` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE `caisse` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `nom` VARCHAR(100) NOT NULL,
            `montant_ouverture` DECIMAL(10,2) DEFAULT 0.00,
            `montant_fermeture` DECIMAL(10,2) DEFAULT NULL,
            `heure_ouverture` DATETIME DEFAULT NULL,
            `heure_fermeture` DATETIME DEFAULT NULL,
            `statut` ENUM('ouverte','fermee') DEFAULT 'fermee',
            `vendeur_id` INT DEFAULT NULL,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`vendeur_id`) REFERENCES `vendeur`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE `vente` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `date_vente` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `medicament_id` INT NOT NULL, `patient_id` INT NOT NULL,
            `vendeur_id` INT NOT NULL, `caisse_id` INT NOT NULL,
            `quantite` INT NOT NULL,
            `prix_unitaire` DECIMAL(10,2) NOT NULL,
            `prix_total` DECIMAL(10,2) NOT NULL,
            `notes` TEXT DEFAULT NULL,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`medicament_id`) REFERENCES `medicament`(`id`),
            FOREIGN KEY (`patient_id`)    REFERENCES `patient`(`id`),
            FOREIGN KEY (`vendeur_id`)    REFERENCES `vendeur`(`id`),
            FOREIGN KEY (`caisse_id`)     REFERENCES `caisse`(`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE `password_resets` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `email` VARCHAR(100) NOT NULL,
            `token` VARCHAR(64) NOT NULL,
            `table_source` ENUM('vendeur','patient') NOT NULL,
            `expires_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            INDEX (`token`), INDEX (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];

        $allOk = true;
        foreach ($ddl as $sql) {
            if (!$conn->query($sql)) { $errors[] = $conn->error; $allOk = false; }
        }
        if ($allOk) $steps[] = ['ok', 'Tables créées', 'vendeur, patient, medicament, caisse, vente, password_resets'];

        $dataQueries = [
        "INSERT INTO `vendeur` (`nom`,`email`,`password`,`role`) VALUES
            ('Administrateur','admin@pharmacare.dz','" . password_hash('admin123',   PASSWORD_BCRYPT) . "','admin'),
            ('Karim Benali',  'karim@pharmacare.dz', '" . password_hash('vendeur123', PASSWORD_BCRYPT) . "','vendeur'),
            ('Samira Hamidi', 'samira@pharmacare.dz','" . password_hash('vendeur456', PASSWORD_BCRYPT) . "','vendeur')",

        "INSERT INTO `patient` (`nom`,`prenom`,`telephone`,`email`,`password`,`adresse`,`date_naissance`) VALUES
            ('Mekki', 'Youcef','0770123456','youcef@gmail.com', '" . password_hash('patient123', PASSWORD_BCRYPT) . "','12 Rue Didouche, Alger','1995-06-15'),
            ('Aouad', 'Lina',  '0661234567','lina@gmail.com',   '" . password_hash('patient123', PASSWORD_BCRYPT) . "','5 Cité 1000 Log, Oran','1998-03-22'),
            ('Brahim','Karima','0550987654','karima@gmail.com', '" . password_hash('patient123', PASSWORD_BCRYPT) . "','8 Rue Bab Azoun, Constantine','1990-11-05')",

        "INSERT INTO `medicament` (`nom`,`description`,`prix_dinar`,`quantite_stock`,`categorie`) VALUES
            ('Paracétamol 500mg','Analgésique et antipyrétique',150.00,200,'Analgésique'),
            ('Doliprane 1000mg','Paracétamol haute dose adulte',165.00,130,'Analgésique'),
            ('Ibuprofène 400mg','Anti-inflammatoire non stéroïdien',180.00,150,'Anti-inflammatoire'),
            ('Diclofénac 50mg','Anti-inflammatoire et analgésique',195.00,85,'Anti-inflammatoire'),
            ('Amoxicilline 500mg','Antibiotique à large spectre',320.00,80,'Antibiotique'),
            ('Doxycycline 100mg','Antibiotique tétracycline',290.00,45,'Antibiotique'),
            ('Metformine 850mg','Traitement du diabète de type 2',210.00,60,'Antidiabétique'),
            ('Cétirizine 10mg','Antihistaminique pour les allergies',160.00,100,'Antihistaminique'),
            ('Loratadine 10mg','Antihistaminique de 2e génération',175.00,75,'Antihistaminique'),
            ('Amlodipine 5mg','Traitement de l hypertension artérielle',240.00,90,'Cardiovasculaire'),
            ('Simvastatine 20mg','Hypolipémiant pour réduire le cholestérol',260.00,55,'Cardiovasculaire'),
            ('Oméprazole 20mg','Inhibiteur de la pompe à protons',280.00,120,'Gastro-entérologie'),
            ('Salbutamol Spray','Bronchodilatateur pour l asthme',420.00,40,'Respiratoire'),
            ('Vitamine C 1000mg','Supplément de vitamine C',120.00,250,'Vitamines'),
            ('Zinc 15mg','Complément alimentaire en zinc',140.00,180,'Vitamines')",

        "INSERT INTO `caisse` (`nom`,`montant_ouverture`,`statut`,`vendeur_id`) VALUES
            ('Caisse Principale',5000.00,'fermee',2)"
        ];

        $dataOk = true;
        foreach ($dataQueries as $sql) {
            if (!$conn->query($sql)) { $errors[] = $conn->error; $dataOk = false; }
        }
        if ($dataOk) $steps[] = ['ok', 'Données de démonstration', '3 vendeurs, 3 patients, 15 médicaments, 1 caisse insérés'];

        $conn->close();
        if (empty($errors)) $done = true;
    }
}
?>

<div class="card">
    <div class="logo">
        <i class="fas fa-pills"></i>
        <h1>PharmaSanté</h1>
        <p>TP Final DAW — L2 INF G 04 — UHBC FSEI</p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert a-err">
        <i class="fas fa-exclamation-circle"></i> <strong>Erreur :</strong><br>
        <?php foreach ($errors as $e): ?><?php echo htmlspecialchars($e); ?><br><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($done): ?>
        <div class="alert a-ok"><i class="fas fa-check-circle"></i> <strong>Installation réussie !</strong> La base de données est prête.</div>
        <h2>Étapes effectuées</h2>
        <?php foreach ($steps as $s): ?>
        <div class="step">
            <div class="ico ok"><i class="fas fa-check"></i></div>
            <div class="step-text"><strong><?php echo $s[1]; ?></strong><span><?php echo $s[2]; ?></span></div>
        </div>
        <?php endforeach; ?>
        <div class="accounts">
            <h3><i class="fas fa-users"></i> &nbsp;Comptes de démonstration</h3>
            <div class="ar"><span><span class="rb ra">ADMIN</span> &nbsp;admin@pharmacare.dz</span><code>admin123</code></div>
            <div class="ar"><span><span class="rb rv">VENDEUR</span> &nbsp;karim@pharmacare.dz</span><code>vendeur123</code></div>
            <div class="ar"><span><span class="rb rv">VENDEUR</span> &nbsp;samira@pharmacare.dz</span><code>vendeur456</code></div>
            <div class="ar"><span><span class="rb rp">PATIENT</span> &nbsp;youcef@gmail.com</span><code>patient123</code></div>
        </div>
        <a href="index.php" class="btn"><i class="fas fa-rocket"></i> &nbsp;Lancer PharmaSanté</a>

    <?php else: ?>
        <?php if ($alreadyOk): ?>
        <div class="alert a-ok"><i class="fas fa-database"></i> Base de données déjà correcte. Vous pouvez utiliser l'application.</div>
        <?php else: ?>
        <div class="alert a-warn"><i class="fas fa-exclamation-triangle"></i> <strong>Base de données manquante ou incomplète.</strong><br>Cliquez sur "Installer" — le script va tout créer automatiquement.</div>
        <?php endif; ?>

        <h2>Ce que fait l'installation</h2>
        <div class="step"><div class="ico info"><i class="fas fa-database"></i></div><div class="step-text"><strong>Crée la base <code>pharmacare_db</code></strong><span>En utf8mb4 — supporte les accents</span></div></div>
        <div class="step"><div class="ico info"><i class="fas fa-trash-alt"></i></div><div class="step-text"><strong>Supprime et recrée toutes les tables</strong><span>Règle les erreurs "Unknown column" (categorie, actif, etc.)</span></div></div>
        <div class="step"><div class="ico info"><i class="fas fa-table"></i></div><div class="step-text"><strong>6 tables créées proprement</strong><span>vendeur, patient, medicament, caisse, vente, password_resets</span></div></div>
        <div class="step"><div class="ico info"><i class="fas fa-pills"></i></div><div class="step-text"><strong>15 médicaments + comptes de démo insérés</strong><span>Mots de passe hachés en bcrypt</span></div></div>

        <hr>
        <form method="POST">
            <button type="submit" name="install" class="btn"><i class="fas fa-download"></i> &nbsp;Installer la base de données</button>
        </form>
        <?php if ($alreadyOk): ?>
        <a href="index.php" class="btn btn-out" style="margin-top:10px"><i class="fas fa-arrow-right"></i> &nbsp;Accéder sans réinstaller</a>
        <?php endif; ?>

        <div class="warning-box" style="margin-top:20px">
            <i class="fas fa-info-circle"></i> <strong>Connexion MySQL par défaut :</strong>
            hôte = <code>localhost</code>, utilisateur = <code>root</code>, mot de passe = <code>(vide)</code>.
            Si vous avez un mot de passe MySQL, modifiez <code>config/database.php</code> et les variables <code>$host/$user/$pass</code> en haut de ce fichier.
        </div>
    <?php endif; ?>
</div>
</body>
</html>
