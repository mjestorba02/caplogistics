<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location:http://localhost/caplog1');
    exit();
}
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <span>Storage & Inventory Management</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Storage & Inventory</h1>
        <div class="text-sm text-gray-600 bg-blue-50 p-3 rounded">
            Items are added via <strong>Inbound Logistics</strong> shipment approval
        </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Search:</label>
            <input id="searchInput" type="text" placeholder="Search by SKU or Product Name..." class="w-full md:w-48 border rounded px-3 py-2" />
            <input type="date" id="dateFrom" class="border rounded px-3 py-2 text-sm">
            <input type="date" id="dateTo" class="border rounded px-3 py-2 text-sm">
            <button id="applySearch" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
            </button>
            <button id="clearSearch" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Clear
            </button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">SKU</th>
                    <th class="px-6 py-3">Product Name</th>
                    <th class="px-6 py-3">Current Stock</th>
                    <th class="px-6 py-3">Available</th>
                    <th class="px-6 py-3">Bin Location</th>
                    <th class="px-6 py-3">Frequency</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="itemsTable"></tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">No items found</div>
    </div>
</div>

<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative my-8">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Add Item</h2>
        <form id="itemForm" class="space-y-4">
            <input type="hidden" id="itemId" />
            <div>
                <label class="block text-gray-700 font-medium">SKU *</label>
                <input id="sku" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., SKU-001" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Product Name *</label>
                <input id="product_name" type="text" class="w-full border rounded px-3 py-2" placeholder="Enter product name" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Category</label>
                <input id="category" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., Electronics" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Bin Location</label>
                <input id="bin_location" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., ZONE-A-BIN-01" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Warehouse Zone</label>
                <input id="warehouse_zone" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., ZONE-A" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Current Stock</label>
                <input id="current_stock" type="number" class="w-full border rounded px-3 py-2" placeholder="0" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Movement Frequency</label>
                <select id="movement_frequency" class="w-full border rounded px-3 py-2">
                    <option value="Fast">Fast</option>
                    <option value="Medium">Medium</option>
                    <option value="Slow">Slow</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Supplier Name</label>
                <input id="supplier_name" type="text" class="w-full border rounded px-3 py-2" placeholder="Enter supplier name" />
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Item</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script src="../scripts/storage_inventory.js"></script>
HTML;
adminLayout($children);
