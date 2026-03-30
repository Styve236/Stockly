<?php
header('Content-Type: application/json');
require_once '../config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non connecté']);
    exit;
}

$role = getUserRole($pdo);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        $where = $role === 'guichet' ? 'WHERE o.guichet_user_id = ' . $_SESSION['user_id'] : '';
        $stmt = $pdo->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.guichet_user_id = u.id $where ORDER BY o.date_created DESC");
        echo json_encode($stmt->fetchAll());
        break;

    case 'create':
        if ($role !== 'guichet') {
            echo json_encode(['error' => 'Accès refusé']);
            break;
        }
        $items_json = $_POST['items'] ?? '';
        $items = json_decode($items_json, true);
        if (empty($items) || !is_array($items)) {
            echo json_encode(['error' => 'Items requis']);
            break;
        }
        $stmt = $pdo->prepare("INSERT INTO orders (guichet_user_id) VALUES (?)");
        $stmt->execute([$_SESSION['user_id']]);
        $order_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
        foreach ($items as $item) {
            $stmt->execute([$order_id, (int)$item['product_id'], (int)$item['quantity']]);
        }
        echo json_encode(['success' => true, 'id' => $order_id]);
        break;

    case 'update_status':
        if ($role === 'guichet') {
            echo json_encode(['error' => 'Accès refusé']);
            break;
        }
        $id = (int)$_GET['id'];
        $status = $_GET['status'];
        if (!in_array($status, ['pending', 'approved', 'delivered'])) {
            echo json_encode(['error' => 'Statut invalide']);
            break;
        }
        // Update stock if delivered
        if ($status === 'delivered') {
            $stmt = $pdo->prepare("
                INSERT INTO stock (product_id, current_stock) 
                SELECT product_id, SUM(quantity) FROM order_items oi 
                JOIN orders o ON oi.order_id = o.id 
                WHERE o.id = ? GROUP BY product_id
                ON DUPLICATE KEY UPDATE current_stock = current_stock + VALUES(current_stock)
            ");
            $stmt->execute([$id]);
        }
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, date_updated = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(['success' => true]);
        break;

    case 'view':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT oi.*, p.name as product_name, p.price 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetchAll());
        break;

    default:
        echo json_encode(['error' => 'Action inconnue']);
}
?>

