document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const form = document.getElementById('requestForm');
    const tableBody = document.getElementById('requestsTable');
    const emptyState = document.getElementById('emptyState');
    const applySearchBtn = document.getElementById('applySearch');
    const clearSearchBtn = document.getElementById('clearSearch');

    function openModal() {
        document.getElementById('modalTitle').textContent = 'Request Supply';
        document.getElementById('requestId').value = '';
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

    async function fetchRequests() {
        try {
            const search = document.getElementById('searchInput').value;
            const dateFrom = document.getElementById('dateFrom')?.value || '';
            const dateTo = document.getElementById('dateTo')?.value || '';

            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);

            const url = `../api/request_supplies.php?${params.toString()}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.status === 'success' && data.requests.length) {
                renderRequests(data.requests);
            } else {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            console.error(err);
            Toastify({
                text: 'Error loading requests',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444'
            }).showToast();
        }
    }

    function renderRequests(requests) {
        emptyState.classList.add('hidden');
        tableBody.innerHTML = requests.map(r => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-3 text-sm text-gray-500">#${r.id}</td>
                <td class="px-6 py-3 font-semibold">${r.item_name}</td>
                <td class="px-6 py-3">
                    <span class="px-2 py-1 rounded bg-blue-100 text-blue-800 text-xs font-semibold">${r.quantity}</span>
                </td>
                <td class="px-6 py-3 text-sm">${r.requester_name}</td>
                <td class="px-6 py-3 text-sm">${new Date(r.date_requested).toLocaleDateString()}</td>
                <td class="px-6 py-3">
                    <span class="px-2 py-1 rounded text-xs font-semibold ${r.status === 'Approved' ? 'bg-green-100 text-green-800' : r.status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">${r.status}</span>
                </td>
                <td class="px-6 py-3 flex gap-1">
                    <button onclick='editRequest(${JSON.stringify(r).replace(/"/g, '&quot;')})' class="bg-indigo-600 text-white px-2 py-1 rounded text-xs hover:bg-indigo-700" title="Edit">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    ${r.status === 'Pending' ? `<button onclick="approveRequest(${r.id})" class="bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700" title="Approve & Create PO">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>` : ''}
                    <button onclick="archiveRequest(${r.id})" class="bg-orange-600 text-white px-2 py-1 rounded text-xs hover:bg-orange-700" title="Archive">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function editRequest(request) {
        document.getElementById('modalTitle').textContent = 'Edit Request';
        document.getElementById('requestId').value = request.id;
        document.getElementById('item_name').value = request.item_name;
        document.getElementById('quantity').value = request.quantity;
        document.getElementById('description').value = request.description || '';
        document.getElementById('urgency').value = request.urgency;
        openModal();
    }

    async function archiveRequest(id) {
        if (!confirm('Archive this request?')) return;
        try {
            const res = await fetch('../api/archive_management.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ archive_type: 'request', item_id: id, original_table: 'request_supplies', reason: 'Archived from supply requests' }) });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({ text: 'Request archived', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                fetchRequests();
            } else throw new Error(data.message || 'Archive failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error archiving request', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const requestId = document.getElementById('requestId').value;
        const payload = {
            id: requestId || undefined,
            item_name: document.getElementById('item_name').value,
            quantity: document.getElementById('quantity').value,
            description: document.getElementById('description').value,
            urgency: document.getElementById('urgency').value,
            requester_id: userId,
            requester_name: userName
        };
        try {
            const method = requestId ? 'PUT' : 'POST';
            const res = await fetch('../api/request_supplies.php', { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({ text: result.message || 'Saved', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                closeModal();
                fetchRequests();
            } else throw new Error(result.message || 'Save failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error saving request', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    });

    applySearchBtn.addEventListener('click', fetchRequests);
    clearSearchBtn.addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        if (document.getElementById('dateFrom')) document.getElementById('dateFrom').value = '';
        if (document.getElementById('dateTo')) document.getElementById('dateTo').value = '';
        fetchRequests();
    });

    async function approveRequest(id) {
        try {
            const res = await fetch('../api/request_supplies.php', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({
                    text: data.message || 'Request approved! Purchase Order created.',
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#10b981'
                }).showToast();
                fetchRequests();
            } else throw new Error(data.message || 'Approval failed');
        } catch (err) {
            console.error('Approve Error:', err);
            Toastify({
                text: 'Error approving request: ' + (err.message || 'Unknown error'),
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444'
            }).showToast();
        }
    }

    window.archiveRequest = archiveRequest;
    window.editRequest = editRequest;
    window.approveRequest = approveRequest;

    fetchRequests();
});