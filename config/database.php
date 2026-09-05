<?php
$host = getenv('PHARMA_DB_HOST') ?: 'localhost';
$user = getenv('PHARMA_DB_USER') ?: 'root';
$password = getenv('PHARMA_DB_PASSWORD') ?: '';
$database = getenv('PHARMA_DB_NAME') ?: 'pharmacare_db';
$productionConfig = __DIR__ . '/database.production.php';
if (is_file($productionConfig)) {
    require $productionConfig;
    $password = $productionDbPassword;
    $host = getenv('PHARMA_DB_HOST') ?: 'sql201.infinityfree.com';
    $user = getenv('PHARMA_DB_USER') ?: 'if0_42835805';
    $database = getenv('PHARMA_DB_NAME') ?: 'pharmasante';
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    error_log('PharmaSante database connection failed: ' . $conn->connect_error);
    http_response_code(503);
    die('<div style="font-family:sans-serif;padding:40px;background:#fff3f3;border:2px solid #D32F2F;border-radius:8px;max-width:600px;margin:40px auto;"><h2 style="color:#D32F2F;">&#x2717; Service temporairement indisponible</h2><p>La connexion à la base de données est indisponible. Vérifiez la configuration du serveur.</p></div>');
}
$conn->set_charset("utf8mb4");
?>
