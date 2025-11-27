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
        <span>Milestones & Status Updates</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-2 md:mb-0">Milestones & Status Updates</h1>
        <div class="flex flex-wrap items-center gap-3">
            <button id="addBtn" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Add Milestone</button>
            <button id="refreshBtn" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800 transition">Refresh</button>
            <button id="exportBtn" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 transition">Export CSV</button>
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
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="Archived">Archived</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 text-sm mb-1">Shipment Number</label>
                <input id="filterShipment" type="text" class="w-full border rounded px-3 py-2" placeholder="Search shipment...">
            </div>
            <div>
                <label class="block text-gray-700 text-sm mb-1">Date From</label>
                <input id="filterDateFrom" type="date" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-700 text-sm mb-1">Date To</label>
                <input id="filterDateTo" type="date" class="w-full border rounded px-3 py-2">
            </div>
        </div>
        <div class="mt-4 flex justify-end gap-3">
            <button id="applyFilters" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Apply Filters</button>
            <button id="clearFilters" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">Clear Filters</button>
        </div>
    </div>

    <!-- Milestones Table -->
    <div class="hidden md:block overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Shipment #</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Milestone</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Status</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Date</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Notes</th>
                    <th class="py-3 px-6 text-center text-sm font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody id="milestones-tbody" class="divide-y divide-gray-200">
                <tr>
                    <td class="py-4 px-6 text-gray-500" colspan="6">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Card layout for small screens -->
    <div id="milestones-cards" class="md:hidden flex flex-col gap-4"></div>
</div>

<!-- Modal: Add/Edit Milestone -->
<div id="modal" class="fixed inset-0 hidden justify-center items-center z-50">
  <div class="absolute inset-0 bg-black/50" data-close="overlay"></div>
  <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
    <div class="flex items-center justify-between mb-4">
      <h2 id="modalTitle" class="text-2xl font-bold">Add Milestone</h2>
      <button class="text-gray-500 hover:text-gray-700 text-2xl leading-none" data-close="button">&times;</button>
    </div>
    <form id="milestoneForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-gray-700 text-sm mb-1">Shipment Number<span class="text-red-600">*</span></label>
            <input id="f_shipment_number" type="text" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Milestone<span class="text-red-600">*</span></label>
            <input id="f_milestone" type="text" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Status<span class="text-red-600">*</span></label>
            <select id="f_status" class="w-full border rounded px-3 py-2" required>
                <option value="Pending">Pending</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
            </select>
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Date<span class="text-red-600">*</span></label>
            <input id="f_milestone_date" type="date" class="w-full border rounded px-3 py-2" required />
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
    const API_URL = 'http://localhost/caplog1/api/milestones.php';
    let milestones = [];
    let filteredMilestones = [];

    const tbody = document.getElementById('milestones-tbody');
    const cards = document.getElementById('milestones-cards');
    const addBtn = document.getElementById('addBtn');
    const refreshBtn = document.getElementById('refreshBtn');
    const exportBtn = document.getElementById('exportBtn');
    
    // Filter elements
    const filterStatus = document.getElementById('filterStatus');
    const filterShipment = document.getElementById('filterShipment');
    const filterDateFrom = document.getElementById('filterDateFrom');
    const filterDateTo = document.getElementById('filterDateTo');
    const applyFilters = document.getElementById('applyFilters');
    const clearFilters = document.getElementById('clearFilters');

    // Modal refs
    const modal = document.getElementById('modal');
    const modalTitle = document.getElementById('modalTitle');
    const milestoneForm = document.getElementById('milestoneForm');
    const f_shipment_number = document.getElementById('f_shipment_number');
    const f_milestone = document.getElementById('f_milestone');
    const f_status = document.getElementById('f_status');
    const f_milestone_date = document.getElementById('f_milestone_date');
    const f_notes = document.getElementById('f_notes');

    let isEditing = false;
    let editId = null;

    function openModal(mode, item = null){
        isEditing = mode === 'edit';
        editId = item?.id ?? null;
        modalTitle.textContent = isEditing ? 'Edit Milestone' : 'Add Milestone';

        f_shipment_number.value = item?.shipment_number || '';
        f_milestone.value = item?.milestone || '';
        f_status.value = item?.status && ['Pending','In Progress','Completed'].includes(item.status) ? item.status : 'Pending';
        f_milestone_date.value = item?.milestone_date || '';
        f_notes.value = item?.notes || '';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal(){
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        milestoneForm.reset();
        isEditing = false;
        editId = null;
    }

    modal.addEventListener('click', (e) => { if(e.target?.dataset?.close) closeModal(); });
    document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });
    addBtn?.addEventListener('click', () => openModal('create'));

    function statusBadge(status){
        let cls = 'bg-gray-200 text-gray-800';
        if(status === 'Pending') cls = 'bg-yellow-200 text-yellow-800';
        else if(status === 'In Progress') cls = 'bg-blue-200 text-blue-800';
        else if(status === 'Completed') cls = 'bg-green-200 text-green-800';
        else if(status === 'Archived') cls = 'bg-gray-300 text-gray-700';
        return `<span class="px-2 py-1 rounded-full text-xs ${cls}">${status}</span>`;
    }

    function applyFiltersToData() {
        const statusFilter = filterStatus.value;
        const shipmentFilter = filterShipment.value.toLowerCase();
        const dateFromFilter = filterDateFrom.value;
        const dateToFilter = filterDateTo.value;
        
        filteredMilestones = milestones.filter(milestone => {
            // Status filter
            if (statusFilter && milestone.status !== statusFilter) {
                return false;
            }
            
            // Shipment number filter
            if (shipmentFilter && !milestone.shipment_number.toLowerCase().includes(shipmentFilter)) {
                return false;
            }
            
            // Date range filter
            if (dateFromFilter && milestone.milestone_date < dateFromFilter) {
                return false;
            }
            
            if (dateToFilter && milestone.milestone_date > dateToFilter) {
                return false;
            }
            
            return true;
        });
        
        render();
    }

    function clearAllFilters() {
        filterStatus.value = '';
        filterShipment.value = '';
        filterDateFrom.value = '';
        filterDateTo.value = '';
        filteredMilestones = [...milestones];
        render();
    }

    function render(){
        const dataToRender = filteredMilestones.length > 0 ? filteredMilestones : milestones;
        
        // Table
        if(!dataToRender.length){
            tbody.innerHTML = `<tr><td class=\"py-4 px-6 text-gray-500\" colspan=\"6\">No milestones found</td></tr>`;
        } else {
            tbody.innerHTML = dataToRender.map(m => `
                <tr>
                    <td class="py-4 px-6 text-gray-800">${escapeHtml(m.shipment_number)}</td>
                    <td class="py-4 px-6 text-gray-800">${escapeHtml(m.milestone)}</td>
                    <td class="py-4 px-6 text-gray-800">${statusBadge(escapeHtml(m.status || 'Pending'))}</td>
                    <td class="py-4 px-6 text-gray-800">${escapeHtml(m.milestone_date)}</td>
                    <td class="py-4 px-6 text-gray-800">${escapeHtml(m.notes || '')}</td>
                    <td class="py-4 px-6 text-center">
                        <button class="text-blue-600 hover:underline mr-3" data-action="edit" data-id="${m.id}">Edit</button>
                        ${m.status === 'Archived' ? `<span class="text-gray-400">Archived</span>` : `<button class="text-red-600 hover:underline" data-action="archive" data-id="${m.id}">Archive</button>`}
                    </td>
                </tr>
            `).join('');
        }

        // Cards
        cards.innerHTML = dataToRender.map(m => `
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between mb-2">
                    <h3 class="font-semibold">${escapeHtml(m.shipment_number)}</h3>
                    ${statusBadge(escapeHtml(m.status || 'Pending'))}
                </div>
                <p class="text-gray-600"><span class="font-semibold">Milestone:</span> ${escapeHtml(m.milestone)}</p>
                <p class="text-gray-600"><span class="font-semibold">Date:</span> ${escapeHtml(m.milestone_date)}</p>
                <p class="text-gray-600"><span class="font-semibold">Notes:</span> ${escapeHtml(m.notes || '')}</p>
                <div class="flex justify-end gap-4 mt-3">
                    <button class="text-blue-600 hover:underline" data-action="edit" data-id="${m.id}">Edit</button>
                    ${m.status === 'Archived' ? `<span class="text-gray-400">Archived</span>` : `<button class="text-red-600 hover:underline" data-action="archive" data-id="${m.id}">Archive</button>`}
                </div>
            </div>
        `).join('');
    }

    function escapeHtml(str){
        return String(str ?? '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m]));
    }

    async function load(){
        try{
            tbody.innerHTML = `<tr><td class=\"py-4 px-6 text-gray-500\" colspan=\"6\">Loading...</td></tr>`;
            
            // Build query string with filter parameters
            let queryParams = [];
            if (filterStatus.value) queryParams.push(`status=${encodeURIComponent(filterStatus.value)}`);
            if (filterShipment.value) queryParams.push(`shipment_number=${encodeURIComponent(filterShipment.value)}`);
            if (filterDateFrom.value) queryParams.push(`date_from=${encodeURIComponent(filterDateFrom.value)}`);
            if (filterDateTo.value) queryParams.push(`date_to=${encodeURIComponent(filterDateTo.value)}`);
            
            const url = queryParams.length ? `${API_URL}?${queryParams.join('&')}` : API_URL;
            
            const res = await fetch(url, { credentials: 'include' });
            const data = await res.json();
            if(data.status !== 'success') throw new Error(data.message || 'Failed to fetch');
            milestones = Array.isArray(data.data) ? data.data : [];
            filteredMilestones = [...milestones];
            render();
        }catch(err){
            console.error(err);
            tbody.innerHTML = `<tr><td class=\"py-4 px-6 text-red-600\" colspan=\"6\">Error loading</td></tr>`;
            cards.innerHTML = '';
        }
    }

    async function createItem(payload){
        const res = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if(res.ok && data.status === 'success') return data;
        throw new Error(data?.message || 'Create failed');
    }

    async function updateItem(id, payload){
        const res = await fetch(`${API_URL}?id=${encodeURIComponent(id)}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if(res.ok && data.status === 'success') return data;
        throw new Error(data?.message || 'Update failed');
    }

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-action]');
        if(!btn) return;
        const action = btn.dataset.action;
        const id = btn.dataset.id;
        if(!action || !id) return;

        const m = milestones.find(x => String(x.id) === String(id));
        if(!m) return;

        if(action === 'edit'){
            openModal('edit', m);
        } else if(action === 'archive'){
            if(!confirm('Archive this milestone?')) return;
            try{
                await updateItem(id, {
                    shipment_number: m.shipment_number,
                    milestone: m.milestone,
                    status: 'Archived',
                    milestone_date: m.milestone_date,
                    notes: m.notes || ''
                });
                if(window.Toastify) Toastify({ text: 'Archived successfully', duration: 2200, backgroundColor: '#16a34a' }).showToast();
                load();
            } catch(err){
                console.error(err);
                if(window.Toastify) Toastify({ text: err.message || 'Archive failed', duration: 3000, backgroundColor: '#dc2626' }).showToast();
            }
        }
    });

    milestoneForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            shipment_number: f_shipment_number.value.trim(),
            milestone: f_milestone.value.trim(),
            status: f_status.value,
            milestone_date: f_milestone_date.value,
            notes: f_notes.value.trim()
        };
        if(!payload.shipment_number || !payload.milestone || !payload.milestone_date){
            if(window.Toastify) Toastify({ text: 'Please fill all required fields', duration: 2200, backgroundColor: '#dc2626' }).showToast();
            return;
        }
        try{
            if(isEditing && editId){
                await updateItem(editId, payload);
                if(window.Toastify) Toastify({ text: 'Updated successfully', duration: 2200, backgroundColor: '#16a34a' }).showToast();
            } else {
                await createItem(payload);
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
        const dataToExport = filteredMilestones.length > 0 ? filteredMilestones : milestones;
        
        if(!dataToExport.length){
            if(window.Toastify) Toastify({ text: 'No data to export', duration: 2200 }).showToast();
            return;
        }
        const headers = ['ID','Shipment Number','Milestone','Status','Date','Notes'];
        const rows = dataToExport.map(m => [
            m.id,
            m.shipment_number,
            m.milestone,
            m.status,
            excelText(m.milestone_date),
            (m.notes || '')
        ]);
        const csv = [headers, ...rows].map(r => r.map(cell => {
            const s = String(cell ?? '');
            return /[",\n]/.test(s) ? '"' + s.replace(/\"/g, '""') + '"' : s;
        }).join(',')).join('\n');

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'milestones.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // Event listeners for filters
    applyFilters.addEventListener('click', load);
    clearFilters.addEventListener('click', clearAllFilters);
    
    // Add event listeners for enter key in filter inputs
    [filterShipment, filterDateFrom, filterDateTo].forEach(input => {
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                load();
            }
        });
    });

    exportBtn?.addEventListener('click', exportCSV);
    refreshBtn?.addEventListener('click', load);

    // initial load
    load();
})();
</script>
HTML;

adminLayout($children);
?>