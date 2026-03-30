<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole($pdo) === 'guichet') {
    header('Location: dashboard.php');
    exit;
}
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Fournisseurs - Stockly</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stockly.css">
</head>
<body>
    <nav class="navbar"><div class="container">
        <a class="navbar-brand" href="dashboard.php">Stockly</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
            <a class="nav-link" href="inventory.php">Inventaire</a>
            <a class="nav-link active" href="#">Fournisseurs</a>
        </div>
    </div></nav>

    <div class="container my-5">
        <h1><i class="bi bi-truck"></i> Fournisseurs</h1>
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addSupplierModal">+ Ajouter</button>
        
        <div class="table-responsive">
            <table class="table">
<th>Prix unitaire</th>
                <tbody>
                    <?php foreach ($suppliers as $sup): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sup['name']); ?></td>
                        <td><?php echo htmlspecialchars($sup['company_num']); ?></td>
<td><?php echo number_format($sup['prix_unitaire'] ?? 0, 2); ?> FCFA</td>
                        <td><?php echo htmlspecialchars($sup['phone']); ?></td>
                        <td><button class="btn btn-sm btn-danger delete-sup" data-id="<?php echo $sup['id']; ?>">Supprimer</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Supplier Modal -->
    <div class="modal fade" id="addSupplierModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="supplierForm">
                    <div class="modal-header"><h5>Ajouter Fournisseur</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <div class="mb-3"><label>Nom</label><input name="name" class="form-control" required></div>
                        <div class="mb-3"><label>Société #</label><input name="company_num" class="form-control"></div>
<div class="mb-3"><label>Prix unitaire</label><input name="prix_unitaire" type="number" step="0.01" class="form-control"></div>
                        <div class="mb-3"><label>Téléphone</label><input name="phone" class="form-control"></div>
                        <div class="mb-3"><label>Adresse</label><textarea name="address" class="form-control"></textarea></div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="action" value="create">
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('supplierForm').onsubmit = e => {
            e.preventDefault();
            submitForm('api/suppliers.php', e.target);
        };
        document.querySelectorAll('.delete-sup').forEach(btn => {
            btn.onclick = () => {
                if (confirm('Supprimer ce fournisseur?')) {
                    fetch(`api/suppliers.php?action=delete&id=${btn.dataset.id}`).then(() => location.reload());
                }
            };
        });
    </script>
</body>
</html>

