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
        <span>Archived Items</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Archived Items</h1>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                <div class="text-sm text-gray-600">Total Archived</div>
                <div class="text-3xl font-bold text-indigo-600" id="totalArchived">0</div>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div class="text-sm text-gray-600">Today</div>
                <div class="text-3xl font-bold text-blue-600" id="todayArchived">0</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <div class="text-sm text-gray-600">This Month</div>
                <div class="text-3xl font-bold text-green-600" id="monthArchived">0</div>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                <div class="text-sm text-gray-600">Restorable</div>
                <div class="text-3xl font-bold text-orange-600" id="restorableArchived">0</div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Filter by Type:</label>
                <select id="archiveTypeFilter" class="border rounded px-3 py-2 w-full md:w-48">
                    <option value="">All Types</option>
                    <option value="inbound_logistics">Inbound Logistics</option>
                    <option value="asset_management">Asset Management</option>
                    <option value="asset_onboarding">Asset Onboarding</option>
                    <option value="asset_maintenance">Asset Maintenance</option>
                    <option value="asset_disposal">Asset Disposal</option>
                    <option value="asset_deployment">Asset Deployment</option>
                    <option value="po">Purchase Order</option>
                    <option value="request">Supply Request</option>
                    <option value="storage_inventory">Storage Inventory</option>
                    <option value="return">Returns</option>
                    <option value="delivery">Delivery</option>
                    <option value="tracking">Tracking</option>
                    <option value="compliance">Compliance</option>
                    <option value="receiving">Receiving</option>
                    <option value="project">Project</option>
                    <option value="performance">Performance</option>
                    <option value="shipment_scheduling">Shipment Scheduling</option>
                </select>
            </div>
            <button id="applyFilterBtn" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Filter</button>
            <button id="clearFilterBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
        </div>
    </div>

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
            <tbody id="archivesTableBody"></tbody>
        </table>
        <div id="emptyState" class="text-center py-8 text-gray-500">
            No archived items found
        </div>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-[100] overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative my-8">
        <h2 class="text-2xl font-bold mb-4">Archived Item Details</h2>
        <div id="detailsContent" class="mb-6 max-h-96 overflow-y-auto"></div>
        <div class="flex justify-end gap-2">
            <button id="restoreBtn" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">Restore</button>
            <button id="closeDetailsModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Close</button>
        </div>
        <button onclick="document.getElementById('detailsModal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script src="../scripts/archive_management.js"></script>
HTML;

adminLayout($children);
?>
