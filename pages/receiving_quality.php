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
        <span>Receiving & Quality Assurance</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Receiving & Quality Assurance</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">New Receipt</button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Filter by Status:</label>
            <select id="filterStatus" class="w-full md:w-48 border rounded px-3 py-2">
                <option value="all">All Status</option>
                <option value="Received">Received</option>
                <option value="Inspecting">Inspecting</option>
                <option value="Accepted">Accepted</option>
                <option value="Rejected">Rejected</option>
            </select>
            <button id="applyFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Apply Filters</button>
            <button id="clearFilter" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">Receipt #</th>
                    <th class="px-6 py-3">PO #</th>
                    <th class="px-6 py-3">Quantity</th>
                    <th class="px-6 py-3">Condition</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="receiptTable"></tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">No receipts found</div>
    </div>
</div>

<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative max-h-96 overflow-y-auto">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">New Goods Receipt</h2>
        <form id="receiptForm" class="space-y-4">
            <input type="hidden" id="receiptId" />
            <div>
                <label class="block text-gray-700 font-medium">Receipt #</label>
                <input id="receipt_number" type="text" class="w-full border rounded px-3 py-2 bg-gray-100" readonly placeholder="Auto-generated" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">PO Number *</label>
                <select id="po_number" class="w-full border rounded px-3 py-2" required>
                    <option value="">Select PO</option>
                    <option value="PO-0001">PO-0001</option>
                    <option value="PO-0002">PO-0002</option>
                    <option value="PO-0003">PO-0003</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium">Quantity Received</label>
                    <input id="quantity_received" type="number" min="1" class="w-full border rounded px-3 py-2" required />
                </div>
                <div>
                    <label class="block text-gray-700 font-medium">Quantity Inspected</label>
                    <input id="quantity_inspected" type="number" min="0" class="w-full border rounded px-3 py-2" required />
                </div>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Condition</label>
                <select id="condition" class="w-full border rounded px-3 py-2">
                    <option value="Good">Good</option>
                    <option value="Damaged">Damaged</option>
                    <option value="Defective">Defective</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Status</label>
                <select id="status" class="w-full border rounded px-3 py-2">
                    <option value="Received">Received</option>
                    <option value="Inspecting">Inspecting</option>
                    <option value="Accepted">Accepted</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script src="../scripts/receiving_quality.js"></script>
HTML;
adminLayout($children);
