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
        <span>Suppliers & Bidding</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Suppliers & Bidding</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
            Add Supplier
        </button>
    </div>

    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Filter by Status:</label>
            <select id="filterStatus" class="w-full md:w-48 border rounded px-3 py-2">
                <option value="all">All Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
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

    <!-- Suppliers Cards Grid -->
    <div id="suppliersGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden bg-white p-8 rounded-lg shadow text-center text-gray-600">
        No suppliers found.
    </div>

</div>

<!-- Modal Background -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 mx-2 relative">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Add Supplier</h2>
        <form id="supplierForm" class="space-y-4">
            <input type="hidden" id="supplierId" />
            <div>
                <label for="name" class="block text-gray-700">Supplier Name</label>
                <input id="name" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
                <label for="email" class="block text-gray-700">Contact Email</label>
                <input id="email" type="email" class="w-full border rounded px-3 py-2" required />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="rfqs_sent" class="block text-gray-700">RFQs Sent</label>
                    <input id="rfqs_sent" type="number" min="0" class="w-full border rounded px-3 py-2" value="0" required />
                </div>
                <div>
                    <label for="bids_submitted" class="block text-gray-700">Bids Submitted</label>
                    <input id="bids_submitted" type="number" min="0" class="w-full border rounded px-3 py-2" value="0" required />
                </div>
            </div>
            <div>
                <label for="status" class="block text-gray-700">Status</label>
                <select id="status" class="w-full border rounded px-3 py-2">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
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
    const API_BASE = '../api/suppliers_bidding.php';

    const grid = document.getElementById('suppliersGrid');
    const emptyState = document.getElementById('emptyState');

    // Modal controls
    const modal = document.getElementById('modal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const modalTitle = document.getElementById('modalTitle');

    // Form controls
    const form = document.getElementById('supplierForm');
    const inputId = document.getElementById('supplierId');
    const inputName = document.getElementById('name');
    const inputEmail = document.getElementById('email');
    const inputRfqs = document.getElementById('rfqs_sent');
    const inputBids = document.getElementById('bids_submitted');
    const inputStatus = document.getElementById('status');
    
    // Filter elements
    const filterStatus = document.getElementById('filterStatus');
    const filterArchive = document.getElementById('filterArchive');
    const applyFilter = document.getElementById('applyFilter');
    const clearFilter = document.getElementById('clearFilter');

    function openModal(){ modal.classList.remove('hidden'); modal.classList.add('flex'); }
    function closeModal(){ modal.classList.add('hidden'); modal.classList.remove('flex'); }

    openModalBtn.addEventListener('click', () => {
        resetForm();
        modalTitle.textContent = 'Add Supplier';
        openModal();
    });
    document.getElementById('closeModal').addEventListener('click', closeModal);

    function resetForm(){
        inputId.value = '';
        inputName.value = '';
        inputEmail.value = '';
        inputRfqs.value = '0';
        inputBids.value = '0';
        inputStatus.value = 'Active';
    }

    function statusBadge(status){
        switch(status){
            case 'Active': return 'bg-green-100 text-green-800';
            case 'Inactive': return 'bg-red-100 text-red-800';
            case 'Archived': return 'bg-gray-100 text-gray-700';
            default: return 'bg-gray-100 text-gray-700';
        }
    }

    function escapeHtml(str){
        if (typeof str !== 'string') return str;
        return str.replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s]));
    }

    async function fetchSuppliers(){
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
            const items = Array.isArray(json.data) ? json.data : (json.data ? [json.data] : []);
            renderSuppliers(items);
        } catch (err) {
            console.error(err);
            alert('Error loading suppliers: ' + err.message);
        }
    }

    function renderSuppliers(items){
        if (!items || items.length === 0){
            grid.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }
        emptyState.classList.add('hidden');
        grid.innerHTML = items.map(item => {
            const badge = statusBadge(item.status);
            const archived = !!item.archived_at;
            const editBtn = archived ? '' : `<button data-action="edit" data-id="${item.id}" class="text-indigo-600 hover:underline font-medium">Edit</button>`;
            const archiveBtn = archived ? 
                `<button data-action="unarchive" data-id="${item.id}" class="text-blue-600 hover:underline font-medium">Restore</button>` : 
                `<button data-action="archive" data-id="${item.id}" class="text-red-600 hover:underline font-medium">Archive</button>`;
            return `
            <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition ${archived ? 'opacity-70' : ''}">
                <div class="flex justify-between mb-2">
                    <h2 class="font-semibold text-lg">${escapeHtml(item.name)}</h2>
                    <span class="px-2 py-1 rounded-full ${badge} text-xs font-semibold">${escapeHtml(item.status)}${archived ? ' (Archived)' : ''}</span>
                </div>
                <p class="text-gray-600 mb-1"><span class="font-semibold">Email:</span> ${escapeHtml(item.email)}</p>
                <p class="text-gray-600 mb-1"><span class="font-semibold">RFQs Sent:</span> ${Number(item.rfqs_sent) || 0}</p>
                <p class="text-gray-600 mb-2"><span class="font-semibold">Bids Submitted:</span> ${Number(item.bids_submitted) || 0}</p>
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
                inputName.value = item.name;
                inputEmail.value = item.email;
                inputRfqs.value = item.rfqs_sent;
                inputBids.value = item.bids_submitted;
                inputStatus.value = ['Active','Inactive'].includes(item.status) ? item.status : 'Active';
                modalTitle.textContent = 'Edit Supplier';
                openModal();
            } catch (err) {
                console.error(err);
                alert('Error loading record: ' + err.message);
            }
        }
        
        if (action === 'archive') {
            if (!confirm('Archive this supplier?')) return;
            try {
                const res = await fetch(`${API_BASE}?id=${encodeURIComponent(id)}&action=archive`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                });
                const json = await res.json();
                if (!res.ok || json.status !== 'success') throw new Error(json.message || 'Failed to archive');
                await fetchSuppliers();
            } catch (err) {
                console.error(err);
                alert('Error archiving: ' + err.message);
            }
        }
        
        if (action === 'unarchive') {
            if (!confirm('Restore this supplier?')) return;
            try {
                const res = await fetch(`${API_BASE}?id=${encodeURIComponent(id)}&action=unarchive`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ restore_status: 'Active' })
                });
                const json = await res.json();
                if (!res.ok || json.status !== 'success') throw new Error(json.message || 'Failed to restore');
                await fetchSuppliers();
            } catch (err) {
                console.error(err);
                alert('Error restoring: ' + err.message);
            }
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            name: inputName.value.trim(),
            email: inputEmail.value.trim(),
            rfqs_sent: Number(inputRfqs.value),
            bids_submitted: Number(inputBids.value),
            status: inputStatus.value
        };

        if (!payload.name || !payload.email || isNaN(payload.rfqs_sent) || isNaN(payload.bids_submitted)){
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
            await fetchSuppliers();
        } catch (err) {
            console.error(err);
            alert('Error saving: ' + err.message);
        }
    });
    
    // Filter event listeners
    applyFilter.addEventListener('click', () => fetchSuppliers());
    clearFilter.addEventListener('click', () => {
        filterStatus.value = 'all';
        filterArchive.value = 'active';
        fetchSuppliers();
    });

    // Initial load with active filter
    filterArchive.value = 'active';
    fetchSuppliers();
})();
</script>
HTML;

adminLayout($children);
?>
