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
        <span>Inbound Logistics - Receiving & Putaway</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Inbound Logistics</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Shipment
        </button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-end gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700">Search</label>
                <input id="searchInput" type="text" placeholder="Shipment ID or Supplier"
                    class="w-full md:w-48 border rounded px-3 py-2" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select id="statusFilter"
                    class="border rounded px-3 py-2 w-full md:w-48">
                    <option value="">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Received">Received</option>
                    <option value="Verified">Verified</option>
                    <option value="Putaway Complete">Putaway Complete</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">From Date</label>
                <input id="dateFrom" type="date"
                    class="border rounded px-3 py-2 w-full md:w-40" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">To Date</label>
                <input id="dateTo" type="date"
                    class="border rounded px-3 py-2 w-full md:w-40" />
            </div>

            <div class="flex gap-2">
                <button id="applySearch"
                    class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                </button>

                <button id="clearSearch"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Clear
                </button>
            </div>

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
                    <th class="px-6 py-3">Expected</th>
                    <th class="px-6 py-3">Received</th>
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
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-8 relative my-8">
        <button id="closeModalBtn" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        
        <h2 id="modalTitle" class="text-2xl font-bold mb-6 text-gray-800">Add Shipment</h2>
        
        <form id="shipmentForm" class="space-y-6">
            <input type="hidden" id="shipmentId" />
            
            <!-- Header Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold text-sm mb-2">Shipment ID *</label>
                    <input id="shipment_id" type="text" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-indigo-500" placeholder="e.g., SHIP-001" required />
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold text-sm mb-2">PO Number</label>
                    <input id="po_number" type="text" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-indigo-500" placeholder="e.g., PO-2024-001" />
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold text-sm mb-2">Supplier Name *</label>
                    <input id="supplier_name" type="text" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-indigo-500" placeholder="Enter supplier name" required />
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold text-sm mb-2">Handler Name</label>
                    <input id="handler_name" type="text" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-indigo-500" placeholder="Person receiving shipment" />
                </div>
            </div>

            <!-- Item Receipt Section -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Item Receipt Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">Total Items (Expected)</label>
                        <input id="total_items" type="number" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-indigo-500" placeholder="0" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">Items Received</label>
                        <input id="items_received" type="number" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-indigo-500" placeholder="0" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">Items Verified</label>
                        <input id="items_verified" type="number" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-indigo-500" placeholder="0" />
                    </div>
                </div>
            </div>

            <!-- Quality & Status Section -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quality & Status</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">Quality Status</label>
                        <select id="quality_status" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-indigo-500">
                            <option value="Pending">Pending</option>
                            <option value="Good">Good</option>
                            <option value="Damaged">Damaged</option>
                            <option value="Partial">Partial</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">Shipment Status</label>
                        <select id="status" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-indigo-500">
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Received">Received</option>
                            <option value="Verified">Verified</option>
                            <option value="Putaway Complete">Putaway Complete</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Storage Location Section -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Storage Location</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">Category</label>
                        <input id="category" type="text" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-indigo-500" placeholder="e.g., Electronics, Parts" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">Bin Location</label>
                        <input id="bin_location" type="text" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-indigo-500" placeholder="e.g., ZONE-A-BIN-01" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">Warehouse Zone</label>
                        <input id="warehouse_zone" type="text" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-indigo-500" placeholder="e.g., ZONE-A" />
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="border-t pt-6">
                <label class="block text-gray-700 font-semibold text-sm mb-2">Notes</label>
                <textarea id="notes" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:border-indigo-500" rows="3" placeholder="Additional notes or special handling instructions"></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3 border-t pt-6">
                <button type="button" id="closeModal" class="px-6 py-2 bg-gray-300 text-gray-700 rounded font-medium hover:bg-gray-400 transition">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded font-medium hover:bg-indigo-700 transition">Save Shipment</button>
            </div>
        </form>
    </div>
</div>

<script src="../scripts/inbound_logistics.js"></script>
HTML;
adminLayout($children);
