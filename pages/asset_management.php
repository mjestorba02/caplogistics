<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location:../index.php');
    exit();
}
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <span>Asset Management</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Asset Management</h1>
        <div class="flex gap-3">
            <a href="asset_maintenance.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition flex items-center gap-2">
                <i class='bx bx-wrench'></i> View Maintenance
            </a>
            <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Add Asset</button>
        </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Search by Item Number:</label>
            <input id="searchItem" type="text" placeholder="Enter item number..." class="w-full md:w-48 border rounded px-3 py-2" />
            <label class="text-gray-700 font-medium whitespace-nowrap">Status:</label>
            <select id="searchStatus" class="w-full md:w-48 border rounded px-3 py-2">
                <option value="">All</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="Maintenance">Maintenance</option>
                <option value="Retired">Retired</option>
            </select>
            <button id="applySearch" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Search</button>
            <button id="clearSearch" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">Item Number</th>
                    <th class="px-6 py-3">Image</th>
                    <th class="px-6 py-3">Type of Asset</th>
                    <th class="px-6 py-3">Item Code</th>
                    <th class="px-6 py-3">Item Name</th>
                    <th class="px-6 py-3">Quality</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="assetsTable"></tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">No assets found</div>
    </div>
</div>

<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-[100] overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative my-8">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Add Asset</h2>
        <form id="assetForm" class="space-y-4">
            <input type="hidden" id="assetId" />
            <div>
                <label class="block text-gray-700 font-medium">Item Number *</label>
                <input id="item_number" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., ITEM001" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Image</label>
                <input id="image" type="file" accept="image/*" class="w-full border rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Type of Asset</label>
                <input id="type_of_asset" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., Computer, Equipment" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Item Code</label>
                <input id="item_code" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., CODE001" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Item Name</label>
                <input id="item_name" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., Dell Desktop" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Purchase Date</label>
                <input id="purchase_date" type="date" class="w-full border rounded px-3 py-2" />
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Lifespan (Years)</label>
                <input id="lifespan_years" type="number" min="1" value="5"
                    class="w-full border rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Status</label>
                <select id="status" class="w-full border rounded px-3 py-2">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Retired">Retired</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Asset</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-[100]">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
        <h2 class="text-2xl font-bold mb-4">Asset Image - <span id="imageName"></span></h2>
        <img id="modalImage" src="" alt="Asset Image" class="w-full max-h-96 object-contain rounded">
        <button id="closeImageModal" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const imageModal = document.getElementById('imageModal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const closeImageModalBtn = document.getElementById('closeImageModal');
    const form = document.getElementById('assetForm');
    const tableBody = document.getElementById('assetsTable');
    const emptyState = document.getElementById('emptyState');
    const applySearchBtn = document.getElementById('applySearch');
    const clearSearchBtn = document.getElementById('clearSearch');

    function openModal() {
        document.getElementById('modalTitle').textContent = 'Add Asset';
        document.getElementById('assetId').value = '';
        form.reset();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        form.reset();
    }

    function openImageModal(imageSrc, itemName) {
        if (!imageSrc) {
            alert('No image available for this asset');
            return;
        }
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageName').textContent = itemName;
        imageModal.classList.remove('hidden');
        imageModal.classList.add('flex');
    }

    function closeImageModal() {
        imageModal.classList.add('hidden');
        imageModal.classList.remove('flex');
    }

    openModalBtn.addEventListener('click', openModal);
    closeModalBtn.addEventListener('click', closeModal);
    closeBtn.addEventListener('click', closeModal);
    closeImageModalBtn.addEventListener('click', closeImageModal);

    async function fetchAssets() {
        try {
            const itemNumber = document.getElementById('searchItem').value;
            const status = document.getElementById('searchStatus').value;

            const params = new URLSearchParams();
            if (itemNumber) params.append('item_number', itemNumber);
            if (status) params.append('status', status);

            const url = `../api/asset_management.php${params.toString() ? '?' + params.toString() : ''}`;
            
            const res = await fetch(url);
            
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            
            const data = await res.json();
            
            if (data.status === 'success' && data.assets && data.assets.length > 0) {
                renderAssets(data.assets);
            } else {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            Toastify({
                text: 'Error loading assets: ' + err.message,
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444'
            }).showToast();
        }
    }

    function renderAssets(assets) {
        if (!assets || assets.length === 0) {
            tableBody.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }
        
        // Store assets for quick lookup
        window.assetMap = {};
        assets.forEach(asset => {
            window.assetMap[asset.id] = asset;
        });
        
        emptyState.classList.add('hidden');
        tableBody.innerHTML = assets.map((a, idx) => {
            let imageSrc = a.image;
            if (imageSrc && imageSrc.includes('/caplog1/uploads/')) {
                imageSrc = '../uploads/' + imageSrc.split('/caplog1/uploads/')[1];
            }
            return `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-3 font-semibold">${a.item_number}</td>
                <td class="px-6 py-3">
                    ${imageSrc ? `<button class="viewImageBtn bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600" data-image="${imageSrc}" data-name="${a.item_name}">View</button>` : '<span class="text-gray-500">N/A</span>'}
                </td>
                <td class="px-6 py-3">${a.type_of_asset || ''}</td>
                <td class="px-6 py-3 text-sm">${a.item_code || ''}</td>
                <td class="px-6 py-3">${a.item_name || ''}</td>
                <td class="px-6 py-3">
                    <div class="w-full bg-gray-200 rounded h-3">
                        <div 
                            class="h-3 rounded 
                            ${a.quality_percent > 70 ? 'bg-green-500' : 
                            a.quality_percent > 40 ? 'bg-yellow-500' : 'bg-red-500'}"
                            style="width:${a.quality_percent}%">
                        </div>
                    </div>
                    <span class="text-xs text-gray-600">${a.quality_percent}%</span>
                </td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${a.status === 'Active' ? 'bg-green-100 text-green-800' : a.status === 'Inactive' ? 'bg-gray-100 text-gray-800' : a.status === 'Maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">${a.status}</span></td>
                <td class="px-6 py-3 text-sm">${new Date(a.date).toLocaleDateString()}</td>
                <td class="px-6 py-3 flex gap-1">
                    <button class="bg-indigo-600 text-white px-2 py-1 rounded text-xs hover:bg-indigo-700 editBtn" data-asset-id="${a.id}" title="Edit">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button class="bg-orange-600 text-white px-2 py-1 rounded text-xs hover:bg-orange-700 archiveBtn" data-id="${a.id}" title="Archive">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    </button>
                </td>
            </tr>
        `;
        }).join('');

        tableBody.querySelectorAll('.viewImageBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                openImageModal(this.dataset.image, this.dataset.name);
            });
        });

        tableBody.querySelectorAll('.editBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const assetId = this.dataset.assetId;
                const asset = window.assetMap[assetId];
                if (asset) {
                    editAsset(asset);
                } else {
                    alert('Asset not found');
                }
            });
        });

        tableBody.querySelectorAll('.archiveBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                archiveAsset(parseInt(this.dataset.id));
            });
        });
    }

    function editAsset(asset) {
        document.getElementById('modalTitle').textContent = 'Edit Asset';
        document.getElementById('assetId').value = asset.id;
        document.getElementById('item_number').value = asset.item_number;
        document.getElementById('type_of_asset').value = asset.type_of_asset || '';
        document.getElementById('item_code').value = asset.item_code || '';
        document.getElementById('item_name').value = asset.item_name || '';
        document.getElementById('purchase_date').value = asset.purchase_date || '';
        document.getElementById('lifespan_years').value = asset.lifespan_years || 5;
        document.getElementById('status').value = asset.status;
        openModal();
    }

    async function archiveAsset(id) {
        if (!confirm('Archive this asset?')) {
            return;
        }
        try {
            const res = await fetch('../api/archive_management.php', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' }, 
                body: JSON.stringify({ 
                    archive_type: 'asset_management', 
                    item_id: id, 
                    original_table: 'asset_management', 
                    reason: 'Archived from asset management' 
                }) 
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                Toastify({ text: 'Asset archived', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                fetchAssets();
            } else {
                throw new Error(data.message || 'Archive failed');
            }
        } catch (err) {
            Toastify({ text: 'Error archiving asset', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const assetId = document.getElementById('assetId').value;
        const formData = new FormData();
        
        const item_number = document.getElementById('item_number').value;
        const type_of_asset = document.getElementById('type_of_asset').value;
        const item_code = document.getElementById('item_code').value;
        const item_name = document.getElementById('item_name').value;
        const purchase_date = document.getElementById('purchase_date').value;
        const lifespan_years = document.getElementById('lifespan_years').value;
        const status = document.getElementById('status').value;
        
        formData.append('id', assetId || '');
        formData.append('item_number', item_number);
        formData.append('type_of_asset', type_of_asset);
        formData.append('item_code', item_code);
        formData.append('item_name', item_name);
        formData.append('purchase_date', purchase_date);
        formData.append('lifespan_years', lifespan_years);
        formData.append('status', status);
        
        const imageFile = document.getElementById('image').files[0];
        if (imageFile) {
            formData.append('image', imageFile);
        }
        
        try {
            const method = assetId ? 'PUT' : 'POST';
            
            const res = await fetch('../api/asset_management.php', { 
                method, 
                body: formData 
            });
            
            const result = await res.json();
            
            if (result.status === 'success') {
                Toastify({ text: result.message || 'Saved', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                closeModal();
                fetchAssets();
            } else {
                throw new Error(result.message || 'Save failed');
            }
        } catch (err) {
            Toastify({ text: 'Error saving asset', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    });

    applySearchBtn.addEventListener('click', () => {
        fetchAssets();
    });
    
    clearSearchBtn.addEventListener('click', () => {
        document.getElementById('searchItem').value = '';
        document.getElementById('searchStatus').value = '';
        fetchAssets();
    });

    window.archiveAsset = archiveAsset;
    window.editAsset = editAsset;

    fetchAssets();
});
</script>
HTML;
adminLayout($children);
?>