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
        <span>Document Tracking - Reports</span>
    </div>

    <h1 class="text-3xl font-bold text-gray-800 mb-6">Document Reports</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Document Requests by Status</h2>
            <div id="requestStats" class="space-y-2"></div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Document Uploads by Status</h2>
            <div id="uploadStats" class="space-y-2"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const res = await fetch('../api/document_reports.php');
        const data = await res.json();

        if (data.status === 'success') {
            const requestStats = data.request_stats.map(stat => `<div class="flex justify-between"><span>${stat.status}:</span><span class="font-bold">${stat.count}</span></div>`).join('');
            const uploadStats = data.upload_stats.map(stat => `<div class="flex justify-between"><span>${stat.status}:</span><span class="font-bold">${stat.count}</span></div>`).join('');

            document.getElementById('requestStats').innerHTML = requestStats || '<p>No data</p>';
            document.getElementById('uploadStats').innerHTML = uploadStats || '<p>No data</p>';
        } else {
            throw new Error(data.message || 'Failed to load reports');
        }
    } catch (err) {
        console.error(err);
        Toastify({
            text: 'Error loading reports',
            duration: 3000,
            gravity: 'top',
            position: 'right',
            backgroundColor: '#ef4444'
        }).showToast();
    }
});
</script>
HTML;
adminLayout($children);
?>