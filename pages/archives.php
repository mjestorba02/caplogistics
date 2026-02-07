<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: ../index.php');
    exit();
}
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <span>Archives Management</span>
    </div>

    <h1 class="text-3xl font-bold text-gray-800 mb-6">Archived Items</h1>

    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Filter by Type:</label>
            <select id="archiveTypeFilter" class="border rounded px-3 py-2 text-sm flex-1">
                <option value="">All Types</option>
                <option value="contract">Contracts</option>
                <option value="supplier">Suppliers</option>
                <option value="request">Requests</option>
                <option value="purchase">Purchase Orders</option>
                <option value="document">Documents</option>
                <option value="other">Other</option>
            </select>
            <button id="applyFilterBtn" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Filter</button>
            <button id="clearFilterBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-gray-600 text-sm font-medium">Total Archived</h3>
            <p class="text-3xl font-bold text-indigo-600" id="totalArchived">0</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-gray-600 text-sm font-medium">Today</h3>
            <p class="text-3xl font-bold text-blue-600" id="todayArchived">0</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-gray-600 text-sm font-medium">This Month</h3>
            <p class="text-3xl font-bold text-green-600" id="monthArchived">0</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-gray-600 text-sm font-medium">Restorable</h3>
            <p class="text-3xl font-bold text-purple-600" id="restorableArchived">0</p>
        </div>
    </div>

    <!-- Archives Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Type</th>
                    <th class="px-6 py-3">Original Table</th>
                    <th class="px-6 py-3">Archived By</th>
                    <th class="px-6 py-3">Archived At</th>
                    <th class="px-6 py-3">Reason</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="archivesTableBody" class="divide-y divide-gray-200">
                <!-- Archives will be dynamically inserted here -->
            </tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">
            <i class="fas fa-inbox text-4xl text-gray-400 mb-3"></i>
            <p>No archived items found</p>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 my-8">
        <h2 class="text-2xl font-bold mb-4">Archived Item Details</h2>
        <div id="detailsContent" class="space-y-4 mb-6 max-h-96 overflow-y-auto">
            <!-- Content will be inserted here -->
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" id="restoreBtn" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Restore</button>
            <button type="button" id="closeDetailsModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Close</button>
        </div>
    </div>
</div>

<script src="../scripts/archive_management.js"></script>
HTML;
adminLayout($children);
?>

