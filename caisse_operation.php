<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Opérations de caisse enregistreuse">
    <meta name="keywords" content="pharmacie, caisse, ouverture, fermeture, vendeur, PharmaSanté, Algérie">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Opérations Caisse</title>
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

$userId = $_SESSION['user_id'];
$message = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'open') {
        $caisseId = intval($_POST['caisse_id']);
        $montant  = floatval($_POST['montant_ouverture']);
        $chk = $conn->prepare("SELECT statut FROM caisse WHERE id=? AND statut='fermee'");
        $chk->bind_param("i", $caisseId);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE caisse SET statut='ouverte', heure_ouverture=NOW(), montant_ouverture=?, montant_fermeture=NULL, heure_fermeture=NULL, vendeur_id=? WHERE id=?");
            $stmt->bind_param("dii", $montant, $userId, $caisseId);
            if ($stmt->execute()) { $message = "Caisse ouverte avec succès."; $msgType = 'success'; }
            else { $message = "Erreur: " . $conn->error; $msgType = 'danger'; }
        } else {
            $message = "Cette caisse est déjà ouverte."; $msgType = 'warning';
        }
    }
    if ($action === 'close') {
        $caisseId = intval($_POST['caisse_id']);
        $montant  = floatval($_POST['montant_fermeture']);
        $stmt = $conn->prepare("UPDATE caisse SET statut='fermee', heure_fermeture=NOW(), montant_fermeture=?, vendeur_id=NULL WHERE id=? AND vendeur_id=?");
        $stmt->bind_param("dii", $montant, $caisseId, $userId);
        if ($stmt->execute()) { $message = "Caisse fermée avec succès."; $msgType = 'success'; }
        else { $message = "Erreur: " . $conn->error; $msgType = 'danger'; }
    }
}

$openCaisse = getOpenCaisseForVendeur($conn, $userId);
$closedCaisses = mysqli_query($conn, "SELECT * FROM caisse WHERE statut='fermee' ORDER BY nom");

$stHist = $conn->prepare("SELECT c.nom AS caisse_nom, c.heure_ouverture, c.heure_fermeture, c.montant_ouverture, c.montant_fermeture, c.statut
    FROM caisse c WHERE c.vendeur_id=? OR (c.statut='fermee' AND EXISTS(SELECT 1 FROM vente v WHERE v.caisse_id=c.id AND v.vendeur_id=?))
    ORDER BY c.heure_ouverture DESC LIMIT 5");
$stHist->bind_param("ii", $userId, $userId);
$stHist->execute();
$history = $stHist->get_result();
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fas fa-store"></i> Vendeur</div>
        <nav class="sidebar-nav">
            <a href="vendeur_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="caisse_operation.php" class="active"><i class="fas fa-cash-register"></i> Gestion Caisse</a>
            <a href="gestion_patient.php"><i class="fas fa-users"></i> Gérer les Patients</a>
            <a href="ajouter_vente.php"><i class="fas fa-shopping-cart"></i> Nouvelle Vente</a>
            <a href="mes_ventes.php"><i class="fas fa-receipt"></i> Mes Ventes</a>
            <a href="recherche_medicament.php"><i class="fas fa-search"></i> Rechercher Médicament</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="page-header"><h1><i class="fas fa-cash-register"></i> Gestion de la Caisse</h1></div>

        <?php if ($message): echo alert($msgType, $message); endif; ?>

        <?php if ($openCaisse): ?>
        <div class="section-card caisse-open-card">
            <h2><i class="fas fa-lock-open"></i> Caisse en cours</h2>
            <div class="caisse-info-grid">
                <div class="caisse-info-item">
                    <span class="ci-label">Caisse</span>
                    <span class="ci-value"><?php echo h($openCaisse['nom']); ?></span>
                </div>
                <div class="caisse-info-item">
                    <span class="ci-label">Ouverture</span>
                    <span class="ci-value"><?php echo formatDate($openCaisse['heure_ouverture']); ?></span>
                </div>
                <div class="caisse-info-item">
                    <span class="ci-label">Montant d'ouverture</span>
                    <span class="ci-value"><?php echo number_format($openCaisse['montant_ouverture'], 2, ',', ' '); ?> DA</span>
                </div>
                <div class="caisse-info-item">
                    <span class="ci-label">Durée de session</span>
                    <span class="ci-value" id="timerDisplay"><i class="fas fa-clock"></i> Calcul...</span>
                </div>
            </div>
            <form method="POST" class="close-form">
                <input type="hidden" name="action" value="close">
                <input type="hidden" name="caisse_id" value="<?php echo $openCaisse['id']; ?>">
                <div class="form-group">
                    <label><i class="fas fa-money-bill"></i> Montant de fermeture (DA)</label>
                    <input type="number" name="montant_fermeture" class="form-input" step="0.01" min="0" required>
                </div>
                <button type="submit" class="btn btn-danger btn-block"><i class="fas fa-lock"></i> Fermer la caisse</button>
            </form>
        </div>
        <?php else: ?>
        <div class="section-card">
            <h2><i class="fas fa-door-open"></i> Ouvrir une caisse</h2>
            <?php if (mysqli_num_rows($closedCaisses) === 0): ?>
            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Aucune caisse disponible. Contactez l'administrateur.</div>
            <?php else: ?>
            <form method="POST">
                <input type="hidden" name="action" value="open">
                <div class="form-group">
                    <label><i class="fas fa-cash-register"></i> Sélectionner une caisse</label>
                    <select name="caisse_id" class="form-input" required>
                        <option value="">-- Choisir --</option>
                        <?php while ($c = mysqli_fetch_assoc($closedCaisses)): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo h($c['nom']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-money-bill"></i> Montant d'ouverture (DA)</label>
                    <input type="number" name="montant_ouverture" class="form-input" step="0.01" min="0" value="0" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-door-open"></i> Ouvrir la caisse</button>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="section-card">
            <div class="section-card-header"><h2><i class="fas fa-history"></i> Historique récent</h2></div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Caisse</th><th>Ouverture</th><th>Fermeture</th><th>Montant ouv.</th><th>Montant ferm.</th><th>Statut</th></tr></thead>
                    <tbody>
                    <?php $hasRows = false; while ($h = $history->fetch_assoc()): $hasRows = true; ?>
                        <tr>
                            <td><?php echo h($h['caisse_nom']); ?></td>
                            <td><?php echo formatDate($h['heure_ouverture']); ?></td>
                            <td><?php echo formatDate($h['heure_fermeture']); ?></td>
                            <td><?php echo number_format($h['montant_ouverture'], 2, ',', ' '); ?> DA</td>
                            <td><?php echo $h['montant_fermeture'] !== null ? number_format($h['montant_fermeture'], 2, ',', ' ') . ' DA' : '-'; ?></td>
                            <td><?php echo $h['statut'] === 'ouverte' ? '<span class="badge badge-success">Ouverte</span>' : '<span class="badge badge-danger">Fermée</span>'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if (!$hasRows): ?><tr><td colspan="6" class="text-center text-muted">Aucun historique.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
<?php if ($openCaisse): ?>
<script>
var openTime = new Date('<?php echo $openCaisse['heure_ouverture']; ?>');
updateTimer(openTime);
setInterval(function(){ updateTimer(openTime); }, 1000);
</script>
<?php endif; ?>
</body>
</html>
