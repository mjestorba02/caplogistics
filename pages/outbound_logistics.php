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
        <span>Outbound Logistics - Dispatch & Shipping</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Outbound Logistics</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Shipment
        </button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Search:</label>
            <input id="searchInput" type="text" placeholder="Search by Shipment or Customer..." class="w-full md:w-48 border rounded px-3 py-2" />
            <button id="applySearch" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Search
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
                    <th class="px-6 py-3">Shipment #</th>
                    <th class="px-6 py-3">Order ID</th>
                    <th class="px-6 py-3">Customer</th>
                    <th class="px-6 py-3">Items</th>
                    <th class="px-6 py-3">Carrier</th>
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
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Create Shipment</h2>
        <form id="shipmentForm" class="space-y-4">
            <input type="hidden" id="shipmentId" />
            <div>
                <label class="block text-gray-700 font-medium">Shipment Number *</label>
                <input id="shipment_number" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., SHOUT-001" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Order ID</label>
                <input id="order_id" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., ORD-2024-001" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Customer Name *</label>
                <input id="customer_name" type="text" class="w-full border rounded px-3 py-2" placeholder="Enter customer name" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Customer Email</label>
                <input id="customer_email" type="email" class="w-full border rounded px-3 py-2" placeholder="customer@example.com" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Delivery Address</label>
                <textarea id="delivery_address" class="w-full border rounded px-3 py-2" rows="2" placeholder="Full delivery address"></textarea>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Total Items</label>
                <input id="total_items" type="number" class="w-full border rounded px-3 py-2" readonly />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Carrier Name</label>
                <input id="carrier_name" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., FastShip Logistics" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Status</label>
                <select id="delivery_status" class="w-full border rounded px-3 py-2">
                    <option value="Pending">Pending</option>
                    <option value="Packed">Packed</option>
                    <option value="Loaded">Loaded</option>
                    <option value="Dispatched">Dispatched</option>
                    <option value="In Transit">In Transit</option>
                    <option value="Delivered">Delivered</option>
                </select>
            </div>
            <!-- STEP 4: Outbound Items -->
            <div class="border-t pt-4">
                <h3 class="font-semibold mb-2 text-gray-800">Outbound Items</h3>

                <label class="block text-sm text-gray-600 mb-1">Select Product</label>
                <select id="inventorySelect" class="w-full border rounded px-3 py-2 mb-2"></select>

                <label class="block text-sm text-gray-600 mb-1">Quantity</label>
                <input id="outQty" type="number" min="1"
                    class="w-full border rounded px-3 py-2 mb-2"
                    placeholder="Enter quantity">

                <label class="block text-sm text-gray-600 mb-1">Department</label>
                <select id="department" class="w-full border rounded px-3 py-2 mb-2">
                    <option value="Sales">Sales</option>
                    <option value="Operations">Operations</option>
                    <option value="Warehouse">Warehouse</option>
                    <option value="IT">IT</option>
                </select>

                <button type="button" id="addItem"
                    class="bg-green-600 text-white px-3 py-2 rounded w-full hover:bg-green-700">
                    + Add Item
                </button>

                <ul id="itemList" class="mt-3 text-sm text-gray-700 space-y-1"></ul>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Shipment</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative my-8">
        <h2 class="text-2xl font-bold mb-4">Shipment Details</h2>
        <div class="space-y-4">
            <div><strong>Shipment Number:</strong> <span id="view_shipment_number"></span></div>
            <div><strong>Order ID:</strong> <span id="view_order_id"></span></div>
            <div><strong>Customer Name:</strong> <span id="view_customer_name"></span></div>
            <div><strong>Customer Email:</strong> <span id="view_customer_email"></span></div>
            <div><strong>Delivery Address:</strong> <span id="view_delivery_address"></span></div>
            <div><strong>Total Items:</strong> <span id="view_total_items"></span></div>
            <div><strong>Carrier Name:</strong> <span id="view_carrier_name"></span></div>
            <div><strong>Status:</strong> <span id="view_delivery_status"></span></div>
            <div><strong>Notes:</strong> <span id="view_notes"></span></div>
            <div>
                <strong>Outbound Items:</strong>
                <ul id="view_item_list" class="mt-2 text-sm text-gray-700 space-y-1"></ul>
            </div>
        </div>
        <button id="closeViewModal" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script src="../scripts/outbound_logistics.js"></script>
HTML;
adminLayout($children);
