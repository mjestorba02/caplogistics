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
        <span>Logistic Tracking - Project Status</span>
    </div>

    <h1 class="text-3xl font-bold text-gray-800 mb-6">Project Status</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="statusCards">
        <!-- Status cards will be populated here -->
    </div>

    <div class="mt-8">
        <h2 class="text-2xl font-bold mb-4">Project Timeline</h2>
        <div id="timeline" class="space-y-4">
            <!-- Timeline items will be populated here -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    async function fetchStatus() {
        try {
            const res = await fetch('../api/project_status.php');
            const data = await res.json();
            if (data.status === 'success') {
                renderStatusCards(data.stats);
                renderTimeline(data.timeline);
            }
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error loading status', duration: 3000, backgroundColor: '#ef4444' }).showToast();
        }
    }

    function renderStatusCards(stats) {
        const container = document.getElementById('statusCards');
        container.innerHTML = `
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-bold text-gray-800">Total Projects</h3>
                <p class="text-3xl font-bold text-indigo-600">${stats.total_projects}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-bold text-gray-800">Planning</h3>
                <p class="text-3xl font-bold text-yellow-600">${stats.planning}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-bold text-gray-800">In Progress</h3>
                <p class="text-3xl font-bold text-blue-600">${stats.in_progress}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-bold text-gray-800">Completed</h3>
                <p class="text-3xl font-bold text-green-600">${stats.completed}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-bold text-gray-800">Cancelled</h3>
                <p class="text-3xl font-bold text-red-600">${stats.cancelled}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-bold text-gray-800">Total Requests</h3>
                <p class="text-3xl font-bold text-purple-600">${stats.total_requests}</p>
            </div>
        `;
    }

    function renderTimeline(timeline) {
        const container = document.getElementById('timeline');
        container.innerHTML = timeline.map(item => `
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                    <span class="text-indigo-600 font-bold">${item.type === 'project' ? 'P' : 'R'}</span>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold">${item.title}</h3>
                    <p class="text-gray-600">${item.description}</p>
                    <p class="text-sm text-gray-500">${item.date}</p>
                    <span class="inline-block px-2 py-1 text-xs rounded ${item.status === 'Completed' || item.status === 'Approved' ? 'bg-green-100 text-green-800' : item.status === 'InProgress' || item.status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100'}">${item.status}</span>
                </div>
            </div>
        `).join('');
    }

    fetchStatus();
});
</script>
HTML;
adminLayout($children);
?>