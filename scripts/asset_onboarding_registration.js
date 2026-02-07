document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const form = document.getElementById('form');
    const tableBody = document.getElementById('tableBody');
    const emptyState = document.getElementById('emptyState');
    const applyFilterBtn = document.getElementById('applyFilter');
    const clearFilterBtn = document.getElementById('clearFilter');

    // Load receiving records for the dropdown
    async function loadReceivingRecords() {
        try {
            const res = await fetch('../api/asset_receiving_logistics.php');
            const data = await res.json();
            const select = document.getElementById('receiving_id');
            if (data.status === 'success' && Array.isArray(data.data) && data.data.length) {
                data.data.forEach(rec => {
                    const option = document.createElement('option');
                    option.value = rec.id;
                    option.textContent = `${rec.po_number} - ${rec.supplier_name || 'N/A'} (${rec.received_date})`;
                    select.appendChild(option);
                });
            }
        } catch (err) {
            console.error('Error loading receiving records:', err);
        }
    }

    function openModal() {
        document.getElementById('modalTitle').textContent = 'Add Asset';
        document.getElementById('itemId').value = '';
        form.reset();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModalFn() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        form.reset();
    }

    openModalBtn.addEventListener('click', openModal);
    closeModalBtn.addEventListener('click', closeModalFn);
    closeBtn.addEventListener('click', closeModalFn);

    async function fetchAssets(search = '') {
        try {
            const url = `../api/asset_onboarding_registration.php${search ? '?q=' + encodeURIComponent(search) : ''}`;
            const res = await fetch(url);
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.data) && data.data.length) {
                renderAssets(data.data);
            } else {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            console.error('Error fetching assets:', err);
            Toastify({ text: 'Error loading assets', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
        }
    }

    function renderAssets(assets) {
        emptyState.classList.add('hidden');
        tableBody.innerHTML = assets.map(a => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-3">${a.asset_tag}</td>
                <td class="px-6 py-3 font-semibold">${a.asset_name}</td>
                <td class="px-6 py-3 text-sm">${a.asset_type || 'N/A'}</td>
                <td class="px-6 py-3 text-sm">${a.serial_number || 'N/A'}</td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">${a.status}</span></td>
                <td class="px-6 py-3 text-sm">${a.registration_date || 'N/A'}</td>
                <td class="px-6 py-3 flex gap-2"><button onclick='editAsset(${JSON.stringify(a).replace(/"/g, '&quot;')})' class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Edit</button><button onclick="archiveAsset(${a.id})" class="bg-orange-600 text-white px-3 py-1 rounded text-xs hover:bg-orange-700">Archive</button></td>
            </tr>
        `).join('');
    }

    function editAsset(a) {
        document.getElementById('modalTitle').textContent = 'Edit Asset';
        document.getElementById('itemId').value = a.id;
        document.getElementById('receiving_id').value = a.receiving_id || '';
        document.getElementById('asset_tag').value = a.asset_tag;
        document.getElementById('asset_name').value = a.asset_name;
        document.getElementById('asset_type').value = a.asset_type || '';
        document.getElementById('serial_number').value = a.serial_number || '';
        document.getElementById('registration_date').value = a.registration_date;
        document.getElementById('registered_by').value = a.registered_by || '';
        document.getElementById('status').value = a.status;
        openModal();
    }

    async function archiveAsset(id) {
        if (!confirm('Archive this asset?')) return;
        try {
            const res = await fetch('../api/archive_management.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ archive_type: 'asset_onboarding', item_id: id, original_table: 'asset_onboarding_registration', reason: 'Archived from onboarding' }) });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({ text: 'Asset archived', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #16a34a, #86efac)' }).showToast();
                fetchAssets();
            } else throw new Error(data.message || 'Archive failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error archiving asset', duration: 4000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const itemId = document.getElementById('itemId').value;
        const receivingId = document.getElementById('receiving_id').value;
        
        if (!receivingId && !itemId) {
            Toastify({ text: 'Please select a Receiving Record', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
            return;
        }

        const payload = {
            id: itemId || undefined,
            receiving_id: receivingId,
            asset_tag: document.getElementById('asset_tag').value,
            asset_name: document.getElementById('asset_name').value,
            asset_type: document.getElementById('asset_type').value,
            serial_number: document.getElementById('serial_number').value,
            registration_date: document.getElementById('registration_date').value,
            registered_by: document.getElementById('registered_by').value,
            status: document.getElementById('status').value
        };
        try {
            const method = itemId ? 'PUT' : 'POST';
            const res = await fetch('../api/asset_onboarding_registration.php', { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            const result = await res.json();
            if (res.ok && result.status === 'success') {
                Toastify({ text: result.message || 'Saved', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #16a34a, #86efac)' }).showToast();
                closeModalFn();
                fetchAssets();
            } else {
                throw new Error(result.message || res.statusText || 'Save failed');
            }
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error saving asset: ' + (err.message || ''), duration: 5000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
        }
    });

    applyFilterBtn.addEventListener('click', () => fetchAssets(document.getElementById('filterInput').value));
    clearFilterBtn.addEventListener('click', () => { document.getElementById('filterInput').value = ''; fetchAssets(); });

    window.archiveAsset = archiveAsset;
    window.editAsset = editAsset;

    loadReceivingRecords();
    fetchAssets();
});
