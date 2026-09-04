<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'pharmacare_db';
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;background:#fff3f3;border:2px solid #D32F2F;border-radius:8px;max-width:600px;margin:40px auto;"><h2 style="color:#D32F2F;">&#x2717; Connexion impossible</h2><p>Vérifiez que XAMPP est démarré et MySQL est actif.</p><p style="color:#999;">' . $conn->connect_error . '</p></div>');
}
$conn->set_charset("utf8mb4");
?>
