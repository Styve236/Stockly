<?php
header('Content-Type: application/json');
require_once '../config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non connecté']);
    exit;
}
if (getUserRole($pdo) !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès admin seulement']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        $stmt = $pdo->query("SELECT id, username, role, email, created_at FROM users ORDER BY created_at DESC");
        echo json_encode($stmt->fetchAll());
        break;

    case 'create':
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $email = $_POST['email'] ?? '';
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, $role, $email]);
        echo json_encode(['success' => true]);
        break;

    case 'delete':
        $id = (int)$_GET['id'];
        if ($id === 1) { // Protect first admin
            echo json_encode(['error' => 'Admin par défaut protégé']);
            break;
        }
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    case 'update':
        $id = (int)$_POST['id'];
        $username = $_POST['username'];
        $role = $_POST['role'];
        $email = $_POST['email'] ?? '';
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        
        $sql = "UPDATE users SET username = ?, role = ?, email = ?";
        $params = [$username, $role, $email];
        
        if ($password) {
            $sql .= ", password = ?";
            $params[] = $password;
        }
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['error' => 'Action inconnue']);
}
?>

