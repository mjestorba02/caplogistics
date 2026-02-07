<?php
session_start(); 

if (!isset($_SESSION['id'])) {
    header('Location:http://localhost/caplog1');
    exit();
}
include '../layout/adminLayout.php';

$children = '
<div class="p-6">

    <!-- Breadcrumb -->
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Records & Compliance</a> &gt;
        <span>Audit & Reporting</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Audit & Reporting</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
            Generate Report
        </button>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Recent Audit -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <h2 class="text-xl font-semibold mb-2">Warehouse Audit - August 2025</h2>
            <p class="text-gray-600 mb-1">Type: Inventory Audit</p>
            <p class="text-gray-600 mb-1">Auditor: John Doe</p>
            <p class="text-gray-600 mb-2">Date: 2025-08-20</p>
            <div class="flex justify-end gap-2">
                <button class="text-blue-600 hover:underline">View</button>
                <button class="text-red-600 hover:underline">Download</button>
            </div>
        </div>

        <!-- Supplier Audit -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <h2 class="text-xl font-semibold mb-2">Supplier Performance Audit</h2>
            <p class="text-gray-600 mb-1">Type: Supplier Audit</p>
            <p class="text-gray-600 mb-1">Auditor: Jane Smith</p>
            <p class="text-gray-600 mb-2">Date: 2025-08-10</p>
            <div class="flex justify-end gap-2">
                <button class="text-blue-600 hover:underline">View</button>
                <button class="text-red-600 hover:underline">Download</button>
            </div>
        </div>

        <!-- Compliance Report -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <h2 class="text-xl font-semibold mb-2">Compliance Report - Q2 2025</h2>
            <p class="text-gray-600 mb-1">Type: Compliance</p>
            <p class="text-gray-600 mb-1">Prepared By: Admin</p>
            <p class="text-gray-600 mb-2">Date: 2025-07-31</p>
            <div class="flex justify-end gap-2">
                <button class="text-blue-600 hover:underline">View</button>
                <button class="text-red-600 hover:underline">Download</button>
            </div>
        </div>

        <!-- More reports dynamically from DB -->

    </div>

</div>

<!-- Modal for Generating Report -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
        <h2 class="text-2xl font-bold mb-4">Generate Audit Report</h2>
        <form id="generateReportForm" class="space-y-4">
            <div>
                <label class="block text-gray-700">Report Type</label>
                <select class="w-full border rounded px-3 py-2" required>
                    <option value="">Select Type</option>
                    <option value="Inventory Audit">Inventory Audit</option>
                    <option value="Supplier Audit">Supplier Audit</option>
                    <option value="Compliance">Compliance</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700">Date Range</label>
                <input type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., 2025-08-01 to 2025-08-31" required>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Generate</button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal toggle
const modal = document.getElementById("modal");
const openModal = document.getElementById("openModal");
const closeModal = document.getElementById("closeModal");

openModal.addEventListener("click", () => {
    modal.classList.remove("hidden");
    modal.classList.add("flex");
});

closeModal.addEventListener("click", () => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
});

// Handle form submission
document.getElementById("generateReportForm").addEventListener("submit", function(e){
    e.preventDefault();
    alert("Audit report generated! (Add database logic here)");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
});
</script>
';

adminLayout($children);
?>
