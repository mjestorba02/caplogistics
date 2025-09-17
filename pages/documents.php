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
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Records & Compliance</a> &gt;
        <span>Document Management</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Document Management</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
            Upload Document
        </button>
    </div>

    <!-- Documents Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Document Card -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <h2 class="text-xl font-semibold mb-2">Procurement Contract 2025</h2>
            <p class="text-gray-600 mb-1">Type: Contract</p>
            <p class="text-gray-600 mb-1">Uploaded By: Admin</p>
            <p class="text-gray-600 mb-2">Date: 2025-08-15</p>
            <div class="flex justify-end gap-2">
                <button class="text-blue-600 hover:underline">View</button>
                <button class="text-red-600 hover:underline">Delete</button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <h2 class="text-xl font-semibold mb-2">Warehouse Audit Report</h2>
            <p class="text-gray-600 mb-1">Type: Audit</p>
            <p class="text-gray-600 mb-1">Uploaded By: Auditor</p>
            <p class="text-gray-600 mb-2">Date: 2025-07-30</p>
            <div class="flex justify-end gap-2">
                <button class="text-blue-600 hover:underline">View</button>
                <button class="text-red-600 hover:underline">Delete</button>
            </div>
        </div>

        <!-- More documents dynamically from DB -->

    </div>

</div>

<!-- Modal for Uploading Document -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
        <h2 class="text-2xl font-bold mb-4">Upload Document</h2>
        <form id="uploadForm" class="space-y-4" enctype="multipart/form-data">
            <div>
                <label class="block text-gray-700">Document Name</label>
                <input type="text" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Document Type</label>
                <select class="w-full border rounded px-3 py-2" required>
                    <option value="">Select Type</option>
                    <option value="Contract">Contract</option>
                    <option value="Audit">Audit</option>
                    <option value="Report">Report</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700">File Upload</label>
                <input type="file" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Upload</button>
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
document.getElementById("uploadForm").addEventListener("submit", function(e){
    e.preventDefault();
    alert("Document uploaded! (Add database logic here)");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
});
</script>
';

adminLayout($children);
?>
