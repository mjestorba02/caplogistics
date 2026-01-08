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
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Add Asset</button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Search by Item Number:</label>
            <input id="searchItem" type="text" placeholder="Enter item number..." class="w-full md:w-48 border rounded px-3 py-2" />
            <label class="text-gray-700 font-medium whitespace-nowrap">Status:</label>
            <select id="searchStatus" class="w-full md:w-48 border rounded px-3 py-2">
                <option value="">All</option>
                <option value="Release">Release</option>
                <option value="InTransit">InTransit</option>
                <option value="Pending">Pending</option>
            </select>
            <button id="applySearch" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Search</button>
            <button id="clearSearch" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Item Number</th>
                    <th class="px-6 py-3">QR Code</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Description</th>
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
                <label class="block text-gray-700 font-medium">QR Code</label>
                <input id="qr_code" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., QR001" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Status</label>
                <select id="status" class="w-full border rounded px-3 py-2">
                    <option value="Pending">Pending</option>
                    <option value="InTransit">InTransit</option>
                    <option value="Release">Release</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Description</label>
                <textarea id="description" class="w-full border rounded px-3 py-2" rows="3" placeholder="Asset description"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Asset</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const closeBtn = document.getElementById('closeModalBtn');
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

    openModalBtn.addEventListener('click', openModal);
    closeModalBtn.addEventListener('click', closeModal);
    closeBtn.addEventListener('click', closeModal);

    async function fetchAssets() {
        try {
            const itemNumber = document.getElementById('searchItem').value;
            const status = document.getElementById('searchStatus').value;

            const params = new URLSearchParams();
            if (itemNumber) params.append('item_number', itemNumber);
            if (status) params.append('status', status);

            const url = `../api/asset_management.php?${params.toString()}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.status === 'success' && data.assets.length) {
                renderAssets(data.assets);
            } else {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            console.error(err);
            Toastify({
                text: 'Error loading assets',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444'
            }).showToast();
        }
    }

    function renderAssets(assets) {
        emptyState.classList.add('hidden');
        tableBody.innerHTML = assets.map(a => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-3">${a.id}</td>
                <td class="px-6 py-3">${a.item_number}</td>
                <td class="px-6 py-3">${a.qr_code || 'N/A'}</td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${a.status === 'Release' ? 'bg-green-100 text-green-800' : a.status === 'InTransit' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'}">${a.status}</span></td>
                <td class="px-6 py-3">${a.description || ''}</td>
                <td class="px-6 py-3 flex gap-2"><button onclick='editAsset(${JSON.stringify(a).replace(/"/g, '&quot;')})' class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Edit</button><button onclick="deleteAsset(${a.id})" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Delete</button></td>
            </tr>
        `).join('');
    }

    function editAsset(asset) {
        document.getElementById('modalTitle').textContent = 'Edit Asset';
        document.getElementById('assetId').value = asset.id;
        document.getElementById('item_number').value = asset.item_number;
        document.getElementById('qr_code').value = asset.qr_code || '';
        document.getElementById('status').value = asset.status;
        document.getElementById('description').value = asset.description || '';
        openModal();
    }

    async function deleteAsset(id) {
        if (!confirm('Delete this asset?')) return;
        try {
            const res = await fetch('../api/asset_management.php', { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({ text: 'Asset deleted', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                fetchAssets();
            } else throw new Error(data.message || 'Delete failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error deleting asset', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const assetId = document.getElementById('assetId').value;
        const payload = {
            id: assetId || undefined,
            item_number: document.getElementById('item_number').value,
            qr_code: document.getElementById('qr_code').value,
            status: document.getElementById('status').value,
            description: document.getElementById('description').value
        };
        try {
            const method = assetId ? 'PUT' : 'POST';
            const res = await fetch('../api/asset_management.php', { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({ text: result.message || 'Saved', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                closeModal();
                fetchAssets();
            } else throw new Error(result.message || 'Save failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error saving asset', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    });

    applySearchBtn.addEventListener('click', fetchAssets);
    clearSearchBtn.addEventListener('click', () => {
        document.getElementById('searchItem').value = '';
        document.getElementById('searchStatus').value = '';
        fetchAssets();
    });

    window.deleteAsset = deleteAsset;
    window.editAsset = editAsset;

    fetchAssets();
});
</script>
HTML;
adminLayout($children);
?>