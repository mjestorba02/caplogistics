<?php
session_start(); 

if (!isset($_SESSION['id'])) {
    header('Location:http://localhost/hr1-ecommerce');
    exit();
}
include '../layout/adminLayout.php';

$children = '
<div class="p-6">

    <!-- Breadcrumb -->
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Logistics Dashboard</a> &gt;
        <span>Warehouse Analytics</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Warehouse Analytics</h1>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-gray-500 text-sm mb-2">Total Stock Items</h2>
            <p class="text-2xl font-bold text-indigo-600">320</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-gray-500 text-sm mb-2">Low Stock Alerts</h2>
            <p class="text-2xl font-bold text-red-600">12</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-gray-500 text-sm mb-2">Incoming Shipments</h2>
            <p class="text-2xl font-bold text-green-600">18</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-gray-500 text-sm mb-2">Outgoing Shipments</h2>
            <p class="text-2xl font-bold text-yellow-500">25</p>
        </div>

    </div>

    <!-- Stock Trends Chart -->
    <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
        <h2 class="text-xl font-semibold mb-4">Stock Movement Trends</h2>
        <canvas id="stockChart" class="w-full h-64"></canvas>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById("stockChart").getContext("2d");
const stockChart = new Chart(ctx, {
    type: "line",
    data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug"],
        datasets: [
            {
                label: "Incoming Stock",
                data: [50, 70, 60, 90, 120, 110, 130, 150],
                borderColor: "rgba(34, 197, 94, 1)",
                backgroundColor: "rgba(34, 197, 94, 0.2)",
                tension: 0.3
            },
            {
                label: "Outgoing Stock",
                data: [30, 50, 40, 70, 100, 90, 110, 120],
                borderColor: "rgba(234, 179, 8, 1)",
                backgroundColor: "rgba(234, 179, 8, 0.2)",
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: "top"
            },
            tooltip: {
                mode: "index",
                intersect: false
            }
        },
        interaction: {
            mode: "nearest",
            axis: "x",
            intersect: false
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
';

adminLayout($children);
?>
