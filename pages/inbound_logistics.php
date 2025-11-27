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
        <span>Inbound Logistics - Receiving & Putaway</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Inbound Logistics</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Add Shipment</button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Search:</label>
            <input id="searchInput" type="text" placeholder="Search by Shipment ID or Supplier..." class="w-full md:w-48 border rounded px-3 py-2" />
            <button id="applySearch" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Search</button>
            <button id="clearSearch" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Shipment ID</th>
                    <th class="px-6 py-3">PO Number</th>
                    <th class="px-6 py-3">Supplier</th>
                    <th class="px-6 py-3">Items Received</th>
                    <th class="px-6 py-3">Quality</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="shipmentsTable"></tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">No shipments found</div>
    </div>
</div>

<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative my-8">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Add Shipment</h2>
        <form id="shipmentForm" class="space-y-4">
            <input type="hidden" id="shipmentId" />
            <div>
                <label class="block text-gray-700 font-medium">Shipment ID *</label>
                <input id="shipment_id" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., SHIP-001" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">PO Number</label>
                <input id="po_number" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., PO-2024-001" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Supplier Name *</label>
                <input id="supplier_name" type="text" class="w-full border rounded px-3 py-2" placeholder="Enter supplier name" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Total Items</label>
                <input id="total_items" type="number" class="w-full border rounded px-3 py-2" placeholder="0" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Quality Status</label>
                <select id="quality_status" class="w-full border rounded px-3 py-2">
                    <option value="Pending">Pending</option>
                    <option value="Good">Good</option>
                    <option value="Damaged">Damaged</option>
                    <option value="Partial">Partial</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Handler Name</label>
                <input id="handler_name" type="text" class="w-full border rounded px-3 py-2" placeholder="Person receiving shipment" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Status</label>
                <select id="status" class="w-full border rounded px-3 py-2">
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Received">Received</option>
                    <option value="Verified">Verified</option>
                    <option value="Putaway Complete">Putaway Complete</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Notes</label>
                <textarea id="notes" class="w-full border rounded px-3 py-2" rows="2" placeholder="Additional notes"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Shipment</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script src="../scripts/inbound_logistics.js"></script>
HTML;
adminLayout($children);
