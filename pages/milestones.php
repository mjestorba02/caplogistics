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
        <span>Milestones & Status Updates</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Milestones & Status Updates</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
            Add Milestone
        </button>
    </div>

    <!-- Milestones Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Shipment ID</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Milestone</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Status</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Updated On</th>
                    <th class="py-3 px-6 text-center text-sm font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr>
                    <td class="py-4 px-6 text-gray-800">SHIP-001</td>
                    <td class="py-4 px-6 text-gray-800">Picked Up</td>
                    <td class="py-4 px-6 text-gray-800"><span class="px-2 py-1 bg-green-200 text-green-800 rounded-full text-xs">Completed</span></td>
                    <td class="py-4 px-6 text-gray-800">2025-08-25</td>
                    <td class="py-4 px-6 text-center">
                        <button class="text-blue-600 hover:underline mr-2">Edit</button>
                        <button class="text-red-600 hover:underline">Remove</button>
                    </td>
                </tr>
                <tr>
                    <td class="py-4 px-6 text-gray-800">SHIP-001</td>
                    <td class="py-4 px-6 text-gray-800">In Transit</td>
                    <td class="py-4 px-6 text-gray-800"><span class="px-2 py-1 bg-yellow-200 text-yellow-800 rounded-full text-xs">Pending</span></td>
                    <td class="py-4 px-6 text-gray-800">2025-08-26</td>
                    <td class="py-4 px-6 text-center">
                        <button class="text-blue-600 hover:underline mr-2">Edit</button>
                        <button class="text-red-600 hover:underline">Remove</button>
                    </td>
                </tr>
                <!-- More milestones from DB -->
            </tbody>
        </table>
    </div>

</div>

<!-- Modal Background -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
        <h2 class="text-2xl font-bold mb-4">Add Milestone</h2>
        <form id="addMilestoneForm" class="space-y-4">
            <div>
                <label class="block text-gray-700">Shipment ID</label>
                <input type="text" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Milestone Description</label>
                <input type="text" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Status</label>
                <select class="w-full border rounded px-3 py-2" required>
                    <option value="Pending">Pending</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700">Date</label>
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

// Handle form submission
document.getElementById("addMilestoneForm").addEventListener("submit", function(e){
    e.preventDefault();
    alert("Milestone added! (Add database logic here)");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
});
</script>
';

adminLayout($children);
?>
