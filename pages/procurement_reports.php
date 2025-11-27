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
        <span>Procurement Reports</span>
    </div>

    <!-- Page Header -->
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Procurement Reports & Contracts</h1>
            <p class="text-gray-500 mt-1">Overview of active contracts, purchase orders, and supplier performance.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button id="addReportBtn" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Add Report</button>
            <button id="refreshBtn" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-800">Refresh</button>
            <button id="exportBtn" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">Export CSV</button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Filter by Status:</label>
            <select id="filterStatus" class="w-full md:w-48 border rounded px-3 py-2">
                <option value="all">All Status</option>
                <option value="Active">Active</option>
                <option value="Pending">Pending</option>
                <option value="Terminated">Terminated</option>
                <option value="Expired">Expired</option>
                <option value="Cancelled">Cancelled</option>
                <option value="Completed">Completed</option>
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

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-lg font-semibold text-gray-700 mb-2">Active Contracts</h2>
            <h3 id="activeCount" class="text-2xl font-bold text-green-600">0</h3>
            <p class="text-gray-500 text-sm mt-1">Contracts currently valid and ongoing.</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-lg font-semibold text-gray-700 mb-2">Pending Approvals</h2>
            <h3 id="pendingCount" class="text-2xl font-bold text-yellow-600">0</h3>
            <p class="text-gray-500 text-sm mt-1">Purchase orders waiting for approval.</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-lg font-semibold text-gray-700 mb-2">Expired Contracts</h2>
            <h3 id="expiredCount" class="text-2xl font-bold text-red-600">0</h3>
            <p class="text-gray-500 text-sm mt-1">Contracts that have passed their end date.</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-lg font-semibold text-gray-700 mb-2">Suppliers On Time</h2>
            <h3 class="text-2xl font-bold text-blue-600">88%</h3>
            <p class="text-gray-500 text-sm mt-1">Percentage of deliveries completed on time.</p>
        </div>

    </div>

    <!-- Responsive Contracts Table -->
    <div class="flex flex-col gap-4">

        <!-- Table for large screens -->
        <div class="hidden md:block overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Contract / PO</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Supplier</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Status</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Start Date</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">End Date</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Total Value</th>
                        <th class="py-3 px-6 text-center text-sm font-medium text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody id="contracts-tbody" class="divide-y divide-gray-200">
                    <tr>
                        <td class="py-4 px-6 text-gray-500" colspan="7">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Card layout for small screens -->
        <div id="contracts-cards" class="md:hidden flex flex-col gap-4">
            <!-- Cards injected here -->
        </div>

    </div>

</div>

<!-- Modal: Add/Edit Report -->
<div id="reportModal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/50" data-close="overlay"></div>
  <div class="relative bg-white w-11/12 max-w-2xl rounded-lg shadow-lg p-5">
    <div class="flex items-center justify-between mb-4">
      <h2 id="modalTitle" class="text-xl font-semibold">Add Report</h2>
      <button class="text-gray-500 hover:text-gray-700" data-close="button">&times;</button>
    </div>
    <form id="reportForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm text-gray-700 mb-1">Reference<span class="text-red-600">*</span></label>
        <input id="f_reference" type="text" class="w-full border rounded px-3 py-2" required />
      </div>
      <div>
        <label class="block text-sm text-gray-700 mb-1">Supplier<span class="text-red-600">*</span></label>
        <input id="f_supplier" type="text" class="w-full border rounded px-3 py-2" required />
      </div>
      <div>
        <label class="block text-sm text-gray-700 mb-1">Status<span class="text-red-600">*</span></label>
        <select id="f_status" class="w-full border rounded px-3 py-2" required>
          <option value="Active">Active</option>
          <option value="Pending" selected>Pending</option>
          <option value="Terminated">Terminated</option>
          <option value="Expired">Expired</option>
          <option value="Cancelled">Cancelled</option>
          <option value="Completed">Completed</option>
        </select>
      </div>
      <div>
        <label class="block text-sm text-gray-700 mb-1">Currency<span class="text-red-600">*</span></label>
        <input id="f_currency" type="text" class="w-full border rounded px-3 py-2" value="USD" required />
      </div>
      <div>
        <label class="block text-sm text-gray-700 mb-1">Start Date<span class="text-red-600">*</span></label>
        <input id="f_start_date" type="date" class="w-full border rounded px-3 py-2" required />
      </div>
      <div>
        <label class="block text-sm text-gray-700 mb-1">End Date<span class="text-red-600">*</span></label>
        <input id="f_end_date" type="date" class="w-full border rounded px-3 py-2" required />
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm text-gray-700 mb-1">Total Value<span class="text-red-600">*</span></label>
        <input id="f_total_value" type="number" step="0.01" min="0" class="w-full border rounded px-3 py-2" required />
      </div>
      <div class="md:col-span-2 flex justify-end gap-3 mt-2">
        <button type="button" class="px-4 py-2 rounded border" data-close="cancel">Cancel</button>
        <button id="saveBtn" type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
    const API_URL = "http://localhost/caplog1/api/procurement_reports.php";
    let contracts = [];

    const tbody = document.getElementById('contracts-tbody');
    const cardsContainer = document.getElementById('contracts-cards');
    const exportBtn = document.getElementById('exportBtn');
    const refreshBtn = document.getElementById('refreshBtn');
    const addReportBtn = document.getElementById('addReportBtn');
    
    // Filter elements
    const filterStatus = document.getElementById('filterStatus');
    const filterArchive = document.getElementById('filterArchive');
    const applyFilter = document.getElementById('applyFilter');
    const clearFilter = document.getElementById('clearFilter');

    const activeCountEl = document.getElementById('activeCount');
    const pendingCountEl = document.getElementById('pendingCount');
    const expiredCountEl = document.getElementById('expiredCount');

    // Modal refs
    const modal = document.getElementById('reportModal');
    const modalTitle = document.getElementById('modalTitle');
    const reportForm = document.getElementById('reportForm');
    const f_reference = document.getElementById('f_reference');
    const f_supplier = document.getElementById('f_supplier');
    const f_status = document.getElementById('f_status');
    const f_start_date = document.getElementById('f_start_date');
    const f_end_date = document.getElementById('f_end_date');
    const f_total_value = document.getElementById('f_total_value');
    const f_currency = document.getElementById('f_currency');

    let isEditing = false;
    let editId = null;

    function statusClass(status){
        switch(status){
            case 'Active': return 'text-green-600';
            case 'Pending': return 'text-yellow-600';
            case 'Expired': return 'text-red-600';
            case 'Terminated': return 'text-red-600';
            case 'Cancelled': return 'text-red-600';
            case 'Completed': return 'text-blue-600';
            case 'Archived': return 'text-gray-500';
            default: return 'text-gray-700';
        }
    }

    function formatMoney(value, currency){
        const num = Number(value || 0);
        return `${currency || 'USD'} ${num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    function isArchived(c){ return c.status === 'Archived'; }

    function render(){
        // Table rows
        if(!contracts.length){
            tbody.innerHTML = `<tr><td class="py-4 px-6 text-gray-500" colspan="7">No records found</td></tr>`;
        } else {
            tbody.innerHTML = contracts.map(c => `
                <tr class="${isArchived(c) ? 'opacity-70' : ''}">
                    <td class="py-4 px-6 text-gray-800">${escapeHtml(c.reference)}</td>
                    <td class="py-4 px-6 text-gray-800">${escapeHtml(c.supplier)}</td>
                    <td class="py-4 px-6 text-gray-800"><span class="font-semibold ${statusClass(c.status)}">${escapeHtml(c.status)}${isArchived(c) ? ' (Archived)' : ''}</span></td>
                    <td class="py-4 px-6 text-gray-800">${escapeHtml(c.start_date)}</td>
                    <td class="py-4 px-6 text-gray-800">${escapeHtml(c.end_date)}</td>
                    <td class="py-4 px-6 text-gray-800">${formatMoney(c.total_value, c.currency)}</td>
                    <td class="py-4 px-6 text-center">
                        <button class="text-blue-600 hover:underline mr-3" data-id="${c.id}" data-action="edit">Edit</button>
                        ${isArchived(c) ? `<button class="text-blue-600 hover:underline" data-id="${c.id}" data-action="unarchive">Restore</button>` : `<button class="text-red-600 hover:underline" data-id="${c.id}" data-action="archive">Archive</button>`}
                    </td>
                </tr>
            `).join('');
        }

        // Mobile cards
        cardsContainer.innerHTML = contracts.map(c => `
            <div class="bg-white p-4 rounded-lg shadow ${isArchived(c) ? 'opacity-70' : ''}">
                <div class="flex justify-between mb-2">
                    <h2 class="font-semibold">${escapeHtml(c.reference)}</h2>
                    <span class="font-semibold ${statusClass(c.status)}">${escapeHtml(c.status)}${isArchived(c) ? ' (Archived)' : ''}</span>
                </div>
                <p class="text-gray-600"><span class="font-semibold">Supplier:</span> ${escapeHtml(c.supplier)}</p>
                <p class="text-gray-600"><span class="font-semibold">Start:</span> ${escapeHtml(c.start_date)}</p>
                <p class="text-gray-600"><span class="font-semibold">End:</span> ${escapeHtml(c.end_date)}</p>
                <p class="text-gray-600"><span class="font-semibold">Total:</span> ${formatMoney(c.total_value, c.currency)}</p>
                <div class="flex justify-end gap-4 mt-3">
                    <button class="text-blue-600 hover:underline" data-id="${c.id}" data-action="edit">Edit</button>
                    ${isArchived(c) ? `<button class="text-blue-600 hover:underline" data-id="${c.id}" data-action="unarchive">Restore</button>` : `<button class="text-red-600 hover:underline" data-id="${c.id}" data-action="archive">Archive</button>`}
                </div>
            </div>
        `).join('');

        // Update summary counts
        const today = new Date().toISOString().slice(0,10);
        const active = contracts.filter(c => c.status === 'Active').length;
        const pending = contracts.filter(c => c.status === 'Pending').length;
        const expired = contracts.filter(c => c.status === 'Expired' || (c.end_date && c.end_date < today && c.status !== 'Archived')).length;
        activeCountEl.textContent = active;
        pendingCountEl.textContent = pending;
        expiredCountEl.textContent = expired;
    }

    function escapeHtml(str){
        return String(str ?? '').replace(/[&<>"]+/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[s]));
    }

    async function load(){
        try{
            tbody.innerHTML = `<tr><td class="py-4 px-6 text-gray-500" colspan="7">Loading...</td></tr>`;
            
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
            
            const url = API_URL + (params.toString() ? '?' + params.toString() : '');
            const res = await fetch(url, { credentials: 'include' });
            const data = await res.json();
            if(data.status !== 'success') throw new Error(data.message || 'Failed to fetch');
            contracts = Array.isArray(data.data) ? data.data : [];
            render();
        }catch(e){
            console.error(e);
            if(window.Toastify){ Toastify({ text: e.message || 'Failed to load', duration: 3000, backgroundColor: '#dc2626' }).showToast(); }
            tbody.innerHTML = `<tr><td class="py-4 px-6 text-red-600" colspan="7">Error loading data</td></tr>`;
            cardsContainer.innerHTML = '';
            activeCountEl.textContent = '0';
            pendingCountEl.textContent = '0';
            expiredCountEl.textContent = '0';
        }
    }

    function excelText(val){
        const s = String(val ?? '');
        return `="${s}"`;
    }

    function exportCSV(){
        if(!contracts.length){
            if(window.Toastify){ Toastify({ text: 'No data to export', duration: 2500 }).showToast(); }
            return;
        }
        const headers = ['ID','Reference','Supplier','Status','Start Date','End Date','Total Value','Currency'];
        const rows = contracts.map(c => [
            c.id,
            c.reference,
            c.supplier,
            c.status,
            excelText(c.start_date),
            excelText(c.end_date),
            Number(c.total_value ?? 0).toFixed(2),
            c.currency || 'USD'
        ]);
        const csv = [headers, ...rows].map(r => r.map(cell => {
            const s = String(cell ?? '');
            if(/[",\n]/.test(s)) return '"' + s.replace(/\"/g, '""') + '"';
            return s;
        }).join(',')).join('\n');

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'procurement_contracts.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function openModal(mode, c = null){
        isEditing = mode === 'edit';
        editId = isEditing && c ? c.id : null;
        modalTitle.textContent = isEditing ? 'Edit Report' : 'Add Report';

        f_reference.value = c?.reference || '';
        f_supplier.value = c?.supplier || '';
        f_status.value = c?.status && ['Active','Pending','Terminated','Expired','Cancelled','Completed'].includes(c.status) ? c.status : 'Pending';
        f_start_date.value = c?.start_date || '';
        f_end_date.value = c?.end_date || '';
        f_total_value.value = c?.total_value != null ? Number(c.total_value) : '';
        f_currency.value = c?.currency || 'USD';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal(){
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        reportForm.reset();
        isEditing = false;
        editId = null;
    }

    // Modal actions
    modal.addEventListener('click', (e) => {
        if (e.target?.dataset?.close) closeModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    addReportBtn?.addEventListener('click', () => openModal('create'));

    // Delegated actions from table and cards
    function findContractById(id){ return contracts.find(c => String(c.id) === String(id)); }

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-action]');
        if(!btn) return;
        const action = btn.dataset.action;
        const id = btn.dataset.id;
        if(!action) return;

        if(action === 'edit'){
            const c = findContractById(id);
            if(c) openModal('edit', c);
        } else if(action === 'archive'){
            const c = findContractById(id);
            if(!c) return;
            if(!confirm('Archive this contract?')) return;
            try{
                const res = await fetch(`${API_URL}?id=${encodeURIComponent(id)}&action=archive`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({})
                });
                const data = await res.json();
                if(data.status !== 'success') throw new Error(data.message || 'Failed to archive');
                if(window.Toastify) Toastify({ text: 'Archived successfully', duration: 2500, backgroundColor: '#16a34a' }).showToast();
                load();
            } catch(err){
                console.error(err);
                if(window.Toastify) Toastify({ text: err.message || 'Archive failed', duration: 3000, backgroundColor: '#dc2626' }).showToast();
            }
        } else if(action === 'unarchive'){
            const c = findContractById(id);
            if(!c) return;
            if(!confirm('Restore this contract?')) return;
            try{
                const res = await fetch(`${API_URL}?id=${encodeURIComponent(id)}&action=unarchive`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ restore_status: 'Active' })
                });
                const data = await res.json();
                if(data.status !== 'success') throw new Error(data.message || 'Failed to restore');
                if(window.Toastify) Toastify({ text: 'Restored successfully', duration: 2500, backgroundColor: '#16a34a' }).showToast();
                load();
            } catch(err){
                console.error(err);
                if(window.Toastify) Toastify({ text: err.message || 'Restore failed', duration: 3000, backgroundColor: '#dc2626' }).showToast();
            }
        }
    });

    // Submit form
    reportForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            reference: f_reference.value.trim(),
            supplier: f_supplier.value.trim(),
            status: f_status.value,
            start_date: f_start_date.value,
            end_date: f_end_date.value,
            total_value: Number(f_total_value.value || 0),
            currency: f_currency.value.trim() || 'USD'
        };

        if(!payload.reference || !payload.supplier || !payload.start_date || !payload.end_date || isNaN(payload.total_value)){
            if(window.Toastify) Toastify({ text: 'Please fill all required fields', duration: 2500, backgroundColor: '#dc2626' }).showToast();
            return;
        }

        try{
            let res, data;
            if(isEditing && editId){
                res = await fetch(`${API_URL}?id=${encodeURIComponent(editId)}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify(payload)
                });
            } else {
                res = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify(payload)
                });
            }
            data = await res.json();
            if(data.status !== 'success') throw new Error(data.message || 'Request failed');
            if(window.Toastify) Toastify({ text: isEditing ? 'Updated successfully' : 'Created successfully', duration: 2500, backgroundColor: '#16a34a' }).showToast();
            closeModal();
            load();
        }catch(err){
            console.error(err);
            if(window.Toastify) Toastify({ text: err.message || 'Save failed', duration: 3000, backgroundColor: '#dc2626' }).showToast();
        }
    });
    
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
