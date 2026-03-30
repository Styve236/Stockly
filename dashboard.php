<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$role = getUserRole($pdo);
$user_id = $_SESSION['user_id'];

// Fetch data for dashboard
$stats = [];
$low_stock = $pdo->query("
    SELECT p.name, s.current_stock, p.threshold 
    FROM stock s JOIN products p ON s.product_id = p.id 
    WHERE s.current_stock <= p.threshold
")->fetchAll();

if ($role === 'guichet') {
    $stats['pending_orders'] = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE guichet_user_id = ? AND status = 'pending'");
    $stats['pending_orders']->execute([$user_id]);
    $stats['pending_orders'] = $stats['pending_orders']->fetchColumn();
} else {
    $stats_query = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM orders WHERE WEEK(date_created) = WEEK(CURDATE()) 
        GROUP BY status
    ");
    $weekly = ['pending' => 0, 'approved' => 0, 'delivered' => 0];
    while ($row = $stats_query->fetch()) {
        $weekly[$row['status']] = $row['count'];
    }
    $stats['weekly'] = $weekly;
    
    $monthly_query = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM orders WHERE MONTH(date_created) = MONTH(CURDATE()) 
        GROUP BY status
    ");
    $monthly = [0, 0, 0]; // pending, approved, delivered
    while ($row = $monthly_query->fetch()) {
        $idx = ['pending'=>0, 'approved'=>1, 'delivered'=>2][$row['status']] ?? 0;
        $monthly[$idx] = $row['count'];
    }
    $stats['monthly'] = $monthly;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stockly Dashboard - <?php echo ucfirst($role); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stockly.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Stockly <small class="text-white-50">FIGEC</small></a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-white"><?php echo htmlspecialchars($role); ?> | Bonjour <?php echo $_SESSION['username'] ?? ''; ?></span>
                <a class="nav-link text-white" href="?logout=1"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h1 class="mb-4"><i class="bi bi-house-door"></i> Tableau de Bord (<?php echo ucfirst($role); ?>)</h1>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <?php if ($role === 'guichet'): ?>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2><?php echo $stats['pending_orders']; ?></h2>
                            <p>Commandes en attente</p>
                            <a href="orders.php" class="btn btn-primary">Nouvelle commande</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-md-3"><div class="card text-center"><div class="card-body"><h3><?php echo $stats['weekly']['pending'] ?? 0; ?></h3><p>En attente (Hebdo)</p></div></div></div>
                <div class="col-md-3"><div class="card text-center"><div class="card-body"><h3><?php echo $stats['weekly']['approved'] ?? 0; ?></h3><p>Approuvées (Hebdo)</p></div></div></div>
                <div class="col-md-3"><div class="card text-center"><div class="card-body"><h3><?php echo $stats['weekly']['delivered'] ?? 0; ?></h3><p>Livrées (Hebdo)</p></div></div></div>
                <div class="col-md-3"><div class="card text-center"><div class="card-body"><h3><?php echo array_sum($stats['weekly'] ?? []); ?></h3><p>Total Hebdo</p></div></div></div>
            <?php endif; ?>
        </div>

        <!-- Charts (Manager/Admin) -->
        <?php if ($role !== 'guichet'): ?>
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Résumé Hebdomadaire</div>
                    <div class="card-body">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Résumé Mensuel</div>
                    <div class="card-body">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-file-earmark-text fs-1 text-primary mb-3"></i>
                        <h5>Commandes</h5>
                        <a href="orders.php" class="btn btn-outline-primary">Voir / Créer</a>
                    </div>
                </div>
            </div>
            <?php if ($role !== 'guichet'): ?>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-box-seam fs-1 text-success mb-3"></i>
                        <h5>Inventaire</h5>
                        <a href="inventory.php" class="btn btn-outline-success">Gérer</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-truck fs-1 text-info mb-3"></i>
                        <h5>Fournisseurs</h5>
                        <a href="suppliers.php" class="btn btn-outline-info">Gérer</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-file-earmark-spreadsheet fs-1 text-warning mb-3"></i>
                        <h5>Rapports</h5>
                        <a href="reports.php" class="btn btn-outline-warning">Générer</a>
                    </div>
                </div>
            </div>
            <?php if ($role === 'admin'): ?>
            <div class="col-md-3 mt-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-people fs-1 text-danger mb-3"></i>
                        <h5>Utilisateurs</h5>
                        <a href="admin/users.php" class="btn btn-outline-danger">Gérer</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <!-- Low Stock Alert -->
            <?php if ($role !== 'guichet' && !empty($low_stock)): ?>
            <div class="col-12 mt-4">
                <div class="alert alert-low-stock">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Alertes Stock Bas:</strong>
                    <?php foreach ($low_stock as $item): ?>
                        <?php echo htmlspecialchars($item['name']) . ' (' . $item['current_stock'] . '/' . $item['threshold'] . ')'; ?><?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/charts.js"></script>
    <script>
        <?php if ($role !== 'guichet'): ?>
        initCharts(<?php echo json_encode($stats['weekly'] ?? []); ?>, <?php echo json_encode($stats['monthly'] ?? []); ?>);
        <?php endif; ?>
        <?php if ($role !== 'guichet' && !empty($low_stock)): ?>
        checkStockAlerts(<?php echo json_encode($low_stock); ?>);
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

