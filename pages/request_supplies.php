<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location:http://localhost/caplog1');
    exit();
}
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <span>Procurement - Request Supplies</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Request Supplies</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Request New Supply
        </button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Search:</label>
            <input id="searchInput" type="text" placeholder="Search by Item or Requester..." class="w-full md:w-48 border rounded px-3 py-2" />
            <input type="date" id="dateFrom" class="border rounded px-3 py-2 text-sm">
            <input type="date" id="dateTo" class="border rounded px-3 py-2 text-sm">
            <button id="applySearch" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
            </button>
            <button id="clearSearch" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Clear
            </button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Item Name</th>
                    <th class="px-6 py-3">Quantity</th>
                    <th class="px-6 py-3">Requester</th>
                    <th class="px-6 py-3">Date Requested</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="requestsTable"></tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">No requests found</div>
    </div>
</div>

<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-[100] overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative my-8">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Request Supply</h2>
        <form id="requestForm" class="space-y-4">
            <input type="hidden" id="requestId" />
            <div>
                <label class="block text-gray-700 font-medium">Item Name *</label>
                <input id="item_name" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., Office Supplies" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Quantity *</label>
                <input id="quantity" type="number" class="w-full border rounded px-3 py-2" placeholder="0" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Description</label>
                <textarea id="description" class="w-full border rounded px-3 py-2" rows="3" placeholder="Additional details"></textarea>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Urgency</label>
                <select id="urgency" class="w-full border rounded px-3 py-2">
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Submit Request</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script>
// Temporary hardcoded values for testing
const userId = 1;
const userName = 'Test User';
</script>
<script>
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
                    <button onclick="deleteRequest(${r.id})" class="bg-red-600 text-white px-2 py-1 rounded text-xs hover:bg-red-700" title="Delete">
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
    window.approveRequest = approveRequest;

    fetchRequests();
});
</script>
HTML;
adminLayout($children);
?>