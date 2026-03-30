<?php
header('Content-Type: application/json');
require_once '../config.php';

if (!isLoggedIn() || getUserRole($pdo) === 'guichet') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        $stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name");
        echo json_encode($stmt->fetchAll());
        break;

    case 'create':
        $data = [
            $_POST['name'], 
            $_POST['company_num'] ?? '', 
            (float)($_POST['prix_unitaire'] ?? 0), 
            $_POST['phone'] ?? '', 
            $_POST['address'] ?? ''
        ];
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, company_num, prix_unitaire, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute($data);
        echo json_encode(['success' => true]);
        break;

    case 'delete':
        $id = (int)$_GET['id'];
        $pdo->prepare("DELETE FROM suppliers WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Action inconnue']);
}
?>

