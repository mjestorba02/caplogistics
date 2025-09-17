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
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Logistics Dashboard</a> &gt;
        <span>Purchase Management</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Purchase Management</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
            Add Purchase Request
        </button>
    </div>

    <!-- Purchase Requests Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Purchase Request Card -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">PO #001</h2>
                <span class="text-gray-500 text-sm">Pending</span>
            </div>
            <p class="text-gray-600 mb-2">Requested By: John Doe</p>
            <p class="text-gray-600 mb-2">Items: 15</p>
            <p class="text-gray-600 mb-4">Request Date: 2025-09-01</p>
            <div class="flex justify-end gap-2">
                <button class="text-blue-600 hover:underline">Edit</button>
                <button class="text-red-600 hover:underline">Delete</button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">PO #002</h2>
                <span class="text-green-600 text-sm">Approved</span>
            </div>
            <p class="text-gray-600 mb-2">Requested By: Jane Smith</p>
            <p class="text-gray-600 mb-2">Items: 25</p>
            <p class="text-gray-600 mb-4">Request Date: 2025-09-03</p>
            <div class="flex justify-end gap-2">
                <button class="text-blue-600 hover:underline">Edit</button>
                <button class="text-red-600 hover:underline">Delete</button>
            </div>
        </div>

        <!-- Add more purchase request cards dynamically from DB -->

    </div>

</div>

<!-- Modal Background -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
        <h2 class="text-2xl font-bold mb-4">Add Purchase Request</h2>
        <form id="addPurchaseForm" class="space-y-4">
            <div>
                <label class="block text-gray-700">Request Number</label>
                <input type="text" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Requested By</label>
                <input type="text" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Items Quantity</label>
                <input type="number" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Request Date</label>
                <input type="date" class="w-full border rounded px-3 py-2" required>
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

// Optional: handle form submission
document.getElementById("addPurchaseForm").addEventListener("submit", function(e){
    e.preventDefault();
    alert("Purchase request added! (Add database logic here)");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
});
</script>
';

adminLayout($children);
?>
