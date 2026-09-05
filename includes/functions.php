<?php

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function h($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || !is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die("Requête refusée. Veuillez recharger la page.");
    }
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . h(generateCsrfToken()) . '">';
}

function verifyPostCsrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrfToken($_POST['csrf_token'] ?? '');
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if (is_array($role)) {
        if (!in_array($_SESSION['user_role'], $role)) {
            header("Location: login.php");
            exit;
        }
    } else {
        if ($_SESSION['user_role'] !== $role) {
            header("Location: login.php");
            exit;
        }
    }
}

function requireAdmin() {
    requireRole('admin');
}

function requireVendeur() {
    requireRole(array('admin', 'vendeur'));
}

function requirePatient() {
    requireRole('patient');
}

function formatDate($datetime) {
    if (!$datetime || $datetime === '0000-00-00 00:00:00') return '-';
    $ts = strtotime($datetime);
    return date('d/m/Y H:i', $ts);
}

function formatDateOnly($date) {
    if (!$date || $date === '0000-00-00') return '-';
    $ts = strtotime($date);
    return date('d/m/Y', $ts);
}

function formatMoney($amount) {
    return number_format($amount, 2, ',', ' ') . ' DA';
}

function getStockBadge($qty) {
    if ($qty == 0) {
        return '<span class="badge badge-danger">Épuisé</span>';
    } elseif ($qty < 10) {
        return '<span class="badge badge-warning">Stock Faible</span>';
    } else {
        return '<span class="badge badge-success">En Stock</span>';
    }
}

function getOpenCaisseForVendeur($conn, $vendeur_id) {
    $stmt = $conn->prepare("SELECT * FROM caisse WHERE vendeur_id = ? AND statut = 'ouverte' LIMIT 1");
    $stmt->bind_param("i", $vendeur_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function truncate($text, $length = 100) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

function alert($type, $message) {
    $icon = '';
    switch ($type) {
        case 'success': $icon = '<i class="fas fa-check-circle"></i>'; break;
        case 'danger':  $icon = '<i class="fas fa-exclamation-circle"></i>'; break;
        case 'warning': $icon = '<i class="fas fa-exclamation-triangle"></i>'; break;
        default:        $icon = '<i class="fas fa-info-circle"></i>'; break;
    }
    return '<div class="alert alert-' . $type . '">' . $icon . ' ' . h($message) . '</div>';
}

verifyPostCsrf();
?>
