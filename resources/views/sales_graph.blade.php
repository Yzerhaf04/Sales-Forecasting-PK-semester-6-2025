<!DOCTYPE html>
<html>
<head>
    <title>Grafik Penjualan</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h2>Grafik Penjualan - Store 1 Dept 1</h2>
    <canvas id="salesChart" width="800" height="400"></canvas>

    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($labels) !!},
                datasets: [{
                    label: 'Sales',
                    data: {!! json_encode($values) !!},
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3
                }]
            }
        });
    </script>
</body>
</html>
