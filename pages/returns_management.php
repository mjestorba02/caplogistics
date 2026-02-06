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
        <span>Returns Management - Reverse Logistics</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Returns Management</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">New Return</button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Search:</label>
            <input id="searchInput" type="text" placeholder="Search by Return ID or Customer..." class="w-full md:w-48 border rounded px-3 py-2" />
            <button id="applySearch" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Search</button>
            <button id="clearSearch" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Return ID</th>
                    <th class="px-6 py-3">Customer</th>
                    <th class="px-6 py-3">Reason</th>
                    <th class="px-6 py-3">Items</th>
                    <th class="px-6 py-3">Inspection</th>
                    <th class="px-6 py-3">Classification</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="returnsTable"></tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">No returns found</div>
    </div>
</div>

<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative my-8">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">New Return</h2>
        <form id="returnForm" class="space-y-4">
            <input type="hidden" id="returnId" />
            <div>
                <label class="block text-gray-700 font-medium">Return ID *</label>
                <input id="return_id" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., RET-001" required />
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
                <label class="block text-gray-700 font-medium">Return Reason *</label>
                <textarea id="return_reason" class="w-full border rounded px-3 py-2" rows="2" placeholder="Why is the item being returned?" required></textarea>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Item Count</label>
                <input id="item_count" type="number" class="w-full border rounded px-3 py-2" placeholder="0" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Original Purchase Price</label>
                <input id="original_purchase_price" type="number" step="0.01" class="w-full border rounded px-3 py-2" placeholder="0.00" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Status</label>
                <select id="return_status" class="w-full border rounded px-3 py-2">
                    <option value="Initiated">Initiated</option>
                    <option value="Received">Received</option>
                    <option value="Inspected">Inspected</option>
                    <option value="Restocked">Restocked</option>
                    <option value="Refunded">Refunded</option>
                    <option value="Disposed">Disposed</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Return</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script src="../scripts/returns_management.js"></script>
HTML;
adminLayout($children);
