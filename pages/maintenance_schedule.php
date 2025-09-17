<?php
session_start(); 

if (!isset($_SESSION['id'])) {
    header('Location:http://localhost/hr1-ecommerce');
    exit();
}
include '../layout/adminLayout.php';

$children = '
<div class="p-6">

    <!-- Breadcrumb -->
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Asset Management</a> &gt;
        <span>Maintenance Scheduling</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Maintenance Scheduling</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
            Schedule Maintenance
        </button>
    </div>

    <!-- Maintenance Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Asset Maintenance Card -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <h2 class="text-xl font-semibold mb-2">Laptop - Dell XPS 15</h2>
            <p class="text-gray-600 mb-1">Asset ID: AS001</p>
            <p class="text-gray-600 mb-1">Category: Electronics</p>
            <p class="text-gray-600 mb-1">Next Maintenance: 2025-09-15</p>
            <p class="text-gray-600 mb-2">Type: Preventive</p>
            <div class="flex justify-end gap-2">
                <button class="text-blue-600 hover:underline">Edit</button>
                <button class="text-red-600 hover:underline">Remove</button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <h2 class="text-xl font-semibold mb-2">Projector - Epson X200</h2>
            <p class="text-gray-600 mb-1">Asset ID: AS002</p>
            <p class="text-gray-600 mb-1">Category: Electronics</p>
            <p class="text-gray-600 mb-1">Next Maintenance: 2025-10-01</p>
            <p class="text-gray-600 mb-2">Type: Corrective</p>
            <div class="flex justify-end gap-2">
                <button class="text-blue-600 hover:underline">Edit</button>
                <button class="text-red-600 hover:underline">Remove</button>
            </div>
        </div>

        <!-- Add more maintenance cards dynamically from DB -->

    </div>

</div>

<!-- Modal for Adding Maintenance -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
        <h2 class="text-2xl font-bold mb-4">Schedule Maintenance</h2>
        <form id="addMaintenanceForm" class="space-y-4">
            <div>
                <label class="block text-gray-700">Asset Name</label>
                <input type="text" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Asset ID</label>
                <input type="text" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Maintenance Date</label>
                <input type="date" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Maintenance Type</label>
                <select class="w-full border rounded px-3 py-2">
                    <option>Preventive</option>
                    <option>Corrective</option>
                    <option>Predictive</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
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
document.getElementById("addMaintenanceForm").addEventListener("submit", function(e){
    e.preventDefault();
    alert("Maintenance scheduled! (Add database logic here)");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
});
</script>
';

adminLayout($children);
?>
