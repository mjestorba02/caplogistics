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
        <a href="warehouse_analytics.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <span>Asset Receiving & Logistics Intake</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Asset Receiving & Logistics Intake</h1>
        <button id="addReceivingBtn" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Add Receiving</button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Search:</label>
            <input id="filterInput" type="text" placeholder="Search PO #, supplier, date..." class="w-full md:flex-1 border rounded px-3 py-2" />
            <button id="applyFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Search</button>
            <button id="clearFilter" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">PO #</th>
                    <th class="px-6 py-3">Received Date</th>
                    <th class="px-6 py-3">Received By</th>
                    <th class="px-6 py-3">Supplier</th>
                    <th class="px-6 py-3">Item Description</th>
                    <th class="px-6 py-3">Qty Received</th>
                    <th class="px-6 py-3">Qty Expected</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">No receiving records found</div>
    </div>
</div>

<div id="receivingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative max-h-96 overflow-y-auto">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Add Receiving</h2>
        <form id="receivingForm" class="space-y-4">
            <input type="hidden" id="receivingId" />
            <div>
                <label class="block text-gray-700 font-medium">PO Number *</label>
                <input id="po_number" type="text" class="w-full border rounded px-3 py-2" placeholder="Enter PO number" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Received Date *</label>
                <input id="received_date" type="date" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Received By *</label>
                <input id="received_by" type="text" class="w-full border rounded px-3 py-2" placeholder="Enter receiver name" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Supplier Name</label>
                <input id="supplier_name" type="text" class="w-full border rounded px-3 py-2" placeholder="(optional)" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Item Description</label>
                <input id="item_description" type="text" class="w-full border rounded px-3 py-2" placeholder="(optional)" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Quantity Received *</label>
                <input id="quantity_received" type="number" class="w-full border rounded px-3 py-2" placeholder="0" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Quantity Expected *</label>
                <input id="quantity_expected" type="number" class="w-full border rounded px-3 py-2" placeholder="0" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Damage Notes</label>
                <textarea id="damage_notes" class="w-full border rounded px-3 py-2" placeholder="(optional)" rows="2"></textarea>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Discrepancy Notes</label>
                <textarea id="discrepancy_notes" class="w-full border rounded px-3 py-2" placeholder="(optional)" rows="2"></textarea>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Status</label>
                <select id="status" class="w-full border rounded px-3 py-2">
                    <option value="Received">Received</option>
                    <option value="Pending">Pending</option>
                    <option value="Discrepancy">Discrepancy</option>
                    <option value="Damaged">Damaged</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Receiving</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script src="../scripts/asset_receiving_logistics.js"></script>
<?php
session_start();
if (!isset($_SESSION['id'])) {
        header('Location:https://log1.imarketph.com');
        exit();
}
include '../layout/adminLayout.php';

HTML;
adminLayout($children);
