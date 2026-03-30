<?php
require_once 'config.php';
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}
$role = getUserRole($pdo);

// Fetch orders and products
$orders = [];
if ($role === 'guichet') {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE guichet_user_id = ? ORDER BY date_created DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.guichet_user_id = u.id ORDER BY o.date_created DESC LIMIT 50");
    $orders = $stmt->fetchAll();
}

$products = $pdo->query("SELECT * FROM products")->fetchAll();
if ($_POST && $role === 'guichet') {
    // Create order handled by JS fetch to api/orders.php
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commandes - Stockly</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stockly.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Stockly</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Tableau de bord</a>
                <a class="nav-link active" href="#">Commandes</a>
                <a class="nav-link" href="?logout=1">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-file-earmark-text"></i> Bons de Commande</h1>
            <?php if ($role === 'guichet'): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrderModal">+ Nouvelle</button>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Guichet</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['username'] ?? 'N/A'); ?></td>
                        <td><span class="badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['date_created'])); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button onclick="viewOrder(<?php echo $order['id']; ?>)" class="btn btn-sm btn-outline-info">Voir</button>
                                <?php if ($role !== 'guichet'): ?>
                                <?php if ($order['status'] === 'pending'): ?>
                                <button onclick="updateStatus(<?php echo $order['id']; ?>, 'approved')" class="btn btn-sm btn-outline-success">Approuver</button>
                                <?php endif; ?>
                                <?php if ($order['status'] !== 'delivered'): ?>
                                <button onclick="updateStatus(<?php echo $order['id']; ?>, 'delivered')" class="btn btn-sm btn-success">Livrer</button>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Order Modal (Guichet) -->
    <?php if ($role === 'guichet'): ?>
    <div class="modal fade" id="createOrderModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle Commande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="orderForm">
                    <div class="modal-body">
                        <h6>Lignes fournitures <button type="button" class="btn btn-sm btn-success" id="addItemBtn">+ Ajouter</button></h6>
                        <div id="itemsContainer">
                            <!-- Dynamic rows added by JS -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" id="submitOrder">Envoyer Commande</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- View Order Details Modal -->
    <div class="modal fade" id="viewOrderModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails Commande #<span id="viewOrderIdDisplay"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix Unit.</th>
                                <th>Quantité</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="orderDetailsTable">
                            <!-- JS populated -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total Commande:</th>
                                <th id="orderTotalDisplay">0 FCFA</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/charts.js"></script>
    <script>
    <?php if ($role === 'guichet'): ?>
    let itemCount = 0;
    const itemsContainer = document.getElementById('itemsContainer');
    const products = <?php echo json_encode($products); ?>;
    
    function addItemRow() {
        itemCount++;
        const row = document.createElement('div');
        row.className = 'row mb-2 border p-2 rounded item-row';
        row.innerHTML = `
            <div class="col-md-5">
                <select class="form-select product-select" name="product_${itemCount}" required>
                    <option value="">Produit</option>
                    ${products.map(p => `<option value="${p.id}">${p.name} (${p.price} FCFA)</option>`).join('')}
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control qty-input" name="qty_${itemCount}" min="1" required placeholder="Qté">
            </div>
            <div class="col-md-4">
                <button type="button" class="btn btn-outline-danger remove-item" style="width:100%">× Supprimer</button>
            </div>
        `;
        itemsContainer.appendChild(row);
        row.querySelector('.remove-item').onclick = () => row.remove();
    }
    
    document.getElementById('addItemBtn').onclick = addItemRow;
    
    // Add first row
    addItemRow();
    
    document.getElementById('orderForm').onsubmit = function(e) {
        e.preventDefault();
        const items = [];
        document.querySelectorAll('.item-row').forEach(row => {
            const prodId = row.querySelector('.product-select').value;
            const qty = row.querySelector('.qty-input').value;
            if (prodId && qty) items.push({product_id: prodId, quantity: qty});
        });
        if (items.length === 0) return alert('Ajoutez au moins 1 fourniture!');
        
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('items', JSON.stringify(items));
        
        fetch('api/orders.php', {
            method: 'POST',
            body: formData
        }).then(res => res.json()).then(data => {
            if (data.success) {
                alert(`Commande #${data.id} envoyée!`);
                location.reload();
            } else {
                alert('Erreur: ' + data.error);
            }
        });
    };
    <?php endif; ?>

    function viewOrder(orderId) {
        document.getElementById('viewOrderIdDisplay').textContent = orderId;
        const table = document.getElementById('orderDetailsTable');
        table.innerHTML = '<tr><td colspan="4" class="text-center">Chargement...</td></tr>';
        
        const modal = new bootstrap.Modal(document.getElementById('viewOrderModal'));
        modal.show();
        
        fetch(`api/orders.php?action=view&id=${orderId}`)
            .then(res => res.json())
            .then(items => {
                table.innerHTML = '';
                let total = 0;
                items.forEach(item => {
                    const subtotal = item.price * item.quantity;
                    total += subtotal;
                    table.innerHTML += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${item.price} FCFA</td>
                            <td>${item.quantity}</td>
                            <td>${subtotal} FCFA</td>
                        </tr>
                    `;
                });
                document.getElementById('orderTotalDisplay').textContent = total + ' FCFA';
            });
    }
    </script>
</body>
</html>

