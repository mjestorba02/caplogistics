
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
        <span>Asset Onboarding & Registration</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Asset Onboarding & Registration</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Add Asset</button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Search:</label>
            <input id="filterInput" type="text" placeholder="Search asset tag, name, type..." class="w-full md:flex-1 border rounded px-3 py-2" />
            <button id="applyFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Search</button>
            <button id="clearFilter" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">Asset Tag</th>
                    <th class="px-6 py-3">Asset Name</th>
                    <th class="px-6 py-3">Asset Type</th>
                    <th class="px-6 py-3">Serial Number</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Registration Date</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">No assets found</div>
    </div>
</div>

<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative max-h-96 overflow-y-auto">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Add Asset</h2>
        <form id="form" class="space-y-4">
            <input type="hidden" id="itemId" />
            <div>
                <label class="block text-gray-700 font-medium">Receiving Record *</label>
                <select id="receiving_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">-- Select a Receiving Record --</option>
                </select>
                <small class="text-gray-500">Select the purchase order/receiving record this asset belongs to</small>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Asset Tag *</label>
                <input id="asset_tag" type="text" class="w-full border rounded px-3 py-2" placeholder="Enter asset tag" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Asset Name *</label>
                <input id="asset_name" type="text" class="w-full border rounded px-3 py-2" placeholder="Enter asset name" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Asset Type</label>
                <input id="asset_type" type="text" class="w-full border rounded px-3 py-2" placeholder="(optional)" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Serial Number</label>
                <input id="serial_number" type="text" class="w-full border rounded px-3 py-2" placeholder="(optional)" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Registration Date *</label>
                <input id="registration_date" type="date" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Registered By</label>
                <input id="registered_by" type="text" class="w-full border rounded px-3 py-2" placeholder="(optional)" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Status</label>
                <select id="status" class="w-full border rounded px-3 py-2">
                    <option value="In Inventory">In Inventory</option>
                    <option value="Ready for Deployment">Ready for Deployment</option>
                    <option value="Registered">Registered</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Asset</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script src="../scripts/asset_onboarding_registration.js"></script>
HTML;
adminLayout($children);