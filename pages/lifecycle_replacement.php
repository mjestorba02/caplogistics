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
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Asset Management</a> &gt;
        <span>Lifecycle & Replacement</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-2 md:mb-0">Lifecycle & Replacement</h1>
        <div class="flex flex-wrap items-center gap-3">
            <button id="addBtn" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Schedule Replacement</button>
            <button id="refreshBtn" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800 transition">Refresh</button>
            <button id="exportBtn" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 transition">Export CSV</button>
        </div>
    </div>

    <!-- Replacements Grid -->
    <div id="replGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>

</div>

<!-- Modal: Add/Edit Replacement -->
<div id="modal" class="fixed inset-0 hidden justify-center items-center z-50">
  <div class="absolute inset-0 bg-black/50" data-close="overlay"></div>
  <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
    <div class="flex items-center justify-between mb-4">
      <h2 id="modalTitle" class="text-2xl font-bold">Schedule Asset Replacement</h2>
      <button class="text-gray-500 hover:text-gray-700 text-2xl leading-none" data-close="button">&times;</button>
    </div>
    <form id="replForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-gray-700 text-sm mb-1">Asset Name<span class="text-red-600">*</span></label>
            <input id="f_asset_name" type="text" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Asset ID<span class="text-red-600">*</span></label>
            <input id="f_asset_id" type="text" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Current Age (Years)<span class="text-red-600">*</span></label>
            <input id="f_current_age_years" type="number" min="0" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Replacement Date<span class="text-red-600">*</span></label>
            <input id="f_replacement_date" type="date" class="w-full border rounded px-3 py-2" required />
        </div>
        <div>
            <label class="block text-gray-700 text-sm mb-1">Status</label>
            <select id="f_status" class="w-full border rounded px-3 py-2">
                <option value="Planned">Planned</option>
                <option value="Approved">Approved</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
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
    const API_URL = 'https://log1.imarketph.com/api/replacements.php';

    const grid = document.getElementById('replGrid');
    let items = [];

    const addBtn = document.getElementById('addBtn');
    const refreshBtn = document.getElementById('refreshBtn');
    const exportBtn = document.getElementById('exportBtn');

    // Modal refs
    const modal = document.getElementById('modal');
    const modalTitle = document.getElementById('modalTitle');
    const replForm = document.getElementById('replForm');

    const f_asset_name = document.getElementById('f_asset_name');
    const f_asset_id = document.getElementById('f_asset_id');
    const f_current_age_years = document.getElementById('f_current_age_years');
    const f_replacement_date = document.getElementById('f_replacement_date');
    const f_status = document.getElementById('f_status');
    const f_notes = document.getElementById('f_notes');

    let isEditing = false;
    let editId = null;

    function openModal(mode, item = null){
        isEditing = mode === 'edit';
        editId = item?.id ?? null;
        modalTitle.textContent = isEditing ? 'Edit Replacement' : 'Schedule Asset Replacement';

        f_asset_name.value = item?.asset_name || '';
        f_asset_id.value = item?.asset_id || '';
        f_current_age_years.value = item?.current_age_years ?? '';
        f_replacement_date.value = item?.replacement_date || '';
        f_status.value = item?.status && ['Planned','Approved','Completed','Cancelled'].includes(item.status) ? item.status : 'Planned';
        f_notes.value = item?.notes || '';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal(){
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        replForm.reset();
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
        if(s === 'Planned') cls = 'bg-blue-200 text-blue-800';
        else if(s === 'Approved') cls = 'bg-yellow-200 text-yellow-800';
        else if(s === 'Completed') cls = 'bg-green-200 text-green-800';
        else if(s === 'Cancelled') cls = 'bg-red-200 text-red-800';
        else if(s === 'Archived') cls = 'bg-gray-300 text-gray-700';
        return `<span class="px-2 py-1 rounded-full text-xs ${cls}">${s}</span>`;
    }

    function render(){
        if(!items.length){
            grid.innerHTML = `<div class=\"text-gray-500\">No replacements scheduled</div>`;
            return;
        }
        grid.innerHTML = items.map(r => `
            <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
                <h2 class="text-xl font-semibold mb-2">${escapeHtml(r.asset_name)}</h2>
                <p class="text-gray-600 mb-1">Asset ID: ${escapeHtml(r.asset_id)}</p>
                <p class="text-gray-600 mb-1">Age: ${Number(r.current_age_years || 0)} years</p>
                <p class="text-gray-600 mb-1">Replacement Date: ${escapeHtml(r.replacement_date)}</p>
                <p class="text-gray-600 mb-2">Status: ${statusBadge(escapeHtml(r.status || 'Planned'))}</p>
                <div class="flex justify-end gap-3">
                    <button class="text-blue-600 hover:underline" data-action="edit" data-id="${r.id}">Edit</button>
                    ${r.status === 'Archived' ? `<span class="text-gray-400">Archived</span>` : `<button class="text-red-600 hover:underline" data-action="archive" data-id="${r.id}">Archive</button>`}
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
            grid.innerHTML = `<div class=\"text-red-600\">Error loading</div>`;
        }
    }

    async function createItem(payload){
        const res = await fetch(API_URL, { method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include', body: JSON.stringify(payload) });
        const data = await res.json();
        if(res.ok && data.status === 'success') return data;
        throw new Error(data?.message || 'Create failed');
    }

    async function updateItem(id, payload){
        const res = await fetch(`${API_URL}?id=${encodeURIComponent(id)}`, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, credentials: 'include', body: JSON.stringify(payload) });
        const data = await res.json();
        if(res.ok && data.status === 'success') return data;
        throw new Error(data?.message || 'Update failed');
    }

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-action]');
        if(!btn) return;
        const action = btn.dataset.action;
        const id = btn.dataset.id;
        const r = items.find(x => String(x.id) === String(id));
        if(!action || !r) return;

        if(action === 'edit'){
            openModal('edit', r);
        } else if(action === 'archive'){
            if(!confirm('Archive this replacement?')) return;
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

    replForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            asset_name: f_asset_name.value.trim(),
            asset_id: f_asset_id.value.trim(),
            current_age_years: Number(f_current_age_years.value || 0),
            replacement_date: f_replacement_date.value,
            status: f_status.value,
            notes: f_notes.value.trim()
        };
        if(!payload.asset_name || !payload.asset_id || !payload.replacement_date){
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
        const headers = ['ID','Asset ID','Asset Name','Current Age (years)','Replacement Date','Status','Notes'];
        const rows = items.map(r => [
            r.id,
            r.asset_id,
            r.asset_name,
            Number(r.current_age_years || 0),
            excelText(r.replacement_date),
            r.status,
            r.notes || ''
        ]);
        const csv = [headers, ...rows].map(r => r.map(cell => { const s = String(cell ?? ''); return /[",\n]/.test(s) ? '"' + s.replace(/\"/g, '""') + '"' : s; }).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = 'replacements.csv'; document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
    }

    exportBtn?.addEventListener('click', exportCSV);
    refreshBtn?.addEventListener('click', load);

    load();
})();
</script>
HTML;

adminLayout($children);
?>
