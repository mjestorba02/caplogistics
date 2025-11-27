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
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Asset Management</a> &gt;
        <span>Asset Registration & Usage</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-2 md:mb-0">Asset Registration & Usage</h1>
        <div class="flex flex-wrap items-center gap-3">
            <button id="addBtn" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Add New Asset</button>
            <button id="refreshBtn" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800 transition">Refresh</button>
            <button id="exportBtn" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 transition">Export CSV</button>
        </div>
    </div>

    <!-- Assets Grid -->
    <div id="assetsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Cards injected here -->
    </div>

</div>

<!-- Modal: Add/Edit Asset -->
<div id="modal" class="fixed inset-0 hidden justify-center items-center z-50">
  <div class="absolute inset-0 bg-black/50" data-close="overlay"></div>
  <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
    <div class="flex items-center justify-between mb-4">
      <h2 id="modalTitle" class="text-2xl font-bold">Add New Asset</h2>
      <button class="text-gray-500 hover:text-gray-700 text-2xl leading-none" data-close="button">&times;</button>
    </div>
    <form id="assetForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-gray-700 text-sm mb-1">Asset Name<span class="text-red-600">*</span></label>
            <input id="f_name" type="text" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Asset ID<span class="text-red-600">*</span></label>
            <input id="f_asset_id" type="text" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Category<span class="text-red-600">*</span></label>
            <input id="f_category" type="text" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Status</label>
            <select id="f_status" class="w-full border rounded px-3 py-2">
                <option value="Available">Available</option>
                <option value="In Use">In Use</option>
                <option value="Maintenance">Maintenance</option>
                <option value="Retired">Retired</option>
            </select>
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Assigned To</label>
            <input id="f_assigned_to" type="text" class="w-full border rounded px-3 py-2" />
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Purchased Date</label>
            <input id="f_purchased_date" type="date" class="w-full border rounded px-3 py-2" />
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
    const API_URL = 'http://localhost/caplog1/api/assets.php';

    const grid = document.getElementById('assetsGrid');
    const addBtn = document.getElementById('addBtn');
    const refreshBtn = document.getElementById('refreshBtn');
    const exportBtn = document.getElementById('exportBtn');

    // Modal refs
    const modal = document.getElementById('modal');
    const modalTitle = document.getElementById('modalTitle');
    const assetForm = document.getElementById('assetForm');

    const f_name = document.getElementById('f_name');
    const f_asset_id = document.getElementById('f_asset_id');
    const f_category = document.getElementById('f_category');
    const f_status = document.getElementById('f_status');
    const f_assigned_to = document.getElementById('f_assigned_to');
    const f_purchased_date = document.getElementById('f_purchased_date');
    const f_notes = document.getElementById('f_notes');

    let items = [];
    let isEditing = false;
    let editId = null;

    function openModal(mode, item = null){
        isEditing = mode === 'edit';
        editId = item?.id ?? null;
        modalTitle.textContent = isEditing ? 'Edit Asset' : 'Add New Asset';

        f_name.value = item?.name || '';
        f_asset_id.value = item?.asset_id || '';
        f_category.value = item?.category || '';
        f_status.value = item?.status && ['Available','In Use','Maintenance','Retired'].includes(item.status) ? item.status : 'Available';
        f_assigned_to.value = item?.assigned_to || '';
        f_purchased_date.value = item?.purchased_date || '';
        f_notes.value = item?.notes || '';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal(){
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        assetForm.reset();
        isEditing = false;
        editId = null;
    }

    modal.addEventListener('click', (e) => { if(e.target?.dataset?.close) closeModal(); });
    document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });

    addBtn?.addEventListener('click', () => openModal('create'));

    function escapeHtml(str){
        return String(str ?? '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
    }

    function statusBadge(s){
        let cls = 'bg-gray-200 text-gray-800';
        if(s === 'Available') cls = 'bg-emerald-200 text-emerald-800';
        else if(s === 'In Use') cls = 'bg-blue-200 text-blue-800';
        else if(s === 'Maintenance') cls = 'bg-yellow-200 text-yellow-800';
        else if(s === 'Retired') cls = 'bg-purple-200 text-purple-800';
        else if(s === 'Archived') cls = 'bg-gray-300 text-gray-700';
        return `<span class="px-2 py-1 rounded-full text-xs ${cls}">${s}</span>`;
    }

    function render(){
        if(!items.length){
            grid.innerHTML = `<div class=\"text-gray-500\">No assets found</div>`;
            return;
        }
        grid.innerHTML = items.map(a => `
            <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
                <h2 class="text-xl font-semibold mb-2">${escapeHtml(a.name)}</h2>
                <p class="text-gray-600 mb-1">Asset ID: ${escapeHtml(a.asset_id)}</p>
                <p class="text-gray-600 mb-1">Category: ${escapeHtml(a.category)}</p>
                <p class="text-gray-600 mb-1">Status: ${statusBadge(escapeHtml(a.status || 'Available'))}</p>
                <p class="text-gray-600 mb-1">Assigned To: ${escapeHtml(a.assigned_to || 'N/A')}</p>
                <p class="text-gray-600 mb-2">Purchased: ${escapeHtml(a.purchased_date || '')}</p>
                <div class="flex justify-end gap-3">
                    <button class="text-blue-600 hover:underline" data-action="edit" data-id="${a.id}">Edit</button>
                    ${a.status === 'Archived' ? `<span class="text-gray-400">Archived</span>` : `<button class="text-red-600 hover:underline" data-action="archive" data-id="${a.id}">Archive</button>`}
                </div>
            </div>
        `).join('');
    }

    async function load(){
        try{
            grid.innerHTML = `<div class=\"text-gray-500\">Loading...</div>`;
            const res = await fetch(API_URL, { credentials: 'include' });
            const data = await res.json();
            if(data.status !== 'success') throw new Error(data.message || 'Failed to fetch');
            items = Array.isArray(data.data) ? data.data : [];
            render();
        }catch(err){
            console.error(err);
            grid.innerHTML = `<div class=\"text-red-600\">Error loading assets</div>`;
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
        const a = items.find(x => String(x.id) === String(id));
        if(!action || !a) return;

        if(action === 'edit'){
            openModal('edit', a);
        } else if(action === 'archive'){
            if(!confirm('Archive this asset?')) return;
            try{
                await fetch(`${API_URL}?id=${encodeURIComponent(id)}&action=archive`, {
                    method: 'PUT', headers: { 'Content-Type': 'application/json' }, credentials: 'include', body: JSON.stringify({})
                }).then(r => r.json()).then(j => { if(j.status !== 'success') throw new Error(j.message || 'Archive failed'); });
                if(window.Toastify) Toastify({ text: 'Archived successfully', duration: 2200, backgroundColor: '#16a34a' }).showToast();
                load();
            }catch(err){
                console.error(err);
                if(window.Toastify) Toastify({ text: err.message || 'Archive failed', duration: 3000, backgroundColor: '#dc2626' }).showToast();
            }
        }
    });

    assetForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            name: f_name.value.trim(),
            asset_id: f_asset_id.value.trim(),
            category: f_category.value.trim(),
            status: f_status.value,
            assigned_to: f_assigned_to.value.trim(),
            purchased_date: f_purchased_date.value,
            notes: f_notes.value.trim()
        };
        if(!payload.name || !payload.asset_id || !payload.category){
            if(window.Toastify) Toastify({ text: 'Please fill required fields', duration: 2200, backgroundColor: '#dc2626' }).showToast();
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

    function excelText(val){ const s = String(val ?? ''); return `="${s}"`; }

    function exportCSV(){
        if(!items.length){ if(window.Toastify) Toastify({ text: 'No data to export', duration: 2200 }).showToast(); return; }
        const headers = ['ID','Asset ID','Name','Category','Status','Assigned To','Purchased Date','Notes'];
        const rows = items.map(a => [
            a.id,
            a.asset_id,
            a.name,
            a.category,
            a.status,
            a.assigned_to || '',
            excelText(a.purchased_date || ''),
            a.notes || ''
        ]);
        const csv = [headers, ...rows].map(r => r.map(cell => { const s = String(cell ?? ''); return /[",\n]/.test(s) ? '"' + s.replace(/\"/g, '""') + '"' : s; }).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a'); a.href = url; a.download = 'assets.csv'; document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
    }

    exportBtn?.addEventListener('click', exportCSV);
    refreshBtn?.addEventListener('click', load);

    // initial load
    load();
})();
</script>
HTML;

adminLayout($children);
?>
