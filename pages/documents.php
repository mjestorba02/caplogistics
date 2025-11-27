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
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Records & Compliance</a> &gt;
        <span>Document Management</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-2 md:mb-0">Document Management</h1>
        <div class="flex flex-wrap items-center gap-3">
            <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Upload Document</button>
            <button id="refreshBtn" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800 transition">Refresh</button>
            <button id="exportBtn" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 transition">Export CSV</button>
        </div>
    </div>

    <!-- Filter Section -->
   <!-- Filter Section -->
<div class="bg-white p-4 rounded-lg shadow mb-6">
    <div class="flex flex-col md:flex-row items-center gap-4">
        <label class="text-gray-700 font-medium whitespace-nowrap">Filter by Type:</label>
        <select id="filterType" class="w-full md:w-48 border rounded px-3 py-2">
            <option value="all">All Types</option>
            <option value="Contract">Contract</option>
            <option value="Audit">Audit</option>
            <option value="Report">Report</option>
            <option value="Other">Other</option>
        </select>
        <label class="text-gray-700 font-medium whitespace-nowrap">Status:</label>
        <select id="filterStatus" class="w-full md:w-48 border rounded px-3 py-2">
            <option value="all">All Status</option>
            <option value="active" selected>Active Only</option>
            <option value="archived">Archived Only</option>
        </select>
        <button id="applyFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Apply Filters</button>
        <button id="clearFilter" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
    </div>
</div>

    <!-- Documents Grid -->
    <div id="docsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>

</div>

<!-- Modal for Uploading Document -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-bold">Upload Document</h2>
          <button class="text-gray-500 hover:text-gray-700 text-2xl leading-none" data-close="button">&times;</button>
        </div>
        <form id="uploadForm" class="grid grid-cols-1 md:grid-cols-2 gap-4" enctype="multipart/form-data">
            <div class="md:col-span-2">
                <label class="block text-gray-700">Document Name</label>
                <input id="f_name" type="text" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Document Type</label>
                <select id="f_type" class="w-full border rounded px-3 py-2" required>
                    <option value="">Select Type</option>
                    <option value="Contract">Contract</option>
                    <option value="Audit">Audit</option>
                    <option value="Report">Report</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700">File Upload</label>
                <input id="f_file" type="file" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="md:col-span-2 flex justify-end gap-3 mt-1">
                <button type="button" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300" data-close="cancel">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Upload</button>
            </div>
        </form>
    </div>
</div>

<script>
(function(){
    const API_URL = 'https://log1.imarketph.com/api/documents.php';

    const grid = document.getElementById('docsGrid');
    const openModalBtn = document.getElementById('openModal');
    const modal = document.getElementById('modal');
    const uploadForm = document.getElementById('uploadForm');
    
    const filterType = document.getElementById('filterType');
    const filterStatus = document.getElementById('filterStatus');
    const applyFilter = document.getElementById('applyFilter');
    const clearFilter = document.getElementById('clearFilter');

    const f_name = document.getElementById('f_name');
    const f_type = document.getElementById('f_type');
    const f_file = document.getElementById('f_file');

    const refreshBtn = document.getElementById('refreshBtn');
    const exportBtn = document.getElementById('exportBtn');
    
    // Store all documents and filtered documents
    let allDocuments = [];
    let filteredDocuments = [];

    function openModal(){ modal.classList.remove('hidden'); modal.classList.add('flex'); }
    function closeModal(){ modal.classList.add('hidden'); modal.classList.remove('flex'); uploadForm.reset(); }

    modal.addEventListener('click', (e) => { if(e.target?.dataset?.close) closeModal(); });
    document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });

    openModalBtn?.addEventListener('click', openModal);

    function escapeHtml(str){ return String(str ?? '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m])); }

    function render(items){
        if(!items.length){ grid.innerHTML = `<div class="text-gray-500 p-6 text-center">No documents found matching your filters</div>`; return; }
        grid.innerHTML = items.map(d => `
            <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer ${d.archived_at ? 'opacity-70' : ''}">
                <h2 class="text-xl font-semibold mb-2">${escapeHtml(d.name)}</h2>
                <p class="text-gray-600 mb-1">Type: <span class="font-medium">${escapeHtml(d.type)}</span></p>
                <p class="text-gray-600 mb-1">Uploaded By: ${escapeHtml(d.uploaded_by)}</p>
                <p class="text-gray-600 mb-2">Date: ${escapeHtml((d.uploaded_at || '').split(' ')[0])}</p>
                <div class="flex justify-end gap-3">
                    <a class="text-blue-600 hover:underline" href="${escapeHtml(d.file_path)}" target="_blank" rel="noopener">View</a>
                    ${d.archived_at ? `<span class="text-gray-400">Archived</span>` : `<button class="text-red-600 hover:underline" data-action="archive" data-id="${d.id}">Archive</button>`}
                </div>
            </div>
        `).join('');
    }
    
    function applyFilters() {
        const typeFilter = filterType.value;
        const statusFilter = filterStatus.value;
        
        filteredDocuments = allDocuments.filter(doc => {
            // Type filter
            if (typeFilter !== 'all' && doc.type !== typeFilter) return false;
            
            // Status filter
            if (statusFilter === 'active' && doc.archived_at) return false;
            if (statusFilter === 'archived' && !doc.archived_at) return false;
            
            return true;
        });
        
        render(filteredDocuments);
    }
    
    function clearFilters() {
        filterType.value = 'all';
        filterStatus.value = 'all';
        filteredDocuments = [...allDocuments];
        render(filteredDocuments);
    }

   // Replace the load() function in your frontend code with this updated version
async function load(){
    try{
        grid.innerHTML = `<div class="text-gray-500 p-6 text-center">Loading...</div>`;
        
        // Build query parameters based on current filters
        const params = new URLSearchParams();
        const typeFilter = filterType.value;
        const statusFilter = filterStatus.value;
        
        if (typeFilter && typeFilter !== 'all') {
            params.append('type', typeFilter);
        }
        
        if (statusFilter === 'active') {
            params.append('archived', '0');
        } else if (statusFilter === 'archived') {
            params.append('archived', '1');
        }
        
        const url = API_URL + (params.toString() ? '?' + params.toString() : '');
        const res = await fetch(url, { credentials: 'include' });
        const data = await res.json();
        if(data.status !== 'success') throw new Error(data.message || 'Failed to fetch');
        
        allDocuments = Array.isArray(data.data) ? data.data : [];
        filteredDocuments = [...allDocuments];
        render(filteredDocuments);
    }catch(err){
        console.error(err);
        grid.innerHTML = `<div class="text-red-600 p-6 text-center">Error loading documents</div>`;
    }
}

// Also update the applyFilters function to use the backend filtering instead of client-side
function applyFilters() {
    load(); // Now we're using server-side filtering
}

function clearFilters() {
    filterType.value = 'all';
    filterStatus.value = 'active';
    load(); // Reload without filters
}

    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('name', f_name.value.trim());
        formData.append('type', f_type.value);
        if(f_file.files[0]) formData.append('file', f_file.files[0]);

        try{
            const res = await fetch(API_URL, { method: 'POST', body: formData, credentials: 'include' });
            const data = await res.json();
            if(data.status !== 'success') throw new Error(data.message || 'Upload failed');
            if(window.Toastify) Toastify({ text: 'Uploaded successfully', duration: 2200, backgroundColor: '#16a34a' }).showToast();
            closeModal();
            load();
        }catch(err){
            console.error(err);
            if(window.Toastify) Toastify({ text: err.message || 'Upload failed', duration: 3000, backgroundColor: '#dc2626' }).showToast();
        }
    });

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-action]');
        if(!btn) return;
        const action = btn.dataset.action; const id = btn.dataset.id;
        if(action === 'archive'){
            if(!confirm('Archive this document?')) return;
            try{
                const res = await fetch(`${API_URL}?id=${encodeURIComponent(id)}&action=archive`, { method: 'PUT', credentials: 'include' });
                const data = await res.json();
                if(data.status !== 'success') throw new Error(data.message || 'Archive failed');
                if(window.Toastify) Toastify({ text: 'Archived successfully', duration: 2200, backgroundColor: '#16a34a' }).showToast();
                load();
            }catch(err){
                console.error(err);
                if(window.Toastify) Toastify({ text: err.message || 'Archive failed', duration: 3000, backgroundColor: '#dc2626' }).showToast();
            }
        }
    });

    function excelText(val){ const s = String(val ?? ''); return `="${s}"`; }
    function exportCSV(){
        // Use filtered documents for export
        const items = filteredDocuments;
        if(!items.length){ if(window.Toastify) Toastify({ text: 'No data to export', duration: 2200 }).showToast(); return; }
        const headers = ['ID','Name','Type','Uploaded By','Uploaded At','Archived At','File Path'];
        const rows = items.map(d => [
            d.id,
            d.name,
            d.type,
            d.uploaded_by,
            excelText(d.uploaded_at),
            excelText(d.archived_at || ''),
            d.file_path
        ]);
        const csv = [headers, ...rows].map(r => r.map(cell => { const s = String(cell ?? ''); return /[",\n]/.test(s) ? '"' + s.replace(/\"/g, '""') + '"' : s; }).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = 'documents.csv'; document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
    }

    // Event listeners for filters
    applyFilter.addEventListener('click', applyFilters);
    clearFilter.addEventListener('click', clearFilters);
    
    // Add event listeners for enter key in filter fields
    filterType.addEventListener('keypress', (e) => { if(e.key === 'Enter') applyFilters(); });
    filterStatus.addEventListener('keypress', (e) => { if(e.key === 'Enter') applyFilters(); });

    exportBtn?.addEventListener('click', exportCSV);
    refreshBtn?.addEventListener('click', load);

    load();
})();
</script>
HTML;

adminLayout($children);
?>