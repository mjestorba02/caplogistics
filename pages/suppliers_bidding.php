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
        <span>Suppliers & Bidding</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Suppliers & Bidding</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
            Add Supplier / Bid
        </button>
    </div>

    <!-- Suppliers Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Supplier Card -->
        <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition">
            <div class="flex justify-between mb-2">
                <h2 class="font-semibold text-lg">Supplier A</h2>
                <span class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">Active</span>
            </div>
            <p class="text-gray-600 mb-1"><span class="font-semibold">Email:</span> supplierA@example.com</p>
            <p class="text-gray-600 mb-1"><span class="font-semibold">RFQs Sent:</span> 5</p>
            <p class="text-gray-600 mb-2"><span class="font-semibold">Bids Submitted:</span> 3</p>
            <div class="flex justify-end gap-2">
                <button class="text-indigo-600 hover:underline font-medium">View Bids</button>
                <button class="text-red-600 hover:underline font-medium">Remove</button>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition">
            <div class="flex justify-between mb-2">
                <h2 class="font-semibold text-lg">Supplier B</h2>
                <span class="px-2 py-1 rounded-full bg-red-100 text-red-800 text-xs font-semibold">Inactive</span>
            </div>
            <p class="text-gray-600 mb-1"><span class="font-semibold">Email:</span> supplierB@example.com</p>
            <p class="text-gray-600 mb-1"><span class="font-semibold">RFQs Sent:</span> 3</p>
            <p class="text-gray-600 mb-2"><span class="font-semibold">Bids Submitted:</span> 2</p>
            <div class="flex justify-end gap-2">
                <button class="text-indigo-600 hover:underline font-medium">View Bids</button>
                <button class="text-red-600 hover:underline font-medium">Remove</button>
            </div>
        </div>

        <!-- Add more supplier cards dynamically from DB -->

    </div>

</div>

<!-- Modal Background -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 mx-2 relative">
        <h2 class="text-2xl font-bold mb-4">Add Supplier / Bid</h2>
        <form id="addSupplierForm" class="space-y-4">
            <div>
                <label class="block text-gray-700">Supplier Name</label>
                <input type="text" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Contact Email</label>
                <input type="email" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">RFQs Sent</label>
                <input type="number" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Bids Submitted</label>
                <input type="number" class="w-full border rounded px-3 py-2" required>
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
document.getElementById("addSupplierForm").addEventListener("submit", function(e){
    e.preventDefault();
    alert("Supplier / Bid added! (Add database logic here)");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
});
</script>
';

adminLayout($children);
?>
