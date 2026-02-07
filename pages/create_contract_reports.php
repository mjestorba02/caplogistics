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
        <span>Procurement - Create Contract and Reports</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Create Contract and Reports</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Create Contract</button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Filter by Date:</label>
            <input type="date" id="dateFrom" class="border rounded px-3 py-2 text-sm">
            <input type="date" id="dateTo" class="border rounded px-3 py-2 text-sm">
            <button id="applyFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Filter</button>
            <button id="clearFilter" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
            <button id="generateReport" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">Generate Report</button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Contract Title</th>
                    <th class="px-6 py-3">Supplier</th>
                    <th class="px-6 py-3">Start Date</th>
                    <th class="px-6 py-3">End Date</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="contractsTable"></tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">No contracts found</div>
    </div>
</div>

<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-[100] overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative my-8">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Create Contract</h2>
        <form id="contractForm" class="space-y-4">
            <input type="hidden" id="contractId" />
            <input type="hidden" id="vendor_id" />
            <div>
                <label class="block text-gray-700 font-medium">Contract Title *</label>
                <input id="contract_title" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., Office Supplies Contract" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Supplier Name *</label>
                <select id="supplier_name" class="w-full border rounded px-3 py-2" required>
                    <option value="">-- Select from Approved Vendors --</option>
                </select>
                <small class="text-gray-500">Only approved vendors from the Vendor Portal are available.</small>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Start Date *</label>
                <input id="start_date" type="date" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">End Date *</label>
                <input id="end_date" type="date" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Contract Value</label>
                <input id="contract_value" type="number" step="0.01" class="w-full border rounded px-3 py-2" placeholder="0.00" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Details</label>
                <textarea id="details" class="w-full border rounded px-3 py-2" rows="3" placeholder="Contract details"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Contract</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script src="../scripts/create_contract_reports.js"></script>
HTML;
adminLayout($children);
?>