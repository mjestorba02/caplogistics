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
                <td class="px-6 py-3">${r.id}</td>
                <td class="px-6 py-3">${r.item_name}</td>
                <td class="px-6 py-3">${r.quantity}</td>
                <td class="px-6 py-3">${r.requester_name}</td>
                <td class="px-6 py-3">${r.date_requested}</td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${r.status === 'Approved' ? 'bg-green-100 text-green-800' : r.status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">${r.status}</span></td>
                <td class="px-6 py-3 flex gap-2"><button onclick='editRequest(${JSON.stringify(r).replace(/"/g, '&quot;')})' class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Edit</button><button onclick="deleteRequest(${r.id})" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Delete</button></td>
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

    async function deleteRequest(id) {
        if (!confirm('Delete this request?')) return;
        try {
            const res = await fetch('../api/request_supplies.php', { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({ text: 'Request deleted', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                fetchRequests();
            } else throw new Error(data.message || 'Delete failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error deleting request', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
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

    window.deleteRequest = deleteRequest;
    window.editRequest = editRequest;

    fetchRequests();
});