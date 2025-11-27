<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location:https://log1.imarketph.com');
    exit();
}
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <span>Asset Maintenance & Servicing</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Asset Maintenance & Servicing</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Add Maintenance</button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Search:</label>
            <input id="filterInput" type="text" placeholder="Search work order, asset, technician..." class="w-full md:flex-1 border rounded px-3 py-2" />
            <button id="applyFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Search</button>
            <button id="clearFilter" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">Asset ID</th>
                    <th class="px-6 py-3">Work Order</th>
                    <th class="px-6 py-3">Type</th>
                    <th class="px-6 py-3">Scheduled Date</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Technician</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">No maintenance records found</div>
    </div>
</div>

<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative max-h-96 overflow-y-auto">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Add Maintenance</h2>
        <form id="form" class="space-y-4">
            <input type="hidden" id="itemId" />
            <div>
                <label class="block text-gray-700 font-medium">Asset *</label>
                <select id="asset_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">-- Select Asset --</option>
                </select>
                <small class="text-gray-500">Select an onboarded asset from the dropdown</small>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Work Order # *</label>
                <input id="work_order_number" type="text" class="w-full border rounded px-3 py-2" placeholder="Enter work order number" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Maintenance Type</label>
                <input id="maintenance_type" type="text" class="w-full border rounded px-3 py-2" placeholder="(optional)" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Scheduled Date *</label>
                <input id="scheduled_date" type="date" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Completed Date</label>
                <input id="completed_date" type="date" class="w-full border rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Technician</label>
                <input id="technician" type="text" class="w-full border rounded px-3 py-2" placeholder="(optional)" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Status</label>
                <select id="status" class="w-full border rounded px-3 py-2">
                    <option value="Pending">Pending</option>
                    <option value="Under Maintenance">Under Maintenance</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Maintenance</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script src="../scripts/asset_maintenance_servicing.js"></script>
HTML;
adminLayout($children);
