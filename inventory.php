<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole($pdo) === 'guichet') {
    header('Location: dashboard.php');
    exit;
}

$products = $pdo->query("
    SELECT p.*, s.name as supplier_name, COALESCE(st.current_stock, 0) as current_stock, 
    ROUND(p.price * COALESCE(st.current_stock, 0), 2) as total_value
    FROM products p 
    LEFT JOIN suppliers s ON p.supplier_id = s.id 
    LEFT JOIN stock st ON p.id = st.product_id
")->fetchAll();

$total_stock_value = $pdo->query("SELECT ROUND(SUM(p.price * COALESCE(st.current_stock, 0)), 2) as total FROM products p LEFT JOIN stock st ON p.id = st.product_id")->fetchColumn() ?? 0;

$suppliers = $pdo->query("SELECT * FROM suppliers")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Inventaire - Stockly</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stockly.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg"><div class="container">
        <a class="navbar-brand" href="dashboard.php">Stockly</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
            <a class="nav-link" href="orders.php">Commandes</a>
            <a class="nav-link active" href="#">Inventaire</a>
            <a class="nav-link" href="suppliers.php">Fournisseurs</a>
        </div>
    </div></nav>

    <div class="container my-5">
        <h1><i class="bi bi-box-seam"></i> Inventaire & Produits <small class="text-muted">Total stock: <?php echo number_format($total_stock_value, 2); ?> FCFA</small></h1>
        
        <!-- Add Product Modal Trigger -->
        <div class="mb-4"><button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">+ Ajouter Produit</button></div>

        <div class="table-responsive">
            <table class="table table-hover">
<thead><tr><th>Produit</th><th>Prix unitaire</th><th>Fournisseur</th><th>Stock</th><th>Valeur</th><th>Seuil</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($products as $prod): ?>
                    <tr data-product-id="<?php echo $prod['id']; ?>">
                        <td><?php echo htmlspecialchars($prod['name']); ?></td>
<td><?php echo number_format($prod['price'], 2); ?> FCFA</td>
                        <td><?php echo htmlspecialchars($prod['supplier_name'] ?? 'N/A'); ?></td>
                        <td><strong><?php echo $prod['current_stock']; ?></strong></td>
                        <td><?php echo number_format($prod['total_value'], 2); ?> FCFA</td>
                        <td><?php echo $prod['threshold']; ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-prod" data-id="<?php echo $prod['id']; ?>">Éditer</button>
                            <button class="btn btn-sm btn-outline-danger delete-prod" data-id="<?php echo $prod['id']; ?>">Suppr</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="productForm">
                    <div class="modal-header">
                        <h5>Ajouter Produit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3"><label>Nom</label><input name="name" class="form-control" required></div>
                        <div class="mb-3"><label>Prix</label><input name="price" type="number" step="0.01" class="form-control" required></div>
                        <div class="mb-3"><label>Fournisseur</label>
                            <select name="supplier_id" class="form-select">
                                <option value="">Choisir un fournisseur</option>
                                <?php foreach ($suppliers as $s): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3"><label>Seuil alerte</label><input name="threshold" type="number" class="form-control" value="10"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editForm">
                    <div class="modal-header">
                        <h5>Éditer Produit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id">
                        <div class="mb-3"><label>Nom</label><input name="name" class="form-control" required></div>
                        <div class="mb-3"><label>Prix</label><input name="price" type="number" step="0.01" class="form-control" required></div>
                        <div class="mb-3"><label>Fournisseur</label>
                            <select name="supplier_id" id="editSupplier" class="form-select">
                                <option value="">Choisir un fournisseur</option>
                                <?php foreach ($suppliers as $s): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3"><label>Seuil alerte</label><input name="threshold" type="number" class="form-control" required></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Sauvegarder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/charts.js"></script>
    <script>
        let editModal = new bootstrap.Modal(document.getElementById('editProductModal'), {});
        document.getElementById('productForm').onsubmit = e => {
            e.preventDefault();
            submitForm('api/products.php', e.target);
        };
        document.getElementById('editForm').onsubmit = e => {
            e.preventDefault();
            submitForm('api/products.php', e.target);
        };
        document.querySelectorAll('.delete-prod').forEach(btn => {
            btn.onclick = () => {
                if (confirm('Supprimer?')) {
                    fetch(`api/products.php?action=delete&id=${btn.dataset.id}`).then(() => location.reload());
                }
            };
        });
        document.querySelectorAll('.edit-prod').forEach(btn => {
            btn.onclick = () => {
                const id = btn.dataset.id;
                const row = btn.closest('tr');
                document.querySelector('#editForm [name=id]').value = id;
                document.querySelector('#editForm [name=name]').value = row.cells[0].textContent;
                document.querySelector('#editForm [name=price]').value = row.cells[1].textContent.replace(' FCFA', '').replace(',', '');
                const supplierName = row.cells[2].textContent;
                const supplierOption = [...document.querySelectorAll('#editSupplier option')].find(o => o.text === supplierName);
                if (supplierOption) document.getElementById('editSupplier').value = supplierOption.value;
                document.querySelector('#editForm [name=threshold]').value = row.cells[5].textContent;
                editModal.show();
            };
        });
        checkStockAlerts(<?php echo json_encode($products); ?>);
    </script>
</body>
</html>

