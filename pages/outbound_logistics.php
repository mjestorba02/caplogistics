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
        <span>Outbound Logistics - Dispatch & Shipping</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Outbound Logistics</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Create Shipment</button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Search:</label>
            <input id="searchInput" type="text" placeholder="Search by Shipment or Customer..." class="w-full md:w-48 border rounded px-3 py-2" />
            <button id="applySearch" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Search</button>
            <button id="clearSearch" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
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
                <input id="total_items" type="number" class="w-full border rounded px-3 py-2" placeholder="0" />
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
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Shipment</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script src="../scripts/outbound_logistics.js"></script>
HTML;
adminLayout($children);
