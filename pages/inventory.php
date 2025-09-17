<?php
include '../layout/adminLayout.php';

$children = '
<div class="p-6">

    <!-- Breadcrumb -->
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Logistics Dashboard</a> &gt;
        <span>Inventory & Stock Control</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Inventory & Stock Control</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
            <i class="bx bx-plus mr-1"></i> Add New Item
        </button>
    </div>

    <!-- Inventory Cards Grid -->
    <div id="inventoryGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Items will be injected here via JS -->
    </div>

</div>

<!-- Modal Background -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50 px-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-xl p-6 relative">
        <h2 class="text-2xl font-bold mb-4" id="modalTitle">Add New Item</h2>
        <form id="addItemForm" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input type="hidden" name="id" id="itemId">
            <div>
                <label class="block text-gray-700">Item Name</label>
                <input type="text" name="item_name" id="item_name" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">SKU</label>
                <input type="text" name="sku" id="sku" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Category</label>
                <input type="text" name="category" id="category" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Stock Level</label>
                <input type="number" name="stock_level" id="stock_level" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Reorder Level</label>
                <input type="number" name="reorder_level" id="reorder_level" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Supplier</label>
                <input type="text" name="supplier" id="supplier" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Price (₱)</label>
                <input type="number" step="0.01" name="price" id="price" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Last Restocked</label>
                <input type="date" name="last_restocked" id="last_restocked" class="w-full border rounded px-3 py-2" required>
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
<script>
// Toast helper
function showToast(message, type) {
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
const modal = document.getElementById("modal");
const openModal = document.getElementById("openModal");
const closeModal = document.getElementById("closeModal");
const modalTitle = document.getElementById("modalTitle");
const itemIdField = document.getElementById("itemId");

openModal.addEventListener("click", () => {
    modalTitle.textContent = "Add New Item";
    itemIdField.value = "";
    document.getElementById("addItemForm").reset();
    modal.classList.remove("hidden");
    modal.classList.add("flex");
});

closeModal.addEventListener("click", () => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
});

// API endpoint
const apiUrl = "http://localhost/logistics1-ecommerce/api/inventory.php";
const inventoryGrid = document.getElementById("inventoryGrid");

// Fetch inventory and render
async function fetchInventory() {
    try {
        const res = await fetch(apiUrl);
        const items = await res.json();

        inventoryGrid.innerHTML = "";
        items.forEach(item => {
            const stockClass = item.stock_level >= 100 ? "text-green-600" :
                               item.stock_level >= 50 ? "text-yellow-600" : "text-red-600";

            const card = document.createElement("div");
            card.className = "bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer";
            card.innerHTML = `
                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-xl font-semibold">${item.item_name}</h2>
                    <span class="text-gray-500 text-sm">${item.sku}</span>
                </div>
                <div class="space-y-1 text-gray-600">
                    <p>Category: ${item.category}</p>
                    <p>Stock Level: ${item.stock_level} <span class="font-semibold ${stockClass}">(${item.stock_level >= 100 ? "High" : item.stock_level >= 50 ? "Medium" : "Low"})</span></p>
                    <p>Reorder Level: ${item.reorder_level}</p>
                    <p>Supplier: ${item.supplier}</p>
                    <p>Price: ₱${item.price}</p>
                    <p class="text-gray-500 text-sm">Last Restocked: ${item.last_restocked}</p>
                    <p class="text-gray-500 text-sm italic">Notes: ${item.notes}</p>
                </div>
                <div class="flex justify-end gap-3 mt-4">
                    <button class="text-blue-600 hover:text-blue-800 transition" title="Edit" onclick="editItem(${item.id})">
                        <i class="bx bx-edit-alt text-lg"></i>
                    </button>
                    <button class="text-red-600 hover:text-red-800 transition" title="Delete" onclick="deleteItem(${item.id})">
                        <i class="bx bx-trash text-lg"></i>
                    </button>
                </div>
            `;
            inventoryGrid.appendChild(card);
        });
    } catch (error) {
        console.error("Error fetching inventory:", error);
    }
}

// Delete item
async function deleteItem(id) {
    if(!confirm("Are you sure you want to delete this item?")) return;

    try {
        const res = await fetch(`${apiUrl}?id=${id}`, { method: "DELETE" });
        const result = await res.json();
        showToast(result.message, "success");
        fetchInventory(); // Refresh
    } catch (error) {
        showToast("Error deleting item", "error");
        console.error("Error deleting item:", error);
    }
}

// Edit item
async function editItem(id) {
    try {
        const res = await fetch(`${apiUrl}?id=${id}`);
        const item = await res.json();
        modalTitle.textContent = "Edit Item";
        itemIdField.value = item.id;
        document.getElementById("item_name").value = item.item_name;
        document.getElementById("sku").value = item.sku;
        document.getElementById("category").value = item.category;
        document.getElementById("stock_level").value = item.stock_level;
        document.getElementById("reorder_level").value = item.reorder_level;
        document.getElementById("supplier").value = item.supplier;
        document.getElementById("price").value = item.price;
        document.getElementById("last_restocked").value = item.last_restocked;
        document.getElementById("notes").value = item.notes;

        modal.classList.remove("hidden");
        modal.classList.add("flex");
    } catch (error) {
        showToast("Error fetching item data", "error");
        console.error("Error fetching item:", error);
    }
}

// Handle form submission (POST or PUT)
document.getElementById("addItemForm").addEventListener("submit", async function(e){
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    const id = data.id;
    const method = id ? "PUT" : "POST";

    try {
        const res = await fetch(id ? `${apiUrl}?id=${id}` : apiUrl, {
            method: method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        showToast(result.message, "success");
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        this.reset();
        fetchInventory(); // Refresh list
    } catch (error) {
        showToast("Error saving item", "error");
        console.error("Error adding/updating item:", error);
    }
});

// Initial fetch
fetchInventory();
</script>

<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
';

adminLayout($children);
?>
