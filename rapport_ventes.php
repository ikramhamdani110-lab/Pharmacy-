<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaSanté - Rapport des ventes">
    <meta name="author" content="L2 INF G 04 - UHBC">
    <title>PharmaSanté - Rapport des Ventes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        @media print {
            .sidebar, .no-print, .site-footer, header.site-header, .filter-card { display: none !important; }
            .dashboard-layout { display: block !important; }
            .dashboard-main { margin: 0 !important; padding: 0 !important; width: 100% !important; }
            body { background: #fff !important; font-size: 12px; }
            .print-header { display: block !important; }
            .stat-card { border: 1px solid #ccc !important; background: #fff !important; break-inside: avoid; }
            .stats-grid { grid-template-columns: repeat(4, 1fr) !important; }
            .section-card { break-inside: avoid; box-shadow: none !important; border: 1px solid #ddd !important; }
            .data-table th { background: #2e7d32 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .table-total td { background: #e8f5e9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .top-item { border: 1px solid #ddd !important; }
            a { text-decoration: none !important; color: inherit !important; }
            .page-break { page-break-before: always; }
        }
        .print-header { display: none; text-align: center; margin-bottom: 24px; border-bottom: 3px solid #2e7d32; padding-bottom: 16px; }
        .print-header h1 { color: #2e7d32; font-size: 24px; margin: 0 0 4px; }
        .print-header p { color: #555; margin: 2px 0; font-size: 13px; }
        .filter-card { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .filter-card h3 { margin: 0 0 14px; color: #2e7d32; font-size: 15px; }
        .filter-row { display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; }
        .filter-row .form-group { flex: 1; min-width: 140px; }
        .filter-row label { font-size: 12px; color: #555; display: block; margin-bottom: 4px; }
        .filter-row .form-input { height: 36px; font-size: 13px; }
        .period-btns { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
        .period-btn { padding: 6px 14px; border-radius: 20px; border: 2px solid #2e7d32; background: #fff; color: #2e7d32; font-size: 12px; font-weight: 600; cursor: pointer; transition: all .2s; }
        .period-btn:hover, .period-btn.active { background: #2e7d32; color: #fff; }
        .rapport-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .rstat { background: #fff; border-radius: 10px; padding: 18px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 4px solid #2e7d32; }
        .rstat .rstat-val { font-size: 22px; font-weight: 700; color: #2e7d32; }
        .rstat .rstat-lbl { font-size: 12px; color: #777; margin-top: 4px; }
        .rstat.blue  { border-color: #1565c0; } .rstat.blue .rstat-val { color: #1565c0; }
        .rstat.orange { border-color: #e65100; } .rstat.orange .rstat-val { color: #e65100; }
        .rstat.purple { border-color: #6a1b9a; } .rstat.purple .rstat-val { color: #6a1b9a; }
        .rstat.teal  { border-color: #00695c; } .rstat.teal .rstat-val { color: #00695c; }
        .top-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
        .top-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .top-card h3 { margin: 0 0 14px; font-size: 14px; color: #333; }
        .top-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; margin-bottom: 6px; background: #f9f9f9; }
        .top-rank { width: 24px; height: 24px; border-radius: 50%; background: #2e7d32; color: #fff; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .top-rank.r2 { background: #1565c0; } .top-rank.r3 { background: #6a1b9a; } .top-rank.r4 { background: #e65100; } .top-rank.r5 { background: #555; }
        .top-name { flex: 1; font-size: 13px; font-weight: 500; }
        .top-val { font-size: 13px; color: #2e7d32; font-weight: 700; }
        .cat-bar-wrap { margin-bottom: 8px; }
        .cat-bar-label { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 3px; }
        .cat-bar-bg { background: #e0e0e0; border-radius: 4px; height: 8px; }
        .cat-bar-fill { background: #2e7d32; border-radius: 4px; height: 8px; }
        .rapport-title-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .rapport-title-row h1 { margin: 0; font-size: 20px; }
        .btn-print { background: #2e7d32; color: #fff; border: none; padding: 10px 22px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .btn-print:hover { background: #1b5e20; }
        .period-label { font-size: 13px; color: #555; background: #e8f5e9; padding: 4px 12px; border-radius: 20px; }
        .signature-block { margin-top: 40px; display: flex; justify-content: space-between; }
        .sig-box { text-align: center; }
        .sig-box .sig-title { font-size: 13px; color: #333; font-weight: 600; margin-bottom: 40px; }
        .sig-box .sig-line { border-top: 1px solid #555; width: 160px; margin: 0 auto; padding-top: 6px; font-size: 12px; color: #777; }
        @media (max-width: 768px) { .top-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="dashboard-page">
<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
requireAdmin();
include 'header.php';

$period   = trim($_GET['period']   ?? 'month');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo   = trim($_GET['date_to']   ?? '');

$today = date('Y-m-d');
if ($period === 'today') {
    $dateFrom = $dateTo = $today;
} elseif ($period === 'week') {
    $dateFrom = date('Y-m-d', strtotime('monday this week'));
    $dateTo   = $today;
} elseif ($period === 'month') {
    $dateFrom = date('Y-m-01');
    $dateTo   = $today;
} elseif ($period === 'year') {
    $dateFrom = date('Y-01-01');
    $dateTo   = $today;
} elseif ($period === 'custom') {
    if (!$dateFrom) $dateFrom = date('Y-m-01');
    if (!$dateTo)   $dateTo   = $today;
} else {
    $period   = 'month';
    $dateFrom = date('Y-m-01');
    $dateTo   = $today;
}

$periodLabels = [
    'today'  => "Aujourd'hui (" . date('d/m/Y') . ")",
    'week'   => 'Cette semaine',
    'month'  => 'Ce mois (' . strftime('%B %Y') . ')',
    'year'   => 'Cette année (' . date('Y') . ')',
    'custom' => 'Personnalisé : ' . date('d/m/Y', strtotime($dateFrom)) . ' → ' . date('d/m/Y', strtotime($dateTo)),
];
$periodLabel = $periodLabels[$period] ?? '';

$stKpi = $conn->prepare("SELECT COUNT(*) AS nb_ventes,
    COALESCE(SUM(prix_total),0) AS chiffre_affaires,
    COALESCE(AVG(prix_total),0) AS panier_moyen,
    COALESCE(SUM(quantite),0)  AS unites_vendues,
    COUNT(DISTINCT patient_id) AS patients_servis,
    COUNT(DISTINCT vendeur_id) AS vendeurs_actifs
    FROM vente WHERE DATE(date_vente) BETWEEN ? AND ?");
$stKpi->bind_param("ss", $dateFrom, $dateTo);
$stKpi->execute();
$kpi = $stKpi->get_result()->fetch_assoc();

$stTopMed = $conn->prepare("SELECT m.nom, SUM(v.quantite) AS qte_totale, SUM(v.prix_total) AS ca_total
    FROM vente v JOIN medicament m ON v.medicament_id = m.id
    WHERE DATE(v.date_vente) BETWEEN ? AND ?
    GROUP BY m.id ORDER BY qte_totale DESC LIMIT 5");
$stTopMed->bind_param("ss", $dateFrom, $dateTo);
$stTopMed->execute();
$topMeds = $stTopMed->get_result()->fetch_all(MYSQLI_ASSOC);

$stTopVend = $conn->prepare("SELECT vd.nom, COUNT(v.id) AS nb, SUM(v.prix_total) AS ca
    FROM vente v JOIN vendeur vd ON v.vendeur_id = vd.id
    WHERE DATE(v.date_vente) BETWEEN ? AND ?
    GROUP BY vd.id ORDER BY ca DESC LIMIT 5");
$stTopVend->bind_param("ss", $dateFrom, $dateTo);
$stTopVend->execute();
$topVend = $stTopVend->get_result()->fetch_all(MYSQLI_ASSOC);

$stCat = $conn->prepare("SELECT m.categorie, SUM(v.prix_total) AS ca, SUM(v.quantite) AS qte
    FROM vente v JOIN medicament m ON v.medicament_id = m.id
    WHERE DATE(v.date_vente) BETWEEN ? AND ?
    GROUP BY m.categorie ORDER BY ca DESC");
$stCat->bind_param("ss", $dateFrom, $dateTo);
$stCat->execute();
$byCat = $stCat->get_result()->fetch_all(MYSQLI_ASSOC);
$maxCatCa = !empty($byCat) ? max(array_column($byCat, 'ca')) : 1;

$stDaily = $conn->prepare("SELECT DATE(date_vente) AS jour, COUNT(*) AS nb, SUM(prix_total) AS ca
    FROM vente WHERE DATE(date_vente) BETWEEN ? AND ?
    GROUP BY DATE(date_vente) ORDER BY DATE(date_vente) DESC");
$stDaily->bind_param("ss", $dateFrom, $dateTo);
$stDaily->execute();
$daily = $stDaily->get_result()->fetch_all(MYSQLI_ASSOC);

$stDetail = $conn->prepare("SELECT v.date_vente, vd.nom AS vendeur_nom,
    p.prenom AS pprenom, p.nom AS pnom,
    m.nom AS mnom, m.categorie, v.quantite, v.prix_unitaire, v.prix_total, c.nom AS caisse_nom
    FROM vente v
    JOIN vendeur vd ON v.vendeur_id = vd.id
    JOIN patient p  ON v.patient_id = p.id
    JOIN medicament m ON v.medicament_id = m.id
    JOIN caisse c ON v.caisse_id = c.id
    WHERE DATE(v.date_vente) BETWEEN ? AND ?
    ORDER BY v.date_vente DESC");
$stDetail->bind_param("ss", $dateFrom, $dateTo);
$stDetail->execute();
$detail = $stDetail->get_result()->fetch_all(MYSQLI_ASSOC);

$rankColors = ['r1','r2','r3','r4','r5'];
?>

<div class="dashboard-layout">
    <aside class="sidebar no-print">
        <div class="sidebar-header"><i class="fas fa-cog"></i> Administration</div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            <a href="gestion_vendeur.php"><i class="fas fa-user-tie"></i> Gérer les Vendeurs</a>
            <a href="gestion_medicament.php"><i class="fas fa-pills"></i> Gérer les Médicaments</a>
            <a href="gestion_caisse.php"><i class="fas fa-cash-register"></i> Gérer les Caisses</a>
            <a href="liste_vente.php"><i class="fas fa-receipt"></i> Toutes les Ventes</a>
            <a href="admin_patients.php"><i class="fas fa-users"></i> Voir les Patients</a>
            <a href="rapport_ventes.php" class="active"><i class="fas fa-chart-bar"></i> Rapport des Ventes</a>
            <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </aside>

    <main class="dashboard-main">

        <!-- Print-only header -->
        <div class="print-header">
            <h1><i class="fas fa-pills"></i> PharmaSanté</h1>
            <p>Votre santé, notre priorité</p>
            <p>123 Rue de la Santé, Alger, Algérie &nbsp;|&nbsp; contact@pharmacare.dz</p>
            <p style="font-size:15px;font-weight:700;margin-top:8px;">RAPPORT DES VENTES — <?php echo strtoupper($periodLabel); ?></p>
            <p>Édité le <?php echo date('d/m/Y à H:i'); ?> par <?php echo h($_SESSION['user_name']); ?></p>
        </div>

        <!-- Title row -->
        <div class="rapport-title-row no-print">
            <div>
                <h1><i class="fas fa-chart-bar"></i> Rapport des Ventes</h1>
                <span class="period-label"><i class="fas fa-calendar-alt"></i> <?php echo $periodLabel; ?></span>
            </div>
            <button class="btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimer / Exporter PDF
            </button>
        </div>

        <!-- Period selector -->
        <div class="filter-card no-print">
            <h3><i class="fas fa-filter"></i> Sélectionner la période</h3>
            <div class="period-btns">
                <?php foreach (['today'=>"Aujourd'hui",'week'=>'Cette semaine','month'=>'Ce mois','year'=>'Cette année','custom'=>'Personnalisé'] as $k => $lbl): ?>
                <button class="period-btn <?php echo $period===$k?'active':''; ?>"
                    onclick="setPeriod('<?php echo $k; ?>')"><?php echo $lbl; ?></button>
                <?php endforeach; ?>
            </div>
            <form method="GET" id="filterForm">
                <input type="hidden" name="period" id="periodInput" value="<?php echo h($period); ?>">
                <div class="filter-row" id="customRange" style="<?php echo $period==='custom'?'':'display:none'; ?>">
                    <div class="form-group">
                        <label>Du</label>
                        <input type="date" name="date_from" class="form-input" value="<?php echo h($dateFrom); ?>">
                    </div>
                    <div class="form-group">
                        <label>Au</label>
                        <input type="date" name="date_to" class="form-input" value="<?php echo h($dateTo); ?>">
                    </div>
                    <div class="form-group" style="flex:0">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">Appliquer</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- KPI cards -->
        <div class="rapport-stats">
            <div class="rstat">
                <div class="rstat-val"><?php echo number_format($kpi['nb_ventes']); ?></div>
                <div class="rstat-lbl"><i class="fas fa-shopping-cart"></i> Ventes réalisées</div>
            </div>
            <div class="rstat blue">
                <div class="rstat-val"><?php echo number_format($kpi['chiffre_affaires'], 2, ',', ' '); ?> DA</div>
                <div class="rstat-lbl"><i class="fas fa-coins"></i> Chiffre d'affaires</div>
            </div>
            <div class="rstat orange">
                <div class="rstat-val"><?php echo number_format($kpi['panier_moyen'], 2, ',', ' '); ?> DA</div>
                <div class="rstat-lbl"><i class="fas fa-calculator"></i> Panier moyen</div>
            </div>
            <div class="rstat purple">
                <div class="rstat-val"><?php echo number_format($kpi['unites_vendues']); ?></div>
                <div class="rstat-lbl"><i class="fas fa-boxes"></i> Unités vendues</div>
            </div>
            <div class="rstat teal">
                <div class="rstat-val"><?php echo number_format($kpi['patients_servis']); ?></div>
                <div class="rstat-lbl"><i class="fas fa-users"></i> Patients servis</div>
            </div>
            <div class="rstat">
                <div class="rstat-val"><?php echo number_format($kpi['vendeurs_actifs']); ?></div>
                <div class="rstat-lbl"><i class="fas fa-user-tie"></i> Vendeurs actifs</div>
            </div>
        </div>

        <!-- Top medicines + Top vendeurs -->
        <div class="top-grid">
            <div class="top-card">
                <h3><i class="fas fa-star" style="color:#f9a825"></i> Top 5 Médicaments (par quantité)</h3>
                <?php if (empty($topMeds)): ?>
                <p class="text-muted">Aucune donnée.</p>
                <?php else: foreach ($topMeds as $i => $m): ?>
                <div class="top-item">
                    <div class="top-rank <?php echo $rankColors[$i] ?? ''; ?>"><?php echo $i+1; ?></div>
                    <div class="top-name"><?php echo h($m['nom']); ?></div>
                    <div class="top-val"><?php echo $m['qte_totale']; ?> unités</div>
                </div>
                <?php endforeach; endif; ?>
            </div>
            <div class="top-card">
                <h3><i class="fas fa-trophy" style="color:#f9a825"></i> Performance Vendeurs (CA)</h3>
                <?php if (empty($topVend)): ?>
                <p class="text-muted">Aucune donnée.</p>
                <?php else: foreach ($topVend as $i => $vd): ?>
                <div class="top-item">
                    <div class="top-rank <?php echo $rankColors[$i] ?? ''; ?>"><?php echo $i+1; ?></div>
                    <div class="top-name"><?php echo h($vd['nom']); ?> <span style="font-size:11px;color:#999">(<?php echo $vd['nb']; ?> ventes)</span></div>
                    <div class="top-val"><?php echo number_format($vd['ca'], 0, ',', ' '); ?> DA</div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- Sales by category -->
        <?php if (!empty($byCat)): ?>
        <div class="section-card" style="margin-bottom:24px">
            <div class="section-card-header"><h2><i class="fas fa-tags"></i> Ventes par Catégorie</h2></div>
            <div style="padding:16px">
                <?php foreach ($byCat as $cat): ?>
                <div class="cat-bar-wrap">
                    <div class="cat-bar-label">
                        <span><?php echo h($cat['categorie'] ?: 'Non défini'); ?></span>
                        <span><?php echo number_format($cat['ca'], 0, ',', ' '); ?> DA &nbsp;(<?php echo $cat['qte']; ?> unités)</span>
                    </div>
                    <div class="cat-bar-bg">
                        <div class="cat-bar-fill" style="width:<?php echo round(($cat['ca']/$maxCatCa)*100); ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Daily breakdown -->
        <?php if (!empty($daily)): ?>
        <div class="section-card" style="margin-bottom:24px">
            <div class="section-card-header"><h2><i class="fas fa-calendar-day"></i> Récapitulatif Journalier</h2></div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>Date</th><th>Nombre de ventes</th><th>Chiffre d'affaires (DA)</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($daily as $d): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($d['jour'])); ?> (<?php
                                $days = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
                                echo $days[date('N', strtotime($d['jour'])) - 1];
                            ?>)</td>
                            <td><?php echo $d['nb']; ?></td>
                            <td><strong><?php echo number_format($d['ca'], 2, ',', ' '); ?> DA</strong></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-total">
                            <td><strong>TOTAL</strong></td>
                            <td><strong><?php echo $kpi['nb_ventes']; ?></strong></td>
                            <td><strong><?php echo number_format($kpi['chiffre_affaires'], 2, ',', ' '); ?> DA</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Detailed sales table -->
        <div class="section-card">
            <div class="section-card-header">
                <h2><i class="fas fa-list-alt"></i> Détail des Ventes (<?php echo count($detail); ?>)</h2>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date &amp; Heure</th>
                            <th>Vendeur</th>
                            <th>Patient</th>
                            <th>Médicament</th>
                            <th>Catégorie</th>
                            <th>Qté</th>
                            <th>P.U (DA)</th>
                            <th>Total (DA)</th>
                            <th>Caisse</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($detail)): ?>
                        <tr><td colspan="10" class="text-center text-muted">Aucune vente sur cette période.</td></tr>
                    <?php else: foreach ($detail as $i => $s): ?>
                        <tr>
                            <td><?php echo $i+1; ?></td>
                            <td><?php echo formatDate($s['date_vente']); ?></td>
                            <td><?php echo h($s['vendeur_nom']); ?></td>
                            <td><?php echo h($s['pprenom'] . ' ' . $s['pnom']); ?></td>
                            <td><?php echo h($s['mnom']); ?></td>
                            <td><?php echo h($s['categorie']); ?></td>
                            <td><?php echo $s['quantite']; ?></td>
                            <td><?php echo number_format($s['prix_unitaire'], 2, ',', ' '); ?></td>
                            <td><strong><?php echo number_format($s['prix_total'], 2, ',', ' '); ?></strong></td>
                            <td><?php echo h($s['caisse_nom']); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                    <?php if (!empty($detail)): ?>
                    <tfoot>
                        <tr class="table-total">
                            <td colspan="8"><strong>TOTAL GÉNÉRAL (<?php echo $kpi['nb_ventes']; ?> ventes — <?php echo $kpi['unites_vendues']; ?> unités)</strong></td>
                            <td colspan="2"><strong><?php echo number_format($kpi['chiffre_affaires'], 2, ',', ' '); ?> DA</strong></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- Signature block (print only) -->
        <div class="signature-block">
            <div class="sig-box">
                <div class="sig-title">Établi par</div>
                <div class="sig-line"><?php echo h($_SESSION['user_name']); ?> — Administrateur</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Visé par</div>
                <div class="sig-line">Le Directeur</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Date d'édition</div>
                <div class="sig-line"><?php echo date('d/m/Y'); ?></div>
            </div>
        </div>

    </main>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
<script>
function setPeriod(p) {
    document.getElementById('periodInput').value = p;
    if (p === 'custom') {
        document.getElementById('customRange').style.display = 'flex';
    } else {
        document.getElementById('filterForm').submit();
    }
}
</script>
</body>
</html>
