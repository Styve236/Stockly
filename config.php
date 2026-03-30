<?php
// Stockly Configuration - DB Connection for WAMP
// Update credentials if needed (default WAMP: root/no pass)

define('DB_HOST', 'localhost');
define('DB_NAME', 'stockly');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: Check login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: Get current user role
function getUserRole($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchColumn();
}
?>

