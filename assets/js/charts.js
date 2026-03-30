// Stockly Charts & Utils - Chart.js + Alerts
// Include Chart.js CDN in HTML: <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

let weeklyChart, monthlyChart;

function initCharts(weeklyData, monthlyData) {
    const ctxWeekly = document.getElementById('weeklyChart').getContext('2d');
    weeklyChart = new Chart(ctxWeekly, {
        type: 'bar',
        data: {
            labels: weeklyData.labels,
            datasets: [{
                label: 'Pending',
                data: weeklyData.pending,
                backgroundColor: '#ffc107'
            }, {
                label: 'Approved',
                data: weeklyData.approved,
                backgroundColor: '#007bff'
            }, {
                label: 'Delivered',
                data: weeklyData.delivered,
                backgroundColor: '#28a745'
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    const ctxMonthly = document.getElementById('monthlyChart').getContext('2d');
    monthlyChart = new Chart(ctxMonthly, {
        type: 'pie',
        data: {
            labels: ['Pending', 'Approved', 'Delivered'],
            datasets: [{
                data: monthlyData,
                backgroundColor: ['#ffc107', '#007bff', '#28a745']
            }]
        },
        options: { responsive: true }
    });
}

function checkStockAlerts(stockData) {
    stockData.forEach(item => {
        if (item.current_stock <= item.threshold) {
            const row = document.querySelector(`[data-product-id="${item.product_id}"]`);
            if (row) row.classList.add('low-stock');
        }
    });
    if (stockData.some(item => item.current_stock <= item.threshold)) {
        alert('⚠️ Alertes stock bas! Vérifiez les produits en seuil.');
    }
}

function updateStatus(orderId, newStatus) {
    fetch(`api/orders.php?action=update_status&id=${orderId}&status=${newStatus}`, {
        method: 'POST'
    })
    .then(() => {
        alert(`Commande #${orderId} mise à jour: ${newStatus}`);
        location.reload();
    });
}

// Utils for AJAX forms
function submitForm(url, form) {
    const formData = new FormData(form);
    fetch(url, {
        method: 'POST',
        body: formData
    }).then(res => res.json()).then(data => {
        if (data.success) {
            alert('Opération réussie!');
            location.reload();
        } else {
            alert('Erreur: ' + (data.error || 'Action inconnue'));
        }
    }).catch(err => alert('Erreur réseau: ' + err));
}

