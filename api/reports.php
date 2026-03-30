<?php
header('Content-Type: application/json');
require_once '../config.php';

if (!isLoggedIn() || getUserRole($pdo) === 'guichet') {
    http_response_code(403);
    exit('Accès refusé');
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$type = $_GET['type'] ?? 'orders';  // orders or inventory

if ($action === 'generate' && $type === 'excel') {
    // Simple CSV export as Excel alternative (PhpSpreadsheet later)
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="stockly_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    if ($type === 'orders') {
        fputcsv($output, ['ID', 'Guichet', 'Statut', 'Date', 'Notes']);
        $stmt = $pdo->query("SELECT o.id, u.username, o.status, o.date_created, o.notes FROM orders o JOIN users u ON o.guichet_user_id = u.id");
        while ($row = $stmt->fetch()) {
            fputcsv($output, $row);
        }
    } else {
        fputcsv($output, ['Produit', 'Stock', 'Seuil', 'Fournisseur']);
        $stmt = $pdo->query("SELECT p.name, COALESCE(s.current_stock,0), p.threshold, sup.name FROM products p LEFT JOIN stock s ON p.id=s.product_id LEFT JOIN suppliers sup ON p.supplier_id=sup.id");
        while ($row = $stmt->fetch()) {
            fputcsv($output, $row);
        }
    }
    exit;
}

if ($action === 'data') {
    $data = ['weekly' => [], 'monthly' => []];
    // Already handled in dashboard, return summary
    $stmt = $pdo->query("SELECT status, COUNT(*) cnt FROM orders WHERE YEARWEEK(date_created) = YEARWEEK(CURDATE()) GROUP BY status");
    while ($row = $stmt->fetch()) $data['weekly'][$row['status']] = $row['cnt'];
    echo json_encode($data);
}
?>

