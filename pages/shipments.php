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
        <span>Shipments & Movements</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Shipments & Movements</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
            <i class="bx bx-plus mr-1"></i> Add New Shipment
        </button>
    </div>

    <!-- Shipments Cards Grid -->
    <div id="shipmentsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Cards will be injected here dynamically -->
    </div>

</div>

<!-- Modal Background -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
        <h2 class="text-2xl font-bold mb-4">Add / Edit Shipment</h2>
        <form id="shipmentForm" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input type="hidden" name="id" value="">
            <div>
                <label class="block text-gray-700">Shipment Number</label>
                <input type="text" name="shipment_number" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Origin</label>
                <input type="text" name="origin" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Destination</label>
                <input type="text" name="destination" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Items Quantity</label>
                <input type="number" name="items_quantity" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Dispatch Date</label>
                <input type="date" name="dispatch_date" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Status</label>
                <select name="status" class="w-full border rounded px-3 py-2">
                    <option value="Pending">Pending</option>
                    <option value="In Transit">In Transit</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-gray-700">Notes</label>
                <textarea name="notes" class="w-full border rounded px-3 py-2" rows="2"></textarea>
            </div>
            <div class="sm:col-span-2 flex justify-end gap-2 mt-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

<script>
const apiUrl = "http://localhost/logistics1-ecommerce/api/shipments.php";
const shipmentsGrid = document.getElementById("shipmentsGrid");
const modal = document.getElementById("modal");
const openModalBtn = document.getElementById("openModal");
const closeModalBtn = document.getElementById("closeModal");
const shipmentForm = document.getElementById("shipmentForm");

// Toast helper
function showToast(message, type="success") {
    Toastify({
        text: message,
        style: {
            background: type === "success" ?
                "linear-gradient(to right, #00b09b, #96c93d)" :
                "linear-gradient(to right, #ff5f6d, #ffc371)"
        },
        duration: 3000,
        close: true
    }).showToast();
}

// Modal toggle
openModalBtn.addEventListener("click", () => {
    shipmentForm.reset();
    shipmentForm.id.value = "";
    modal.classList.remove("hidden");
    modal.classList.add("flex");
});
closeModalBtn.addEventListener("click", () => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
});

// Status color helper
function getStatusClass(status) {
    switch(status) {
        case "Pending": return "text-yellow-600";
        case "In Transit": return "text-blue-600";
        case "Delivered": return "text-green-600";
        case "Cancelled": return "text-red-600";
        default: return "text-gray-600";
    }
}

// Fetch shipments
async function fetchShipments() {
    try {
        const res = await fetch(apiUrl);
        const shipments = await res.json();
        shipmentsGrid.innerHTML = "";

        shipments.forEach(s => {
            const card = document.createElement("div");
            const statusClass = getStatusClass(s.status);
            card.className = "bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer";
            card.innerHTML = `
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">${s.shipment_number}</h2>
                    <span class="${statusClass} text-sm font-semibold">${s.status}</span>
                </div>
                <p class="text-gray-600 mb-2">Origin: ${s.origin}</p>
                <p class="text-gray-600 mb-2">Destination: ${s.destination}</p>
                <p class="text-gray-600 mb-2">Items: ${s.items_quantity}</p>
                <p class="text-gray-600 mb-4">Dispatch Date: ${s.dispatch_date}</p>
                <div class="flex justify-end gap-3 mt-4">
                    <button class="text-blue-600 hover:text-blue-800 transition" title="Edit" onclick="editShipment(${s.id})">
                        <i class="bx bx-edit-alt text-lg"></i>
                    </button>
                    <button class="text-red-600 hover:text-red-800 transition" title="Delete" onclick="deleteShipment(${s.id})">
                        <i class="bx bx-trash text-lg"></i>
                    </button>
                </div>
            `;
            shipmentsGrid.appendChild(card);
        });
    } catch(err) {
        console.error("Error fetching shipments:", err);
    }
}

// Edit shipment
function editShipment(id) {
    fetch(`${apiUrl}?id=${id}`)
        .then(res => res.json())
        .then(s => {
            shipmentForm.id.value = s.id;
            shipmentForm.shipment_number.value = s.shipment_number;
            shipmentForm.origin.value = s.origin;
            shipmentForm.destination.value = s.destination;
            shipmentForm.items_quantity.value = s.items_quantity;
            shipmentForm.dispatch_date.value = s.dispatch_date;
            shipmentForm.status.value = s.status;
            shipmentForm.notes.value = s.notes;
            modal.classList.remove("hidden");
            modal.classList.add("flex");
        });
}

// Delete shipment
async function deleteShipment(id) {
    if(!confirm("Are you sure you want to delete this shipment?")) return;
    try {
        const res = await fetch(`${apiUrl}?id=${id}`, { method: "DELETE" });
        const result = await res.json();
        showToast(result.message);
        fetchShipments();
    } catch(err) {
        console.error(err);
    }
}

// Form submit (Add / Update)
shipmentForm.addEventListener("submit", async function(e){
    e.preventDefault();
    const id = this.id.value;
    const data = Object.fromEntries(new FormData(this).entries());

    const method = id ? "PUT" : "POST";
    const url = id ? `${apiUrl}?id=${id}` : apiUrl;

    try {
        const res = await fetch(url, {
            method: method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        showToast(result.message);
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        fetchShipments();
        this.reset();
    } catch(err) {
        console.error(err);
    }
});

// Initial fetch
fetchShipments();
</script>

<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
';

adminLayout($children);
?>
