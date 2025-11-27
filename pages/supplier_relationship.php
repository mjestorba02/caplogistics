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
        <span>Supplier Relationship Management</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Supplier Relationship Management</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">New Supplier</button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Filter by Performance:</label>
            <select id="filterPerformance" class="w-full md:w-48 border rounded px-3 py-2">
                <option value="all">All Performance</option>
                <option value="Excellent">Excellent</option>
                <option value="Good">Good</option>
                <option value="Fair">Fair</option>
                <option value="Poor">Poor</option>
            </select>
            <button id="applyFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Apply Filters</button>
            <button id="clearFilter" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="supplierGrid"></div>
    <div id="emptyState" class="hidden bg-white p-8 rounded-lg shadow text-center text-gray-600">No suppliers found</div>
</div>

<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative max-h-96 overflow-y-auto">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Add Supplier</h2>
        <form id="supplierForm" class="space-y-4">
            <input type="hidden" id="supplierId" />
            <div>
                <label class="block text-gray-700 font-medium">Supplier Name</label>
                <input id="supplier_name" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Contact Email</label>
                <input id="contact_email" type="email" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Performance Rating</label>
                <select id="performance_rating" class="w-full border rounded px-3 py-2">
                    <option value="Excellent">Excellent</option>
                    <option value="Good">Good</option>
                    <option value="Fair">Fair</option>
                    <option value="Poor">Poor</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium">On-Time Delivery %</label>
                    <input id="ontime_delivery" type="number" min="0" max="100" step="0.1" class="w-full border rounded px-3 py-2" required />
                </div>
                <div>
                    <label class="block text-gray-700 font-medium">Quality Score %</label>
                    <input id="quality_score" type="number" min="0" max="100" step="0.1" class="w-full border rounded px-3 py-2" required />
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script src="../scripts/supplier_relationship.js"></script>
HTML;
adminLayout($children);
