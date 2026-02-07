<?php
session_start(); 

if (!isset($_SESSION['id'])) {
    header('Location:https://log1.imarketph.com');
    exit();
}
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">

    <!-- Breadcrumb -->
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Logistics Dashboard</a> &gt;
        <span>Shipments & Deliveries</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-2 md:mb-0">Shipments & Deliveries</h1>
        <div class="flex flex-wrap items-center gap-3">
            <button id="addBtn" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition flex items-center gap-2">
                <i class="bx bx-plus text-lg"></i> Add Shipment
            </button>
            <button id="refreshBtn" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800 transition">Refresh</button>
            <button id="exportBtn" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 transition">Export CSV</button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Filter by Status:</label>
            <select id="filterStatus" class="w-full md:w-48 border rounded px-3 py-2">
                <option value="all">All Status</option>
                <option value="Pending">Pending</option>
                <option value="In Transit">In Transit</option>
                <option value="Delivered">Delivered</option>
                <option value="Cancelled">Cancelled</option>
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

    <!-- Responsive Table -->
    <div class="hidden md:block overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-max min-w-full table-auto divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Shipment #</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Origin</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Destination</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Status</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Dispatch Date</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Items Qty</th>
                    <th class="py-3 px-6 text-center text-sm font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody id="shipments-tbody" class="divide-y divide-gray-200">
                <tr>
                    <td class="py-4 px-6 text-gray-500" colspan="7">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Card layout for small screens -->
    <div id="shipments-cards" class="md:hidden flex flex-col gap-4"></div>

</div>

<!-- Modal: Add/Edit Shipment -->
<div id="modal" class="fixed inset-0 hidden justify-center items-center z-50">
  <div class="absolute inset-0 bg-black bg-opacity-50" data-close="overlay"></div>
  <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
    <div class="flex items-center justify-between mb-4">
      <h2 id="modalTitle" class="text-2xl font-bold">Add Shipment</h2>
      <button class="text-gray-500 hover:text-gray-700 text-2xl leading-none" data-close="button">&times;</button>
    </div>
    <form id="shipmentForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-gray-700 text-sm mb-1">Shipment Number<span class="text-red-600">*</span></label>
            <input id="f_shipment_number" type="text" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Origin<span class="text-red-600">*</span></label>
            <input id="f_origin" type="text" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Destination<span class="text-red-600">*</span></label>
            <input id="f_destination" type="text" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Status<span class="text-red-600">*</span></label>
            <select id="f_status" class="w-full border rounded px-3 py-2" required>
                <option value="Pending">Pending</option>
                <option value="In Transit">In Transit</option>
                <option value="Delivered">Delivered</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Dispatch Date<span class="text-red-600">*</span></label>
            <input id="f_dispatch_date" type="date" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Items Quantity<span class="text-red-600">*</span></label>
            <input id="f_items_quantity" type="number" min="0" class="w-full border rounded px-3 py-2" required />
        </div>
        <div class="md:col-span-2">
            <label class="block text-gray-700 text-sm mb-1">Notes</label>
            <textarea id="f_notes" class="w-full border rounded px-3 py-2" rows="3"></textarea>
        </div>
        <div class="md:col-span-2 flex justify-end gap-3 mt-1">
            <button type="button" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300" data-close="cancel">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
        </div>
    </form>
  </div>
</div>

<script>
(function(){
    const API_URL = 'https://log1.imarketph.com/api/shipments.php';
    let shipments = [];

    const tbody = document.getElementById('shipments-tbody');
    const cards = document.getElementById('shipments-cards');
    const addBtn = document.getElementById('addBtn');
    const refreshBtn = document.getElementById('refreshBtn');
    const exportBtn = document.getElementById('exportBtn');
    
    // Filter elements
    const filterStatus = document.getElementById('filterStatus');
    const filterArchive = document.getElementById('filterArchive');
    const applyFilter = document.getElementById('applyFilter');
    const clearFilter = document.getElementById('clearFilter');

    // Modal refs
    const modal = document.getElementById('modal');
    const modalTitle = document.getElementById('modalTitle');
    const shipmentForm = document.getElementById('shipmentForm');
    const f_shipment_number = document.getElementById('f_shipment_number');
    const f_origin = document.getElementById('f_origin');
    const f_destination = document.getElementById('f_destination');
    const f_status = document.getElementById('f_status');
    const f_dispatch_date = document.getElementById('f_dispatch_date');
    const f_items_quantity = document.getElementById('f_items_quantity');
    const f_notes = document.getElementById('f_notes');

    let isEditing = false;
    let editId = null;

    function openModal(mode, item = null){
        isEditing = mode === 'edit';
        editId = item?.id ?? null;
        modalTitle.textContent = isEditing ? 'Edit Shipment' : 'Add Shipment';

        f_shipment_number.value = item?.shipment_number || '';
        f_origin.value = item?.origin || '';
        f_destination.value = item?.destination || '';
        f_status.value = item?.status && ['Pending','In Transit','Delivered','Cancelled'].includes(item.status) ? item.status : 'Pending';
        f_dispatch_date.value = item?.dispatch_date || '';
        f_items_quantity.value = item?.items_quantity ?? '';
        f_notes.value = item?.notes || '';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal(){
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        shipmentForm.reset();
        isEditing = false;
        editId = null;
    }

    modal.addEventListener('click', (e) => { if(e.target?.dataset?.close) closeModal(); });
    document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });
    addBtn?.addEventListener('click', () => openModal('create'));

    function statusBadge(status){
        let cls = 'bg-gray-200 text-gray-800';
        if(status === 'Pending') cls = 'bg-yellow-200 text-yellow-800';
        else if(status === 'In Transit') cls = 'bg-blue-200 text-blue-800';
        else if(status === 'Delivered') cls = 'bg-green-200 text-green-800';
        else if(status === 'Cancelled') cls = 'bg-red-200 text-red-800';
        else if(status === 'Archived') cls = 'bg-gray-300 text-gray-700';
        return `<span class="px-2 py-1 rounded-full text-xs ${cls}">${status}</span>`;
    }

    function render(){
        // Table
        if(!shipments.length){
            tbody.innerHTML = `<tr><td class="py-4 px-6 text-gray-500" colspan="7">No shipments found</td></tr>`;
        } else {
            tbody.innerHTML = shipments.map(s => {
                const isArchived = s.status === 'Archived';
                return `
                <tr class="${isArchived ? 'opacity-70' : ''}">
                    <td class="py-4 px-6 text-gray-800">${escapeHtml(s.shipment_number)}</td>
                    <td class="py-4 px-6 text-gray-800">${escapeHtml(s.origin)}</td>
                    <td class="py-4 px-6 text-gray-800">${escapeHtml(s.destination)}</td>
                    <td class="py-4 px-6 text-gray-800">${statusBadge(escapeHtml(s.status || 'Pending'))}</td>
                    <td class="py-4 px-6 text-gray-800">${escapeHtml(s.dispatch_date)}</td>
                    <td class="py-4 px-6 text-gray-800">${Number(s.items_quantity || 0)}</td>
                    <td class="py-4 px-6 text-center">
                        <button class="text-blue-600 hover:underline mr-3" data-action="edit" data-id="${s.id}">Edit</button>
                        ${isArchived ? `<button class="text-blue-600 hover:underline" data-action="unarchive" data-id="${s.id}">Restore</button>` : `<button class="text-red-600 hover:underline" data-action="archive" data-id="${s.id}">Archive</button>`}
                    </td>
                </tr>
            `}).join('');
        }

        // Cards
        cards.innerHTML = shipments.map(s => {
            const isArchived = s.status === 'Archived';
            return `
            <div class="bg-white rounded-lg shadow p-4 ${isArchived ? 'opacity-70' : ''}">
                <div class="flex justify-between mb-2">
                    <h3 class="font-semibold">${escapeHtml(s.shipment_number)}</h3>
                    ${statusBadge(escapeHtml(s.status || 'Pending'))}
                </div>
                <p class="text-gray-600"><span class="font-semibold">Origin:</span> ${escapeHtml(s.origin)}</p>
                <p class="text-gray-600"><span class="font-semibold">Destination:</span> ${escapeHtml(s.destination)}</p>
                <p class="text-gray-600"><span class="font-semibold">Dispatch:</span> ${escapeHtml(s.dispatch_date)}</p>
                <p class="text-gray-600"><span class="font-semibold">Qty:</span> ${Number(s.items_quantity || 0)}</p>
                <div class="flex justify-end gap-4 mt-3">
                    <button class="text-blue-600 hover:underline" data-action="edit" data-id="${s.id}">Edit</button>
                    ${isArchived ? `<button class="text-blue-600 hover:underline" data-action="unarchive" data-id="${s.id}">Restore</button>` : `<button class="text-red-600 hover:underline" data-action="archive" data-id="${s.id}">Archive</button>`}
                </div>
            </div>
        `}).join('');
    }

    function escapeHtml(str){
        return String(str ?? '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m]));
    }

    // Replace the load() function with this client-side filtering approach
async function load(){
    try{
        tbody.innerHTML = `<tr><td class="py-4 px-6 text-gray-500" colspan="7">Loading...</td></tr>`;
        
        // Fetch all data
        const res = await fetch(API_URL, { credentials: 'include' });
        const data = await res.json();
        let allShipments = Array.isArray(data) ? data : [];
        
        // Apply client-side filtering
        const statusFilter = filterStatus.value;
        const archiveFilter = filterArchive.value;
        
        shipments = allShipments.filter(s => {
            // Status filter
            if (statusFilter !== 'all' && s.status !== statusFilter) {
                return false;
            }
            
            // Archive filter
            if (archiveFilter === 'active' && s.status === 'Archived') {
                return false;
            } else if (archiveFilter === 'archived' && s.status !== 'Archived') {
                return false;
            }
            
            return true;
        });
        
        render();
    }catch(err){
        console.error(err);
        tbody.innerHTML = `<tr><td class="py-4 px-6 text-red-600" colspan="7">Error loading</td></tr>`;
        cards.innerHTML = '';
    }
}

    async function createShipment(payload){
        const res = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if(!res.ok) throw new Error(data?.error || 'Create failed');
        return data;
    }

    async function updateShipment(id, payload){
        const res = await fetch(`${API_URL}?id=${encodeURIComponent(id)}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if(!res.ok) throw new Error(data?.error || 'Update failed');
        return data;
    }

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-action]');
        if(!btn) return;
        const action = btn.dataset.action;
        const id = btn.dataset.id;
        if(!action || !id) return;

        const s = shipments.find(x => String(x.id) === String(id));
        if(!s) return;

        if(action === 'edit'){
            openModal('edit', s);
        } else if(action === 'archive'){
            if(!confirm('Archive this shipment?')) return;
            try{
                await updateShipment(id, { 
                    shipment_number: s.shipment_number,
                    origin: s.origin,
                    destination: s.destination,
                    items_quantity: s.items_quantity,
                    dispatch_date: s.dispatch_date,
                    status: 'Archived',
                    notes: s.notes || ''
                });
                if(window.Toastify) Toastify({ text: 'Archived successfully', duration: 2200, backgroundColor: '#16a34a' }).showToast();
                load();
            }catch(err){
                console.error(err);
                if(window.Toastify) Toastify({ text: err.message || 'Archive failed', duration: 3000, backgroundColor: '#dc2626' }).showToast();
            }
        } else if(action === 'unarchive'){
            if(!confirm('Restore this shipment?')) return;
            try{
                await updateShipment(id, { 
                    shipment_number: s.shipment_number,
                    origin: s.origin,
                    destination: s.destination,
                    items_quantity: s.items_quantity,
                    dispatch_date: s.dispatch_date,
                    status: 'Pending', // Restore to Pending status
                    notes: s.notes || ''
                });
                if(window.Toastify) Toastify({ text: 'Restored successfully', duration: 2200, backgroundColor: '#16a34a' }).showToast();
                load();
            }catch(err){
                console.error(err);
                if(window.Toastify) Toastify({ text: err.message || 'Restore failed', duration: 3000, backgroundColor: '#dc2626' }).showToast();
            }
        }
    });

    shipmentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            shipment_number: f_shipment_number.value.trim(),
            origin: f_origin.value.trim(),
            destination: f_destination.value.trim(),
            items_quantity: Number(f_items_quantity.value || 0),
            dispatch_date: f_dispatch_date.value,
            status: f_status.value,
            notes: f_notes.value.trim()
        };
        if(!payload.shipment_number || !payload.origin || !payload.destination || !payload.dispatch_date){
            if(window.Toastify) Toastify({ text: 'Please fill all required fields', duration: 2200, backgroundColor: '#dc2626' }).showToast();
            return;
        }
        try{
            if(isEditing && editId){
                await updateShipment(editId, payload);
                if(window.Toastify) Toastify({ text: 'Updated successfully', duration: 2200, backgroundColor: '#16a34a' }).showToast();
            } else {
                await createShipment(payload);
                if(window.Toastify) Toastify({ text: 'Created successfully', duration: 2200, backgroundColor: '#16a34a' }).showToast();
            }
            closeModal();
            load();
        }catch(err){
            console.error(err);
            if(window.Toastify) Toastify({ text: err.message || 'Save failed', duration: 3000, backgroundColor: '#dc2626' }).showToast();
        }
    });

    function excelText(val){
        const s = String(val ?? '');
        return `="${s}"`;
    }

    function exportCSV(){
        if(!shipments.length){
            if(window.Toastify) Toastify({ text: 'No data to export', duration: 2200 }).showToast();
            return;
        }
        const headers = ['ID','Shipment Number','Origin','Destination','Status','Dispatch Date','Items Quantity','Notes'];
        const rows = shipments.map(s => [
            s.id,
            s.shipment_number,
            s.origin,
            s.destination,
            s.status,
            excelText(s.dispatch_date),
            Number(s.items_quantity || 0),
            (s.notes || '')
        ]);
        const csv = [headers, ...rows].map(r => r.map(cell => {
            const s = String(cell ?? '');
            return /[",\n]/.test(s) ? '"' + s.replace(/"/g, '""') + '"' : s;
        }).join(',')).join('\n');

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'shipments.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // Filter event listeners
    applyFilter.addEventListener('click', () => load());
    clearFilter.addEventListener('click', () => {
        filterStatus.value = 'all';
        filterArchive.value = 'active';
        load();
    });

    exportBtn?.addEventListener('click', exportCSV);
    refreshBtn?.addEventListener('click', load);

    // initial load with active filter
    filterArchive.value = 'active';
    load();
})();
</script>
HTML;

adminLayout($children);
?>
