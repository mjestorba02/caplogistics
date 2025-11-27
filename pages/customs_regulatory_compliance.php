<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location:https://log1.imarketph.com');
    exit();
}
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Customs & Regulatory Compliance</h1>
        <button id="addComplianceBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">+ Add Compliance Record</button>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <input type="text" id="searchInput" placeholder="Search compliance records..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left">Compliance ID</th>
                    <th class="px-6 py-3 text-left">Shipment ID</th>
                    <th class="px-6 py-3 text-left">Declaration Status</th>
                    <th class="px-6 py-3 text-left">Customs Clearance</th>
                    <th class="px-6 py-3 text-left">Permits</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody id="complianceTable">
                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div id="complianceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6">
        <h2 class="text-2xl font-bold mb-4">Add Compliance Record</h2>
        <form id="complianceForm" class="space-y-4">
            <input type="hidden" id="complianceId">
            <input type="text" id="complianceIdInput" placeholder="Compliance ID" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
            <input type="text" id="shipmentId" placeholder="Shipment ID" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
            <select id="declarationStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="Draft">Draft</option>
                <option value="Submitted">Submitted</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
            </select>
            <select id="customsClearanceStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="Pending">Pending</option>
                <option value="In Progress">In Progress</option>
                <option value="Cleared">Cleared</option>
                <option value="Hold">Hold</option>
            </select>
            <label class="flex items-center">
                <input type="checkbox" id="permitsObtained" class="w-4 h-4">
                <span class="ml-2">Permits Obtained</span>
            </label>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold">Save</button>
                <button type="button" onclick="closeComplianceModal()" class="flex-1 bg-gray-400 hover:bg-gray-500 text-white py-2 rounded-lg font-semibold">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="../scripts/customs_regulatory_compliance.js"></script>
HTML;

adminLayout($children);
