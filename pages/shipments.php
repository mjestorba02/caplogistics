<?php
session_start(); 

if (!isset($_SESSION['id'])) {
    header('Location:https://log1.imarketph.com');
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
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-2 md:mb-0">Shipments & Movements</h1>
        <div class="flex flex-wrap items-center gap-3">
            <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                <i class="bx bx-plus mr-1"></i> Add New Shipment
            </button>
            <button id="refreshBtn" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800 transition">
                <i class="bx bx-refresh mr-1"></i> Refresh
            </button>
            <button id="exportBtn" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 transition">
                <i class="bx bx-export mr-1"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <h3 class="text-lg font-semibold mb-3">Filters</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-gray-700 text-sm mb-1">Status</label>
                <select id="filterStatus" class="w-full border rounded px-3 py-2">
                    <option value="">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="In Transit">In Transit</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 text-sm mb-1">Origin</label>
                <input id="filterOrigin" type="text" class="w-full border rounded px-3 py-2" placeholder="Filter by origin...">
            </div>
            <div>
                <label class="block text-gray-700 text-sm mb-1">Destination</label>
                <input id="filterDestination" type="text" class="w-full border rounded px-3 py-2" placeholder="Filter by destination...">
            </div>
            <div>
                <label class="block text-gray-700 text-sm mb-1">Date Range</label>
                <select id="filterDateRange" class="w-full border rounded px-3 py-2">
                    <option value="">All Dates</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="past">Past Shipments</option>
                </select>
            </div>
        </div>
        <div class="mt-4 flex justify-end gap-3">
            <button id="applyFilters" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Apply Filters</button>
            <button id="clearFilters" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">Clear Filters</button>
        </div>
    </div>

    <!-- Shipments Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Shipment #</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Origin</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Destination</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Items Qty</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Dispatch Date</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Status</th>
                    <th class="py-3 px-6 text-center text-sm font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody id="shipments-tbody" class="divide-y divide-gray-200">
                <tr>
                    <td class="py-4 px-6 text-gray-500" colspan="7">Loading shipments...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div id="shipments-cards" class="md:hidden flex flex-col gap-4 mt-6"></div>

</div>

<!-- Modal Background -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50 px-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
        <h2 class="text-2xl font-bold mb-4" id="modalTitle">Add / Edit Shipment</h2>
        <form id="shipmentForm" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input type="hidden" name="id" id="shipmentId">
            <div>
                <label class="block text-gray-700">Shipment Number</label>
                <input type="text" name="shipment_number" id="shipment_number" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Origin</label>
                <input type="text" name="origin" id="origin" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Destination</label>
                <input type="text" name="destination" id="destination" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Items Quantity</label>
                <input type="number" name="items_quantity" id="items_quantity" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Dispatch Date</label>
                <input type="date" name="dispatch_date" id="dispatch_date" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Status</label>
                <select name="status" id="status" class="w-full border rounded px-3 py-2">
                    <option value="Pending">Pending</option>
                    <option value="In Transit">In Transit</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-gray-700">Notes</label>
                <textarea name="notes" id="notes" class="w-full border rounded px-3 py-2" rows="2"></textarea>
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


<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
';

adminLayout($children);
?>

<script>
const apiUrl = "https://log1.imarketph.com/api/shipments.php";
let shipments = [];
let filteredShipments = [];

// DOM elements
const modal = document.getElementById("modal");
const openModalBtn = document.getElementById("openModal");
const closeModalBtn = document.getElementById("closeModal");
const shipmentForm = document.getElementById("shipmentForm");
const tbody = document.getElementById("shipments-tbody");
const cards = document.getElementById("shipments-cards");
const refreshBtn = document.getElementById("refreshBtn");
const exportBtn = document.getElementById("exportBtn");

// Filter elements
const filterStatus = document.getElementById("filterStatus");
const filterOrigin = document.getElementById("filterOrigin");
const filterDestination = document.getElementById("filterDestination");
const filterDateRange = document.getElementById("filterDateRange");
const applyFilters = document.getElementById("applyFilters");
const clearFilters = document.getElementById("clearFilters");

// Toast helper
function showToast(message, type = "success") {
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
    document.getElementById("modalTitle").textContent = "Add New Shipment";
    shipmentForm.reset();
    document.getElementById("shipmentId").value = "";
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
        case "Pending": return "bg-yellow-100 text-yellow-800";
        case "In Transit": return "bg-blue-100 text-blue-800";
        case "Delivered": return "bg-green-100 text-green-800";
        case "Cancelled": return "bg-red-100 text-red-800";
        default: return "bg-gray-100 text-gray-800";
    }
}

// Apply filters
function applyFiltersToData() {
    const statusFilter = filterStatus.value;
    const originFilter = filterOrigin.value.toLowerCase();
    const destinationFilter = filterDestination.value.toLowerCase();
    const dateRangeFilter = filterDateRange.value;
    
    const today = new Date();
    const startOfWeek = new Date(today);
    startOfWeek.setDate(today.getDate() - today.getDay());
    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    filteredShipments = shipments.filter(shipment => {
        // Status filter
        if (statusFilter && shipment.status !== statusFilter) {
            return false;
        }
        
        // Origin filter
        if (originFilter && !shipment.origin.toLowerCase().includes(originFilter)) {
            return false;
        }
        
        // Destination filter
        if (destinationFilter && !shipment.destination.toLowerCase().includes(destinationFilter)) {
            return false;
        }
        
        // Date range filter
        if (dateRangeFilter) {
            const dispatchDate = new Date(shipment.dispatch_date);
            
            switch(dateRangeFilter) {
                case "today":
                    if (dispatchDate.toDateString() !== today.toDateString()) return false;
                    break;
                case "week":
                    if (dispatchDate < startOfWeek) return false;
                    break;
                case "month":
                    if (dispatchDate < startOfMonth) return false;
                    break;
                case "upcoming":
                    if (dispatchDate < today) return false;
                    break;
                case "past":
                    if (dispatchDate >= today) return false;
                    break;
            }
        }
        
        return true;
    });
    
    renderShipments();
}

// Clear all filters
function clearAllFilters() {
    filterStatus.value = '';
    filterOrigin.value = '';
    filterDestination.value = '';
    filterDateRange.value = '';
    filteredShipments = [];
    renderShipments();
}

// Render shipments
function renderShipments() {
    const dataToRender = filteredShipments.length > 0 ? filteredShipments : shipments;
    
    // Table view
    if (!dataToRender.length) {
        tbody.innerHTML = `<tr><td class="py-4 px-6 text-gray-500" colspan="7">No shipments found</td></tr>`;
    } else {
        tbody.innerHTML = dataToRender.map(s => {
            const statusClass = getStatusClass(s.status);
            return `
                <tr class="hover:bg-gray-50">
                    <td class="py-4 px-6 font-medium text-gray-900">${escapeHtml(s.shipment_number)}</td>
                    <td class="py-4 px-6 text-gray-700">${escapeHtml(s.origin)}</td>
                    <td class="py-4 px-6 text-gray-700">${escapeHtml(s.destination)}</td>
                    <td class="py-4 px-6 text-gray-700">${escapeHtml(s.items_quantity)}</td>
                    <td class="py-4 px-6 text-gray-700">${escapeHtml(s.dispatch_date)}</td>
                    <td class="py-4 px-6">
                        <span class="px-2 py-1 rounded-full text-xs font-medium ${statusClass}">
                            ${escapeHtml(s.status)}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <button class="text-blue-600 hover:text-blue-800 mr-3" title="Edit" onclick="editShipment(${s.id})">
                            <i class="bx bx-edit-alt"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-800" title="Delete" onclick="deleteShipment(${s.id})">
                            <i class="bx bx-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }
    
    // Mobile card view
    cards.innerHTML = dataToRender.map(s => {
        const statusClass = getStatusClass(s.status);
        return `
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-lg">${escapeHtml(s.shipment_number)}</h3>
                    <span class="px-2 py-1 rounded-full text-xs ${statusClass}">
                        ${escapeHtml(s.status)}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div><span class="font-medium">Origin:</span> ${escapeHtml(s.origin)}</div>
                    <div><span class="font-medium">Destination:</span> ${escapeHtml(s.destination)}</div>
                    <div><span class="font-medium">Items:</span> ${escapeHtml(s.items_quantity)}</div>
                    <div><span class="font-medium">Dispatch:</span> ${escapeHtml(s.dispatch_date)}</div>
                </div>
                <div class="flex justify-end gap-3 mt-3">
                    <button class="text-blue-600 hover:text-blue-800" onclick="editShipment(${s.id})">
                        <i class="bx bx-edit-alt"></i> Edit
                    </button>
                    <button class="text-red-600 hover:text-red-800" onclick="deleteShipment(${s.id})">
                        <i class="bx bx-trash"></i> Delete
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// Escape HTML to prevent XSS
function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m]));
}

// Fetch shipments
async function fetchShipments() {
    try {
        tbody.innerHTML = '<tr><td class="py-4 px-6 text-gray-500" colspan="7">Loading shipments...</td></tr>';
        const res = await fetch(apiUrl);
        shipments = await res.json();
        
        if (!Array.isArray(shipments)) {
            throw new Error('Invalid data format received from server');
        }
        
        filteredShipments = [];
        renderShipments();
    } catch(err) {
        console.error("Error fetching shipments:", err);
        tbody.innerHTML = '<tr><td class="py-4 px-6 text-red-600" colspan="7">Error loading shipments</td></tr>';
        showToast("Error loading shipments", "error");
    }
}

// Edit shipment
async function editShipment(id) {
    try {
        const res = await fetch(`${apiUrl}?id=${id}`);
        const shipment = await res.json();
        
        document.getElementById("modalTitle").textContent = "Edit Shipment";
        document.getElementById("shipmentId").value = shipment.id;
        document.getElementById("shipment_number").value = shipment.shipment_number;
        document.getElementById("origin").value = shipment.origin;
        document.getElementById("destination").value = shipment.destination;
        document.getElementById("items_quantity").value = shipment.items_quantity;
        document.getElementById("dispatch_date").value = shipment.dispatch_date;
        document.getElementById("status").value = shipment.status;
        document.getElementById("notes").value = shipment.notes || '';
        
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    } catch(err) {
        console.error("Error fetching shipment:", err);
        showToast("Error fetching shipment data", "error");
    }
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
        console.error("Error deleting shipment:", err);
        showToast("Error deleting shipment", "error");
    }
}

// Export to CSV
function exportCSV() {
    const dataToExport = filteredShipments.length > 0 ? filteredShipments : shipments;
    
    if (!dataToExport.length) {
        showToast("No data to export", "error");
        return;
    }
    
    const headers = ['Shipment Number', 'Origin', 'Destination', 'Items Quantity', 'Dispatch Date', 'Status', 'Notes'];
    const rows = dataToExport.map(s => [
        s.shipment_number,
        s.origin,
        s.destination,
        s.items_quantity,
        s.dispatch_date,
        s.status,
        s.notes || ''
    ]);
    
    const csvContent = [headers, ...rows]
        .map(row => row.map(cell => `"${String(cell || '').replace(/"/g, '""')}"`).join(','))
        .join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'shipments_export.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// Form submit (Add / Update)
shipmentForm.addEventListener("submit", async function(e){
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    const id = data.id;
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
        console.error("Error saving shipment:", err);
        showToast("Error saving shipment", "error");
    }
});

// Event listeners
applyFilters.addEventListener("click", applyFiltersToData);
clearFilters.addEventListener("click", clearAllFilters);
refreshBtn.addEventListener("click", fetchShipments);
exportBtn.addEventListener("click", exportCSV);

// Search on enter key
[filterOrigin, filterDestination].forEach(input => {
    input.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            applyFiltersToData();
        }
    });
});

// Initial fetch
fetchShipments();
</script>