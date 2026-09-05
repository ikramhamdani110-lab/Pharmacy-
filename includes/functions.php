<?php

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function h($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function getSiteProfile($conn) {
    $create = $conn->query("CREATE TABLE IF NOT EXISTS site_profile (
        id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
        full_name VARCHAR(150) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        email VARCHAR(150) NOT NULL,
        location VARCHAR(200) NOT NULL,
        bio VARCHAR(500) NOT NULL,
        photo_path VARCHAR(255) DEFAULT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    if (!$create) {
        error_log('PharmaSante site profile table setup failed: ' . $conn->error);
        return array(
            'full_name' => 'PharmaSanté',
            'phone' => '+213 550 000 000',
            'email' => 'contact@pharmacare.dz',
            'location' => '123 Rue de la Santé, Alger, Algérie',
            'bio' => 'Votre santé, notre priorité.',
            'photo_path' => null
        );
    }

    $result = $conn->query("SELECT * FROM site_profile WHERE id=1");
    if ($result && ($row = $result->fetch_assoc())) {
        return $row;
    }

    $defaults = array(
        'full_name' => 'PharmaSanté',
        'phone' => '+213 550 000 000',
        'email' => 'contact@pharmacare.dz',
        'location' => '123 Rue de la Santé, Alger, Algérie',
        'bio' => 'Votre santé, notre priorité.',
        'photo_path' => null
    );
    $stmt = $conn->prepare("INSERT INTO site_profile (id, full_name, phone, email, location, bio) VALUES (1, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssss", $defaults['full_name'], $defaults['phone'], $defaults['email'], $defaults['location'], $defaults['bio']);
        $stmt->execute();
    }
    return $defaults;
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
