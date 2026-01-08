<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location:../index.php');
    exit();
}
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <span>Logistic Tracking - Reports</span>
    </div>

    <h1 class="text-3xl font-bold text-gray-800 mb-6">Logistic Reports</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-bold text-gray-800">Total Projects</h3>
            <p id="totalProjects" class="text-3xl font-bold text-indigo-600">-</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-bold text-gray-800">Active Projects</h3>
            <p id="activeProjects" class="text-3xl font-bold text-green-600">-</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-bold text-gray-800">Total Requests</h3>
            <p id="totalRequests" class="text-3xl font-bold text-blue-600">-</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-bold text-gray-800">Total Contracts</h3>
            <p id="totalContracts" class="text-3xl font-bold text-purple-600">-</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Project Status Distribution</h2>
            <canvas id="projectStatusChart"></canvas>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Request Status Distribution</h2>
            <canvas id="requestStatusChart"></canvas>
        </div>
    </div>

    <div class="mt-8 bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4">Recent Activity</h2>
        <div id="recentActivity" class="space-y-2">
            <!-- Recent activity will be populated here -->
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    let projectStatusChart, requestStatusChart;

    async function fetchReports() {
        try {
            const res = await fetch('../api/logistic_reports.php');
            const data = await res.json();
            if (data.status === 'success') {
                updateStats(data.stats);
                renderCharts(data.stats);
                renderRecentActivity(data.recent);
            }
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error loading reports', duration: 3000, backgroundColor: '#ef4444' }).showToast();
        }
    }

    function updateStats(stats) {
        document.getElementById('totalProjects').textContent = stats.total_projects;
        document.getElementById('activeProjects').textContent = stats.active_projects;
        document.getElementById('totalRequests').textContent = stats.total_requests;
        document.getElementById('totalContracts').textContent = stats.total_contracts;
    }

    function renderCharts(stats) {
        // Project Status Chart
        const projectCtx = document.getElementById('projectStatusChart').getContext('2d');
        if (projectStatusChart) projectStatusChart.destroy();
        projectStatusChart = new Chart(projectCtx, {
            type: 'pie',
            data: {
                labels: ['Planning', 'In Progress', 'Completed', 'Cancelled'],
                datasets: [{
                    data: [stats.project_planning, stats.project_in_progress, stats.project_completed, stats.project_cancelled],
                    backgroundColor: ['#fbbf24', '#3b82f6', '#10b981', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Request Status Chart
        const requestCtx = document.getElementById('requestStatusChart').getContext('2d');
        if (requestStatusChart) requestStatusChart.destroy();
        requestStatusChart = new Chart(requestCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Approved', 'Rejected'],
                datasets: [{
                    data: [stats.request_pending, stats.request_approved, stats.request_rejected],
                    backgroundColor: ['#fbbf24', '#10b981', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    function renderRecentActivity(recent) {
        const container = document.getElementById('recentActivity');
        container.innerHTML = recent.map(item => `
            <div class="flex items-center space-x-4 p-3 border rounded">
                <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                    <span class="text-indigo-600 font-bold text-sm">${item.type.charAt(0).toUpperCase()}</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium">${item.title}</p>
                    <p class="text-xs text-gray-500">${item.date}</p>
                </div>
                <span class="px-2 py-1 text-xs rounded ${item.status === 'Completed' || item.status === 'Approved' ? 'bg-green-100 text-green-800' : item.status === 'InProgress' || item.status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100'}">${item.status}</span>
            </div>
        `).join('');
    }

    fetchReports();
});
</script>
HTML;
adminLayout($children);
?>