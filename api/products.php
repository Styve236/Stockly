<?php
header('Content-Type: application/json');
require_once '../config.php';

if (!isLoggedIn() || getUserRole($pdo) === 'guichet') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        $stmt = $pdo->query("
            SELECT p.*, s.name as supplier_name, st.current_stock 
            FROM products p 
            LEFT JOIN suppliers s ON p.supplier_id = s.id 
            LEFT JOIN stock st ON p.id = st.product_id
        ");
        echo json_encode($stmt->fetchAll());
        break;

    case 'create':
        $name = $_POST['name'];
        $price = (float)$_POST['price'];
        $supplier_id = $_POST['supplier_id'] ?? null;
        $threshold = (int)$_POST['threshold'];
        
        $stmt = $pdo->prepare("INSERT INTO products (name, price, supplier_id, threshold) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $price, $supplier_id, $threshold]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'update':
        $id = (int)$_POST['id'];
        $name = $_POST['name'];
        $price = (float)$_POST['price'];
        $supplier_id = $_POST['supplier_id'] ?? null;
        $threshold = (int)$_POST['threshold'];
        
        $stmt = $pdo->prepare("UPDATE products SET name=?, price=?, supplier_id=?, threshold=? WHERE id=?");
        $stmt->execute([$name, $price, $supplier_id, $threshold, $id]);
        echo json_encode(['success' => true]);
        break;

    case 'delete':
        $id = (int)$_GET['id'];
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Action inconnue']);
}
?>

