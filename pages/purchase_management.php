<?php
session_start(); 

if (!isset($_SESSION['id'])) {
    header('Location:http://localhost/caplog1');
    exit();
}
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
    <!-- Breadcrumb -->
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Logistics Dashboard</a> &gt;
        <span>Purchase Management</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Purchase Management</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
            Add Purchase Request
        </button>
    </div>

    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Filter by Status:</label>
            <select id="filterStatus" class="w-full md:w-48 border rounded px-3 py-2">
                <option value="all">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
            </select>
            <label class="text-gray-700 font-medium whitespace-nowrap">Archive Status:</label>
            <select id="filterArchive" class="w-full md:w-48 border rounded px-3 py-2">
                <option value="all">All Items</option>
                <option value="active" selected>Active Only</option>
                <option value="archived">Archived Only</option>
            </select>
            <button id="applyFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Apply Filters</button>
            <button id="clearFilter" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
        </div>
    </div>

    <!-- Purchase Requests Cards Grid -->
    <div id="purchaseGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden bg-white p-8 rounded-lg shadow text-center text-gray-600">
        No purchase requests found.
    </div>
</div>

<!-- Modal Background -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Add Purchase Request</h2>
        <form id="purchaseForm" class="space-y-4">
            <input type="hidden" id="purchaseId" />
            <div>
                <label for="request_number" class="block text-gray-700">Request Number</label>
                <input id="request_number" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
                <label for="requested_by" class="block text-gray-700">Requested By</label>
                <input id="requested_by" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
                <label for="items_quantity" class="block text-gray-700">Items Quantity</label>
                <input id="items_quantity" type="number" min="0" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
                <label for="request_date" class="block text-gray-700">Request Date</label>
                <input id="request_date" type="date" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
                <label for="status" class="block text-gray-700">Status</label>
                <select id="status" class="w-full border rounded px-3 py-2">
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
(function(){
    const API_BASE = '../api/purchase_management.php';

    const modal = document.getElementById('modal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('purchaseForm');

    const inputId = document.getElementById('purchaseId');
    const inputRequestNumber = document.getElementById('request_number');
    const inputRequestedBy = document.getElementById('requested_by');
    const inputItemsQuantity = document.getElementById('items_quantity');
    const inputRequestDate = document.getElementById('request_date');
    const inputStatus = document.getElementById('status');

    const grid = document.getElementById('purchaseGrid');
    const emptyState = document.getElementById('emptyState');
    
    // Filter elements
    const filterStatus = document.getElementById('filterStatus');
    const filterArchive = document.getElementById('filterArchive');
    const applyFilter = document.getElementById('applyFilter');
    const clearFilter = document.getElementById('clearFilter');

    openModalBtn.addEventListener('click', () => {
        resetForm();
        modalTitle.textContent = 'Add Purchase Request';
        openModal();
    });

    closeModalBtn.addEventListener('click', closeModal);

    function openModal(){
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal(){
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function resetForm(){
        inputId.value = '';
        inputRequestNumber.value = '';
        inputRequestedBy.value = '';
        inputItemsQuantity.value = '';
        inputRequestDate.value = '';
        inputStatus.value = 'Pending';
    }

    function statusClasses(status){
        switch(status){
            case 'Approved': return 'text-green-600';
            case 'Rejected': return 'text-red-600';
            case 'Archived': return 'text-gray-500';
            default: return 'text-gray-500';
        }
    }

    function escapeHtml(str){
        if (typeof str !== 'string') return str;
        return str.replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s]));
    }

    async function fetchPurchases(){
        try {
            // Build query parameters based on current filters
            const params = new URLSearchParams();
            const statusFilter = filterStatus.value;
            const archiveFilter = filterArchive.value;
            
            if (statusFilter && statusFilter !== 'all') {
                params.append('status', statusFilter);
            }
            
            if (archiveFilter === 'active') {
                params.append('archived', '0');
            } else if (archiveFilter === 'archived') {
                params.append('archived', '1');
            }
            
            const url = API_BASE + (params.toString() ? '?' + params.toString() : '');
            const res = await fetch(url);
            const json = await res.json();
            if (!res.ok || json.status !== 'success') throw new Error(json.message || 'Failed to load');
            const data = Array.isArray(json.data) ? json.data : (json.data ? [json.data] : []);
            renderPurchases(data);
        } catch (err) {
            console.error(err);
            alert('Error loading purchase requests: ' + err.message);
        }
    }

    function renderPurchases(items){
        if (!items || items.length === 0){
            grid.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }
        emptyState.classList.add('hidden');
        grid.innerHTML = items.map(item => {
            const statusCls = statusClasses(item.status);
            const archived = !!item.archived_at;
            const archiveBtn = archived ? 
                `<button data-action="unarchive" data-id="${item.id}" class="text-blue-600 hover:underline">Restore</button>` : 
                `<button data-action="archive" data-id="${item.id}" class="text-red-600 hover:underline">Archive</button>`;
            const editBtn = archived ? '' : `<button data-action="edit" data-id="${item.id}" class="text-blue-600 hover:underline">Edit</button>`;
            return `
            <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition ${archived ? 'opacity-70' : ''}">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">${escapeHtml(item.request_number)}</h2>
                    <span class="${statusCls} text-sm">${escapeHtml(item.status)}${archived ? ' (Archived)' : ''}</span>
                </div>
                <p class="text-gray-600 mb-2">Requested By: ${escapeHtml(item.requested_by)}</p>
                <p class="text-gray-600 mb-2">Items: ${Number(item.items_quantity) || 0}</p>
                <p class="text-gray-600 mb-4">Request Date: ${escapeHtml(item.request_date)}</p>
                <div class="flex justify-end gap-2">${editBtn}${archiveBtn}</div>
            </div>`;
        }).join('');
    }

    grid.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const id = btn.getAttribute('data-id');
        const action = btn.getAttribute('data-action');
        
        if (action === 'edit') {
            try {
                const res = await fetch(`${API_BASE}?id=${encodeURIComponent(id)}`);
                const json = await res.json();
                if (!res.ok || json.status !== 'success') throw new Error(json.message || 'Failed to load record');
                const item = json.data;
                inputId.value = item.id;
                inputRequestNumber.value = item.request_number;
                inputRequestedBy.value = item.requested_by;
                inputItemsQuantity.value = item.items_quantity;
                inputRequestDate.value = item.request_date;
                inputStatus.value = ['Pending','Approved','Rejected'].includes(item.status) ? item.status : 'Pending';
                modalTitle.textContent = 'Edit Purchase Request';
                openModal();
            } catch (err) {
                console.error(err);
                alert('Error loading record: ' + err.message);
            }
        }
        
        if (action === 'archive') {
            if (!confirm('Archive this purchase request?')) return;
            try {
                const res = await fetch(`${API_BASE}?id=${encodeURIComponent(id)}&action=archive`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                });
                const json = await res.json();
                if (!res.ok || json.status !== 'success') throw new Error(json.message || 'Failed to archive');
                await fetchPurchases();
            } catch (err) {
                console.error(err);
                alert('Error archiving: ' + err.message);
            }
        }
        
        if (action === 'unarchive') {
            if (!confirm('Restore this purchase request?')) return;
            try {
                const res = await fetch(`${API_BASE}?id=${encodeURIComponent(id)}&action=unarchive`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ restore_status: 'Pending' })
                });
                const json = await res.json();
                if (!res.ok || json.status !== 'success') throw new Error(json.message || 'Failed to restore');
                await fetchPurchases();
            } catch (err) {
                console.error(err);
                alert('Error restoring: ' + err.message);
            }
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            request_number: inputRequestNumber.value.trim(),
            requested_by: inputRequestedBy.value.trim(),
            items_quantity: Number(inputItemsQuantity.value),
            request_date: inputRequestDate.value,
            status: inputStatus.value
        };

        if (!payload.request_number || !payload.requested_by || !payload.request_date || isNaN(payload.items_quantity)){
            alert('Please fill in all required fields.');
            return;
        }

        const id = inputId.value;
        const isEdit = !!id;
        try {
            const url = isEdit ? `${API_BASE}?id=${encodeURIComponent(id)}` : API_BASE;
            const method = isEdit ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            if (!res.ok || json.status !== 'success') throw new Error(json.message || 'Failed to save');
            closeModal();
            await fetchPurchases();
        } catch (err) {
            console.error(err);
            alert('Error saving: ' + err.message);
        }
    });
    
    // Filter event listeners
    applyFilter.addEventListener('click', () => fetchPurchases());
    clearFilter.addEventListener('click', () => {
        filterStatus.value = 'all';
        filterArchive.value = 'active';
        fetchPurchases();
    });

    // Initial load with active filter
    filterArchive.value = 'active';
    fetchPurchases();
})();
</script>
HTML;

adminLayout($children);
?>
