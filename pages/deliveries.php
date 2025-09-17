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
        <span>Shipments & Deliveries</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Shipments & Deliveries</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition flex items-center gap-2">
            <i class="bx bx-plus text-lg"></i> Add New Shipment
        </button>
    </div>

    <!-- Responsive Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-max min-w-full table-auto divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Shipment ID</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Origin</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Destination</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Carrier</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Status</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Estimated Delivery</th>
                    <th class="py-3 px-6 text-center text-sm font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr>
                    <td class="py-4 px-6 text-gray-800">SHIP-001</td>
                    <td class="py-4 px-6 text-gray-800">Warehouse A</td>
                    <td class="py-4 px-6 text-gray-800">Store 1</td>
                    <td class="py-4 px-6 text-gray-800">Carrier X</td>
                    <td class="py-4 px-6 text-gray-800">
                        <span class="px-2 py-1 bg-yellow-200 text-yellow-800 rounded-full text-xs flex items-center gap-1">
                            <i class="bx bx-truck"></i> In Transit
                        </span>
                    </td>
                    <td class="py-4 px-6 text-gray-800">2025-09-30</td>
                    <td class="py-4 px-6 text-center flex justify-center gap-3">
                        <button class="text-blue-600 hover:text-blue-800" title="View">
                            <i class="bx bx-show text-lg"></i>
                        </button>
                        <button class="text-green-600 hover:text-green-800" title="Mark Delivered">
                            <i class="bx bx-check-circle text-lg"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td class="py-4 px-6 text-gray-800">SHIP-002</td>
                    <td class="py-4 px-6 text-gray-800">Warehouse B</td>
                    <td class="py-4 px-6 text-gray-800">Store 2</td>
                    <td class="py-4 px-6 text-gray-800">Carrier Y</td>
                    <td class="py-4 px-6 text-gray-800">
                        <span class="px-2 py-1 bg-green-200 text-green-800 rounded-full text-xs flex items-center gap-1">
                            <i class="bx bx-check-circle"></i> Delivered
                        </span>
                    </td>
                    <td class="py-4 px-6 text-gray-800">2025-08-25</td>
                    <td class="py-4 px-6 text-center flex justify-center gap-3">
                        <button class="text-blue-600 hover:text-blue-800" title="View">
                            <i class="bx bx-show text-lg"></i>
                        </button>
                        <button class="text-gray-400 cursor-not-allowed" title="Mark Delivered">
                            <i class="bx bx-check-circle text-lg"></i>
                        </button>
                    </td>
                </tr>
                <!-- Add more rows dynamically from DB -->
            </tbody>
        </table>
    </div>

</div>

<!-- Modal Background -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 mx-2 relative">
        <h2 class="text-2xl font-bold mb-4">Add New Shipment</h2>
        <form id="addShipmentForm" class="space-y-4">
            <div>
                <label class="block text-gray-700">Origin</label>
                <input type="text" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Destination</label>
                <input type="text" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Carrier</label>
                <input type="text" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Estimated Delivery Date</label>
                <input type="date" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
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
document.getElementById("addShipmentForm").addEventListener("submit", function(e){
    e.preventDefault();
    alert("Shipment added! (Integrate database logic here)");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
});
</script>
';

adminLayout($children);
?>
