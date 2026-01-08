<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location:../index.php');
    exit();
}
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <span>Document Tracking - List of Document Request</span>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Status:</label>
            <select id="filterStatus" class="w-full md:w-48 border rounded px-3 py-2">
                <option value="">All</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
                <option value="Completed">Completed</option>
            </select>
            <label class="text-gray-700 font-medium whitespace-nowrap">From Date:</label>
            <input type="date" id="dateFrom" class="w-full md:w-48 border rounded px-3 py-2">
            <label class="text-gray-700 font-medium whitespace-nowrap">To Date:</label>
            <input type="date" id="dateTo" class="w-full md:w-48 border rounded px-3 py-2">
            <button id="applyFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Filter</button>
            <button id="clearFilter" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Clear</button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Requester</th>
                    <th class="px-6 py-3">Document Type</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Request Date</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="requestsTable"></tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">No requests found</div>
    </div>
</div>

<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-[100]">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Update Status</h2>
        <form id="statusForm" class="space-y-4">
            <input type="hidden" id="requestId" />
            <div>
                <label class="block text-gray-700 font-medium">Status</label>
                <select id="status" class="w-full border rounded px-3 py-2">
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Update</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const closeModalBtn = document.getElementById('closeModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const form = document.getElementById('statusForm');
    const tableBody = document.getElementById('requestsTable');
    const emptyState = document.getElementById('emptyState');
    const applyFilterBtn = document.getElementById('applyFilter');
    const clearFilterBtn = document.getElementById('clearFilter');

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        form.reset();
    }

    closeModalBtn.addEventListener('click', closeModal);
    closeBtn.addEventListener('click', closeModal);

    async function fetchRequests() {
        try {
            const status = document.getElementById('filterStatus').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;

            const params = new URLSearchParams();
            if (status) params.append('status', status);
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);

            const url = `../api/list_document_request.php?${params.toString()}`;
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
                <td class="px-6 py-3">${r.requester_name}</td>
                <td class="px-6 py-3">${r.document_type}</td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${r.status === 'Approved' ? 'bg-green-100 text-green-800' : r.status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : r.status === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'}">${r.status}</span></td>
                <td class="px-6 py-3">${r.request_date}</td>
                <td class="px-6 py-3"><button onclick='updateStatus(${r.id})' class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Update Status</button></td>
            </tr>
        `).join('');
    }

    function updateStatus(id) {
        document.getElementById('requestId').value = id;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            id: document.getElementById('requestId').value,
            status: document.getElementById('status').value
        };

        try {
            const res = await fetch('../api/list_document_request.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({ text: result.message || 'Updated', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                closeModal();
                fetchRequests();
            } else {
                throw new Error(result.message || 'Failed');
            }
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error updating status', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    });

    applyFilterBtn.addEventListener('click', fetchRequests);
    clearFilterBtn.addEventListener('click', () => {
        document.getElementById('filterStatus').value = '';
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        fetchRequests();
    });

    window.updateStatus = updateStatus;

    fetchRequests();
});
</script>
HTML;
adminLayout($children);
?>