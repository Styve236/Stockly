<?php
require_once '../config.php';
if (getUserRole($pdo) !== 'admin') {
    header('Location: ../dashboard.php');
    exit;
}
$users = $pdo->query("SELECT id, username, role, email, created_at FROM users WHERE id > 1 ORDER BY created_at DESC")->fetchAll();  // Exclude default admin
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Utilisateurs - Stockly Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/stockly.css">
</head>
<body>
    <nav class="navbar"><div class="container">
        <a class="navbar-brand" href="../dashboard.php">Stockly Admin</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="../dashboard.php">Dashboard</a>
            <a class="nav-link active" href="#">Utilisateurs</a>
        </div>
    </div></nav>

    <div class="container my-5">
        <h1><i class="bi bi-people"></i> Gestion Utilisateurs</h1>
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#userModal">+ Ajouter Utilisateur</button>
        
        <table class="table table-hover">
            <thead><tr><th>Utilisateur</th><th>Rôle</th><th>Email</th><th>Créé le</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'stock' ? 'success' : 'info'); ?>">
                        <?php echo ucfirst($user['role']); ?>
                    </span></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary edit-user me-1" data-id="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>" data-role="<?php echo $user['role']; ?>" data-email="<?php echo htmlspecialchars($user['email']); ?>">Éditer</button>
                        <button class="btn btn-sm btn-danger delete-user" data-id="<?php echo $user['id']; ?>">Supprimer</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- User Modal (Add/Edit) -->
    <div class="modal fade" id="userModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="userForm">
                    <div class="modal-header">
                        <h5 id="modalTitle" class="modal-title">Ajouter Utilisateur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="userId">
                        <div class="mb-3"><label>Nom utilisateur</label><input name="username" id="username" class="form-control" required></div>
                        <div class="mb-3"><label>Mot de passe <small>(laisser vide pour garder ancien)</small></label><input name="password" type="password" id="password" class="form-control"></div>
                        <div class="mb-3"><label>Rôle</label>
                            <select name="role" id="role" class="form-select">
                                <option value="guichet">Guichet</option>
                                <option value="stock">Stock</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3"><label>Email</label><input name="email" id="email" type="email" class="form-control"></div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Sauvegarder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/charts.js"></script>
    <script>
        let editUserId = null;
        const userModal = new bootstrap.Modal(document.getElementById('userModal'));
        const userForm = document.getElementById('userForm');
        
        // Edit buttons
        document.querySelectorAll('.edit-user').forEach(btn => {
            btn.addEventListener('click', () => {
                editUserId = btn.dataset.id;
                document.getElementById('userId').value = btn.dataset.id;
                document.getElementById('username').value = btn.dataset.username;
                document.getElementById('role').value = btn.dataset.role;
                document.getElementById('email').value = btn.dataset.email;
                document.getElementById('password').value = '';
                document.getElementById('formAction').value = 'update';
                document.getElementById('modalTitle').textContent = 'Modifier Utilisateur';
                userModal.show();
            });
        });
        
        // Add button
        document.querySelector('[data-bs-target="#userModal"]').onclick = () => {
            editUserId = null;
            userForm.reset();
            document.getElementById('formAction').value = 'create';
            document.getElementById('modalTitle').textContent = 'Ajouter Utilisateur';
        };
        
        userForm.onsubmit = e => {
            e.preventDefault();
            submitForm('../api/users.php', userForm);
            userModal.hide();
        };
        
        document.querySelectorAll('.delete-user').forEach(btn => {
            btn.onclick = () => {
                if (confirm('Supprimer cet utilisateur?')) {
                    fetch(`../api/users.php?action=delete&id=${btn.dataset.id}`).then(() => location.reload());
                }
            };
        });
    </script>
</body>
</html>

