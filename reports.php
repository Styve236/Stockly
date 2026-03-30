<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole($pdo) === 'guichet') {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Rapports - Stockly</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stockly.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
    <nav class="navbar"><div class="container">
        <a class="navbar-brand" href="dashboard.php">Stockly</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
            <a class="nav-link active" href="#">Rapports</a>
        </div>
    </div></nav>

    <div class="container my-5">
        <h1><i class="bi bi-file-earmark-spreadsheet"></i> Rapports</h1>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5>Commandes</h5>
                        <a href="api/reports.php?action=generate&type=orders" class="btn btn-success mb-2"><i class="bi bi-download"></i> Excel</a>
                        <button onclick="generatePDF('orders')" class="btn btn-primary">PDF</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5>Inventaire</h5>
                        <a href="api/reports.php?action=generate&type=inventory" class="btn btn-success mb-2"><i class="bi bi-download"></i> Excel</a>
                        <button onclick="generatePDF('inventory')" class="btn btn-primary">PDF</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function generatePDF(type) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.setFontSize(20);
        doc.text(`Rapport ${type === 'orders' ? 'Commandes' : 'Inventaire'} - Stockly FIGEC`, 20, 20);
        doc.setFontSize(12);
        doc.text(`Généré le: ${new Date().toLocaleDateString('fr-FR')}`, 20, 30);
        
        fetch(`api/reports.php?action=data&type=${type}`)
        .then(res => res.json())
        .then(data => {
            let y = 50;
            if (type === 'orders') {
                data.forEach(row => {
                    doc.text(`#${row.id} - ${row.status} - ${row.date_created}`, 20, y);
                    y += 10;
                });
            } else {
                data.forEach(row => {
                    doc.text(`${row.name}: Stock ${row.current_stock}/${row.threshold}`, 20, y);
                    y += 10;
                });
            }
            doc.save(`stockly_${type}_${Date.now()}.pdf`);
        });
    }
    </script>
</body>
</html>

