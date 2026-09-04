<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Enregistrement d'une nouvelle vente">
    <meta name="keywords" content="pharmacie, vente, médicament, patient, PharmaSanté, Algérie">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Nouvelle Vente</title>
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
$openCaisse = getOpenCaisseForVendeur($conn, $userId);
$message = ''; $msgType = '';
$receipt = null;

if (!$openCaisse): ?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fas fa-store"></i> Vendeur</div>
        <nav class="sidebar-nav">
            <a href="vendeur_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="caisse_operation.php"><i class="fas fa-cash-register"></i> Gestion Caisse</a>
            <a href="gestion_patient.php"><i class="fas fa-users"></i> Gérer les Patients</a>
            <a href="ajouter_vente.php" class="active"><i class="fas fa-shopping-cart"></i> Nouvelle Vente</a>
            <a href="mes_ventes.php"><i class="fas fa-receipt"></i> Mes Ventes</a>
            <a href="recherche_medicament.php"><i class="fas fa-search"></i> Rechercher Médicament</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>
    <main class="dashboard-main">
        <div class="page-header"><h1><i class="fas fa-shopping-cart"></i> Nouvelle Vente</h1></div>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Veuillez ouvrir une caisse avant d'enregistrer une vente.
            <a href="caisse_operation.php" class="btn btn-primary btn-sm" style="margin-left:10px">Ouvrir une caisse</a>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
</body>
</html>
<?php
    exit;
endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = intval($_POST['patient_id']);
    $medId     = intval($_POST['medicament_id']);
    $quantite  = intval($_POST['quantite']);
    $notes     = trim($_POST['notes']);

    if ($patientId && $medId && $quantite > 0) {
        $stMed = $conn->prepare("SELECT * FROM medicament WHERE id=?");
        $stMed->bind_param("i", $medId);
        $stMed->execute();
        $med = $stMed->get_result()->fetch_assoc();

        if (!$med) {
            $message = "Médicament introuvable."; $msgType = 'danger';
        } elseif ($med['quantite_stock'] < $quantite) {
            $message = "Stock insuffisant. Stock disponible: " . $med['quantite_stock']; $msgType = 'warning';
        } else {
            $prixU = $med['prix_dinar'];
            $total = $prixU * $quantite;
            $caisseId = $openCaisse['id'];

            $stInsert = $conn->prepare("INSERT INTO vente (medicament_id, patient_id, vendeur_id, caisse_id, quantite, prix_unitaire, prix_total, notes) VALUES (?,?,?,?,?,?,?,?)");
            $stInsert->bind_param("iiiiddds", $medId, $patientId, $userId, $caisseId, $quantite, $prixU, $total, $notes);
            if ($stInsert->execute()) {
                $stStock = $conn->prepare("UPDATE medicament SET quantite_stock = quantite_stock - ? WHERE id=?");
                $stStock->bind_param("ii", $quantite, $medId);
                $stStock->execute();

                $stPat = $conn->prepare("SELECT * FROM patient WHERE id=?");
                $stPat->bind_param("i", $patientId);
                $stPat->execute();
                $patient = $stPat->get_result()->fetch_assoc();

                $receipt = array(
                    'patient' => $patient['prenom'] . ' ' . $patient['nom'],
                    'medicament' => $med['nom'],
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixU,
                    'total' => $total,
                    'caisse' => $openCaisse['nom'],
                    'date' => date('d/m/Y H:i')
                );
                $message = "Vente enregistrée avec succès!"; $msgType = 'success';
            } else {
                $message = "Erreur: " . $conn->error; $msgType = 'danger';
            }
        }
    } else {
        $message = "Veuillez remplir tous les champs."; $msgType = 'warning';
    }
}

$patients   = mysqli_query($conn, "SELECT id, nom, prenom, telephone FROM patient ORDER BY nom");
$medicaments= mysqli_query($conn, "SELECT * FROM medicament WHERE quantite_stock > 0 ORDER BY nom");
$prePatient = intval($_GET['patient_id'] ?? 0);
$preMed     = intval($_GET['medicament_id'] ?? 0);
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fas fa-store"></i> Vendeur</div>
        <nav class="sidebar-nav">
            <a href="vendeur_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="caisse_operation.php"><i class="fas fa-cash-register"></i> Gestion Caisse</a>
            <a href="gestion_patient.php"><i class="fas fa-users"></i> Gérer les Patients</a>
            <a href="ajouter_vente.php" class="active"><i class="fas fa-shopping-cart"></i> Nouvelle Vente</a>
            <a href="mes_ventes.php"><i class="fas fa-receipt"></i> Mes Ventes</a>
            <a href="recherche_medicament.php"><i class="fas fa-search"></i> Rechercher Médicament</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="page-header">
            <h1><i class="fas fa-shopping-cart"></i> Nouvelle Vente</h1>
            <span class="badge badge-success"><i class="fas fa-cash-register"></i> <?php echo h($openCaisse['nom']); ?> - OUVERTE</span>
        </div>

        <?php if ($message): echo alert($msgType, $message); endif; ?>

        <?php if ($receipt): ?>
        <div class="receipt-card">
            <div class="receipt-header">
                <i class="fas fa-check-circle"></i>
                <h2>Vente enregistrée!</h2>
            </div>
            <div class="receipt-body">
                <div class="receipt-row"><span>Patient:</span><strong><?php echo h($receipt['patient']); ?></strong></div>
                <div class="receipt-row"><span>Médicament:</span><strong><?php echo h($receipt['medicament']); ?></strong></div>
                <div class="receipt-row"><span>Quantité:</span><strong><?php echo $receipt['quantite']; ?></strong></div>
                <div class="receipt-row"><span>Prix unitaire:</span><strong><?php echo number_format($receipt['prix_unitaire'], 2, ',', ' '); ?> DA</strong></div>
                <div class="receipt-row receipt-total"><span>TOTAL:</span><strong><?php echo number_format($receipt['total'], 2, ',', ' '); ?> DA</strong></div>
                <div class="receipt-row"><span>Caisse:</span><strong><?php echo h($receipt['caisse']); ?></strong></div>
                <div class="receipt-row"><span>Date:</span><strong><?php echo $receipt['date']; ?></strong></div>
            </div>
            <div class="receipt-actions">
                <a href="ajouter_vente.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle vente</a>
                <a href="mes_ventes.php" class="btn btn-outline"><i class="fas fa-list"></i> Mes ventes</a>
            </div>
        </div>
        <?php else: ?>
        <div class="section-card">
            <div class="section-card-header"><h2><i class="fas fa-file-invoice"></i> Formulaire de vente</h2></div>
            <form method="POST" id="venteForm">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Patient *
                            <a href="gestion_patient.php" class="link-small">+ Ajouter patient</a>
                        </label>
                        <select name="patient_id" id="patientSelect" class="form-input" required>
                            <option value="">-- Sélectionner un patient --</option>
                            <?php while ($p = mysqli_fetch_assoc($patients)): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo $prePatient == $p['id'] ? 'selected' : ''; ?>>
                                <?php echo h($p['prenom'] . ' ' . $p['nom']); ?> (<?php echo h($p['telephone']); ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-pills"></i> Médicament *</label>
                        <select name="medicament_id" id="medSelect" class="form-input" required onchange="onMedChange(this)">
                            <option value="">-- Sélectionner un médicament --</option>
                            <?php while ($m = mysqli_fetch_assoc($medicaments)): ?>
                            <option value="<?php echo $m['id']; ?>"
                                data-prix="<?php echo $m['prix_dinar']; ?>"
                                data-stock="<?php echo $m['quantite_stock']; ?>"
                                <?php echo $preMed == $m['id'] ? 'selected' : ''; ?>>
                                <?php echo h($m['nom']); ?> — Stock: <?php echo $m['quantite_stock']; ?> — <?php echo number_format($m['prix_dinar'], 2, ',', ' '); ?> DA
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-sort-numeric-up"></i> Quantité *</label>
                        <input type="number" name="quantite" id="quantiteInput" class="form-input" min="1" value="1" required onchange="calculateTotal()">
                        <small id="stockInfo" class="text-muted"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-sticky-note"></i> Notes</label>
                        <textarea name="notes" class="form-input" rows="2"></textarea>
                    </div>
                </div>

                <div class="price-calculator" id="priceCalc" style="display:none">
                    <div class="price-row"><span>Prix unitaire:</span><span id="prixUnit">0,00 DA</span></div>
                    <div class="price-row"><span>Quantité:</span><span id="qteDisplay">0</span></div>
                    <hr>
                    <div class="price-row price-total"><span>TOTAL:</span><span id="totalDisplay">0,00 DA</span></div>
                </div>

                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-check"></i> Enregistrer la vente</button>
            </form>
        </div>
        <?php endif; ?>
    </main>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
<script>
function onMedChange(sel) {
    var opt = sel.options[sel.selectedIndex];
    var prix = parseFloat(opt.getAttribute('data-prix')) || 0;
    var stock = parseInt(opt.getAttribute('data-stock')) || 0;
    var qteInput = document.getElementById('quantiteInput');
    qteInput.max = stock;
    document.getElementById('stockInfo').textContent = 'Stock disponible: ' + stock;
    document.getElementById('priceCalc').style.display = prix > 0 ? 'block' : 'none';
    calculateTotal();
}
function calculateTotal() {
    var sel = document.getElementById('medSelect');
    var opt = sel.options[sel.selectedIndex];
    var prix = parseFloat(opt.getAttribute('data-prix')) || 0;
    var qte = parseInt(document.getElementById('quantiteInput').value) || 0;
    var total = prix * qte;
    document.getElementById('prixUnit').textContent = prix.toFixed(2).replace('.', ',') + ' DA';
    document.getElementById('qteDisplay').textContent = qte;
    document.getElementById('totalDisplay').textContent = total.toFixed(2).replace('.', ',') + ' DA';
    validateStock(document.getElementById('quantiteInput'), parseInt(opt.getAttribute('data-stock')));
}
window.onload = function() {
    var sel = document.getElementById('medSelect');
    if (sel.selectedIndex > 0) onMedChange(sel);
};
</script>
</body>
</html>
