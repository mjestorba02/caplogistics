<?php
include '../layout/adminLayout.php';
session_start ();
if (!isset($_SESSION['id'])) {
    header('Location:http://localhost/caplog1');
    exit();
}
$children = '
<div class="p-6">

    <!-- Breadcrumb -->
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Logistics Dashboard</a> &gt;
        <span>Inventory & Stock Control</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-2 md:mb-0">Inventory & Stock Control</h1>
        <div class="flex flex-wrap items-center gap-3">
            <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                <i class="bx bx-plus mr-1"></i> Add New Item
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-gray-700 text-sm mb-1">Category</label>
                <select id="filterCategory" class="w-full border rounded px-3 py-2">
                    <option value="">All Categories</option>
                    <!-- Categories will be populated dynamically -->
                </select>
            </div>
            <div>
                <label class="block text-gray-700 text-sm mb-1">Stock Status</label>
                <select id="filterStock" class="w-full border rounded px-3 py-2">
                    <option value="">All Stock Levels</option>
                    <option value="low">Low Stock</option>
                    <option value="medium">Medium Stock</option>
                    <option value="high">High Stock</option>
                    <option value="critical">Critical (Below Reorder)</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 text-sm mb-1">Search</label>
                <input id="filterSearch" type="text" class="w-full border rounded px-3 py-2" placeholder="Search items...">
            </div>
        </div>
        <div class="mt-4 flex justify-end gap-3">
            <button id="applyFilters" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Apply Filters</button>
            <button id="clearFilters" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">Clear Filters</button>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Item Name</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">SKU</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Category</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Stock Level</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Reorder Level</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Supplier</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Price</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Last Restocked</th>
                    <th class="py-3 px-6 text-center text-sm font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody id="inventory-tbody" class="divide-y divide-gray-200">
                <tr>
                    <td class="py-4 px-6 text-gray-500" colspan="9">Loading inventory...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View (hidden on larger screens) -->
    <div id="inventory-cards" class="md:hidden flex flex-col gap-4 mt-6"></div>

</div>

<!-- Modal Background -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50 px-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-xl p-6 relative overflow-y-auto">
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
            <!-- Add this to your form in the modal -->
            <div class="sm:col-span-2">
                <label class="block text-gray-700">Product Photo</label>
                <input type="file" name="product_photo" id="product_photo" accept="image/*" class="w-full border rounded px-3 py-2">
                <div id="photoPreview" class="mt-2 hidden">
                    <img id="previewImage" src="" class="h-32 object-cover rounded border">
                </div>
            </div>
            <div class="sm:col-span-2 flex justify-end gap-2 mt-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>


<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
';

adminLayout($children);
?>
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

// API endpoint
const apiUrl = "http://localhost/caplog1/api/inventory.php";
let inventoryItems = [];
let filteredItems = [];
let categories = [];

// DOM elements
const modal = document.getElementById("modal");
const openModalBtn = document.getElementById("openModal");
const closeModalBtn = document.getElementById("closeModal");
const modalTitle = document.getElementById("modalTitle");
const itemIdField = document.getElementById("itemId");
const tbody = document.getElementById("inventory-tbody");
const cards = document.getElementById("inventory-cards");
const refreshBtn = document.getElementById("refreshBtn");
const exportBtn = document.getElementById("exportBtn");

// Filter elements
const filterCategory = document.getElementById("filterCategory");
const filterStock = document.getElementById("filterStock");
const filterSearch = document.getElementById("filterSearch");
const applyFilters = document.getElementById("applyFilters");
const clearFilters = document.getElementById("clearFilters");

// Modal toggle
openModalBtn.addEventListener("click", () => {
    modalTitle.textContent = "Add New Item";
    itemIdField.value = "";
    document.getElementById("addItemForm").reset();
    modal.classList.remove("hidden");
    modal.classList.add("flex");
});

closeModalBtn.addEventListener("click", () => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
});

// Get stock status class
function getStockStatusClass(stockLevel, reorderLevel) {
    if (stockLevel <= reorderLevel) return "text-red-600 bg-red-100";
    if (stockLevel <= reorderLevel * 1.5) return "text-yellow-600 bg-yellow-100";
    if (stockLevel <= reorderLevel * 3) return "text-blue-600 bg-blue-100";
    return "text-green-600 bg-green-100";
}

// Get stock status text
function getStockStatusText(stockLevel, reorderLevel) {
    if (stockLevel <= reorderLevel) return "Critical";
    if (stockLevel <= reorderLevel * 1.5) return "Low";
    if (stockLevel <= reorderLevel * 3) return "Medium";
    return "High";
}

// Render inventory items
function renderInventory() {
    const itemsToRender = filteredItems.length > 0 ? filteredItems : inventoryItems;
    
    // Table view
    if (!itemsToRender.length) {
        tbody.innerHTML = `<tr><td class="py-4 px-6 text-gray-500" colspan="9">No items found</td></tr>`;
    } else {
        tbody.innerHTML = itemsToRender.map(item => {
            const stockStatusClass = getStockStatusClass(item.stock_level, item.reorder_level);
            const stockStatusText = getStockStatusText(item.stock_level, item.reorder_level);
            
            return `
                <tr class="hover:bg-gray-50">
                    <td class="py-4 px-6 font-medium text-gray-900">${escapeHtml(item.item_name)}</td>
                    <td class="py-4 px-6 text-gray-700">${escapeHtml(item.sku)}</td>
                    <td class="py-4 px-6 text-gray-700">${escapeHtml(item.category)}</td>
                    <td class="py-4 px-6">
                        <span class="px-2 py-1 rounded-full text-xs font-medium ${stockStatusClass}">
                            ${item.stock_level} (${stockStatusText})
                        </span>
                    </td>
                    <td class="py-4 px-6 text-gray-700">${escapeHtml(item.reorder_level)}</td>
                    <td class="py-4 px-6 text-gray-700">${escapeHtml(item.supplier)}</td>
                    <td class="py-4 px-6 text-gray-700">₱${escapeHtml(item.price)}</td>
                    <td class="py-4 px-6 text-gray-700">${escapeHtml(item.last_restocked)}</td>
                    <td class="py-4 px-6 text-center">
                        <button class="text-blue-600 hover:text-blue-800 mr-3" title="Edit" onclick="editItem(${item.id})">
                            <i class="bx bx-edit-alt"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-800" title="Delete" onclick="deleteItem(${item.id})">
                            <i class="bx bx-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Mobile card view
    cards.innerHTML = itemsToRender.map(item => {
        const stockStatusClass = getStockStatusClass(item.stock_level, item.reorder_level);
        const stockStatusText = getStockStatusText(item.stock_level, item.reorder_level);
        
        return `
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-lg">${escapeHtml(item.item_name)}</h3>
                    <span class="text-gray-500 text-sm">${escapeHtml(item.sku)}</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div><span class="font-medium">Category:</span> ${escapeHtml(item.category)}</div>
                    <div><span class="font-medium">Stock:</span> 
                        <span class="px-2 py-1 rounded-full text-xs ${stockStatusClass}">
                            ${item.stock_level} (${stockStatusText})
                        </span>
                    </div>
                    <div><span class="font-medium">Reorder:</span> ${escapeHtml(item.reorder_level)}</div>
                    <div><span class="font-medium">Supplier:</span> ${escapeHtml(item.supplier)}</div>
                    <div><span class="font-medium">Price:</span> ₱${escapeHtml(item.price)}</div>
                    <div><span class="font-medium">Last Restock:</span> ${escapeHtml(item.last_restocked)}</div>
                </div>
                <div class="flex justify-end gap-3 mt-3">
                    <button class="text-blue-600 hover:text-blue-800" onclick="editItem(${item.id})">
                        <i class="bx bx-edit-alt"></i> Edit
                    </button>
                    <button class="text-red-600 hover:text-red-800" onclick="deleteItem(${item.id})">
                        <i class="bx bx-trash"></i> Delete
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// Apply filters
function applyFiltersToData() {
    const categoryFilter = filterCategory.value;
    const stockFilter = filterStock.value;
    const searchFilter = filterSearch.value.toLowerCase();
    
    filteredItems = inventoryItems.filter(item => {
        // Category filter
        if (categoryFilter && item.category !== categoryFilter) {
            return false;
        }
        
        // Stock status filter
        if (stockFilter) {
            const stockLevel = item.stock_level;
            const reorderLevel = item.reorder_level;
            
            if (stockFilter === 'critical' && stockLevel > reorderLevel) return false;
            if (stockFilter === 'low' && (stockLevel <= reorderLevel || stockLevel > reorderLevel * 1.5)) return false;
            if (stockFilter === 'medium' && (stockLevel <= reorderLevel * 1.5 || stockLevel > reorderLevel * 3)) return false;
            if (stockFilter === 'high' && stockLevel <= reorderLevel * 3) return false;
        }
        
        // Search filter
        if (searchFilter && 
            !item.item_name.toLowerCase().includes(searchFilter) &&
            !item.sku.toLowerCase().includes(searchFilter) &&
            !item.category.toLowerCase().includes(searchFilter) &&
            !item.supplier.toLowerCase().includes(searchFilter)) {
            return false;
        }
        
        return true;
    });
    
    renderInventory();
}

// Clear all filters
function clearAllFilters() {
    filterCategory.value = '';
    filterStock.value = '';
    filterSearch.value = '';
    filteredItems = [];
    renderInventory();
}

// Populate categories dropdown
function populateCategories() {
    const uniqueCategories = [...new Set(inventoryItems.map(item => item.category))].sort();
    categories = uniqueCategories;
    
    filterCategory.innerHTML = '<option value="">All Categories</option>';
    uniqueCategories.forEach(category => {
        filterCategory.innerHTML += `<option value="${escapeHtml(category)}">${escapeHtml(category)}</option>`;
    });
}

// Escape HTML to prevent XSS
function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m]));
}

// Fetch inventory
async function fetchInventory() {
    try {
        tbody.innerHTML = '<tr><td class="py-4 px-6 text-gray-500" colspan="9">Loading inventory...</td></tr>';
        const res = await fetch(apiUrl);
        inventoryItems = await res.json();
        
        if (!Array.isArray(inventoryItems)) {
            throw new Error('Invalid data format received from server');
        }
        
        populateCategories();
        filteredItems = [];
        renderInventory();
    } catch (error) {
        console.error("Error fetching inventory:", error);
        tbody.innerHTML = '<tr><td class="py-4 px-6 text-red-600" colspan="9">Error loading inventory</td></tr>';
        showToast("Error loading inventory", "error");
    }
}

// Delete item
async function deleteItem(id) {
    if(!confirm("Are you sure you want to delete this item?")) return;

    try {
        const res = await fetch(`${apiUrl}?id=${id}`, { method: "DELETE" });
        const result = await res.json();
        showToast(result.message, "success");
        fetchInventory();
    } catch (error) {
        showToast("Error deleting item", "error");
        console.error("Error deleting item:", error);
    }
}

// Edit item
async function editItem(id) {
    try {
        const res = await fetch(`${apiUrl}?id=${id}`);
        
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        
        const item = await res.json();
        
        if (item.error) {
            showToast(item.error, "error");
            return;
        }
        
        modalTitle.textContent = "Edit Item";
        document.getElementById("itemId").value = item.id;
        document.getElementById("item_name").value = item.item_name || '';
        document.getElementById("sku").value = item.sku || '';
        document.getElementById("category").value = item.category || '';
        document.getElementById("stock_level").value = item.stock_level || 0;
        document.getElementById("reorder_level").value = item.reorder_level || 0;
        document.getElementById("supplier").value = item.supplier || '';
        document.getElementById("price").value = item.price || 0.00;
        document.getElementById("last_restocked").value = item.last_restocked || '';
        document.getElementById("notes").value = item.notes || '';
        
        // Show current photo if exists
        const preview = document.getElementById('photoPreview');
        const previewImage = document.getElementById('previewImage');
        const fileInput = document.getElementById('product_photo');
        
        if (item.product_photo_url && item.product_photo_url !== 'http://localhost/caplog1/uploads/products/') {
            previewImage.src = item.product_photo_url;
            preview.classList.remove('hidden');
            // Clear the file input so we don't accidentally overwrite the existing photo
            fileInput.value = '';
        } else {
            preview.classList.add('hidden');
        }

        modal.classList.remove("hidden");
        modal.classList.add("flex");
    } catch (error) {
        showToast("Error fetching item data: " + error.message, "error");
        console.error("Error fetching item:", error);
    }
}

// Export to CSV
function exportCSV() {
    const itemsToExport = filteredItems.length > 0 ? filteredItems : inventoryItems;
    
    if (!itemsToExport.length) {
        showToast("No data to export", "error");
        return;
    }
    
    const headers = ['Item Name', 'SKU', 'Category', 'Stock Level', 'Reorder Level', 'Supplier', 'Price', 'Last Restocked', 'Notes'];
    const rows = itemsToExport.map(item => [
        item.item_name,
        item.sku,
        item.category,
        item.stock_level,
        item.reorder_level,
        item.supplier,
        item.price,
        item.last_restocked,
        item.notes || ''
    ]);
    
    const csvContent = [headers, ...rows]
        .map(row => row.map(cell => `"${String(cell || '').replace(/"/g, '""')}"`).join(','))
        .join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'inventory_export.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

document.getElementById("addItemForm").addEventListener("submit", async function(e){
    e.preventDefault();
    
    const id = document.getElementById("itemId").value;
    const formData = new FormData(this);
    
    // Always use POST, but include the ID for updates
    if (id) {
        formData.append('id', id);
    }

    try {
        const res = await fetch(apiUrl, {
            method: "POST",
            body: formData
        });
        
        if (!res.ok) {
            const errorText = await res.text();
            throw new Error(`HTTP error! status: ${res.status}, message: ${errorText}`);
        }
        
        const result = await res.json();
        
        if (result.error) {
            showToast(result.error, "error");
        } else {
            showToast(result.message, "success");
            modal.classList.add("hidden");
            modal.classList.remove("flex");
            
            // Only reset the form for new items, not for edits
            if (!id) {
                this.reset();
                document.getElementById('photoPreview').classList.add('hidden');
            }
            
            fetchInventory();
        }
    } catch (error) {
        showToast("Error saving item: " + error.message, "error");
        console.error("Error adding/updating item:", error);
    }
});

// Event listeners
applyFilters.addEventListener("click", applyFiltersToData);
clearFilters.addEventListener("click", clearAllFilters);
refreshBtn.addEventListener("click", fetchInventory);
exportBtn.addEventListener("click", exportCSV);

// Search on enter key
filterSearch.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
        applyFiltersToData();
    }
});

document.getElementById('product_photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('photoPreview');
    const previewImage = document.getElementById('previewImage');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
});

// Initial load
fetchInventory();
</script>