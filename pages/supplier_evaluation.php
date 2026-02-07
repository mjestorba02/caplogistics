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
        <span>Supplier Evaluation & Selection</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Supplier Evaluation & Selection</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Create RFQ</button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Filter by Status:</label>
            <select id="filterStatus" class="w-full md:w-48 border rounded px-3 py-2">
                <option value="all">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Submitted">Submitted</option>
                <option value="Selected">Selected</option>
            </select>
            <button id="applyFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Apply Filters</button>
            <button id="clearFilter" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">RFQ ID</th>
                    <th class="px-6 py-3">Item</th>
                    <th class="px-6 py-3">Suppliers</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Created Date</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="rfqTable"></tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">No RFQs found</div>
    </div>
</div>

<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative max-h-96 overflow-y-auto">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Create RFQ</h2>
        <form id="rfqForm" class="space-y-4">
            <input type="hidden" id="rfqId" />
            <div>
                <label class="block text-gray-700 font-medium">Item Description</label>
                <input id="item_description" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium">Quantity</label>
                    <input id="quantity" type="number" min="1" class="w-full border rounded px-3 py-2" required />
                </div>
                <div>
                    <label class="block text-gray-700 font-medium">Budget</label>
                    <input id="budget" type="number" step="0.01" class="w-full border rounded px-3 py-2" required />
                </div>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Suppliers (comma-separated)</label>
                <input id="suppliers" type="text" class="w-full border rounded px-3 py-2" placeholder="Supplier1, Supplier2" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Status</label>
                <select id="status" class="w-full border rounded px-3 py-2">
                    <option value="Pending">Pending</option>
                    <option value="Submitted">Submitted</option>
                    <option value="Selected">Selected</option>
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

<script src="../scripts/supplier_evaluation.js"></script>
HTML;
adminLayout($children);
