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
        <span>Document Tracking - Upload Document and Tracking</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Upload Document and Tracking</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Upload Document</button>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-col md:flex-row items-center gap-4">
            <label class="text-gray-700 font-medium whitespace-nowrap">Status:</label>
            <select id="filterStatus" class="w-full md:w-48 border rounded px-3 py-2">
                <option value="">All</option>
                <option value="Uploaded">Uploaded</option>
                <option value="Verified">Verified</option>
                <option value="Rejected">Rejected</option>
            </select>
            <button id="applyFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Search</button>
            <button id="clearFilter" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Clear</button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">File Name</th>
                    <th class="px-6 py-3">Uploader</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Upload Date</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="uploadsTable"></tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">No uploads found</div>
    </div>
</div>

<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-[100] overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative my-8">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Upload Document</h2>
        <form id="uploadForm" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium">Request ID (optional)</label>
                <input id="request_id" type="number" class="w-full border rounded px-3 py-2" placeholder="Link to request" />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Document File *</label>
                <input id="document" type="file" class="w-full border rounded px-3 py-2" accept=".pdf,.doc,.docx,.jpg,.png" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Status</label>
                <select id="status" class="w-full border rounded px-3 py-2">
                    <option value="Uploaded">Uploaded</option>
                    <option value="Verified">Verified</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Upload</button>
            </div>
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const form = document.getElementById('uploadForm');
    const tableBody = document.getElementById('uploadsTable');
    const emptyState = document.getElementById('emptyState');
    const applyFilterBtn = document.getElementById('applyFilter');
    const clearFilterBtn = document.getElementById('clearFilter');

    function openModal() {
        document.getElementById('modalTitle').textContent = 'Upload Document';
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

    async function fetchUploads() {
        try {
            const status = document.getElementById('filterStatus').value;

            const params = new URLSearchParams();
            if (status) params.append('status', status);

            const url = `../api/upload_document_tracking.php?${params.toString()}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.status === 'success' && data.uploads.length) {
                renderUploads(data.uploads);
            } else {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            console.error(err);
            Toastify({
                text: 'Error loading uploads',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444'
            }).showToast();
        }
    }

    function renderUploads(uploads) {
        emptyState.classList.add('hidden');
        tableBody.innerHTML = uploads.map(u => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-3">${u.id}</td>
                <td class="px-6 py-3"><a href="${u.file_path}" target="_blank" class="text-indigo-600 hover:underline">${u.file_name}</a></td>
                <td class="px-6 py-3">${u.uploader_name}</td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${u.status === 'Verified' ? 'bg-green-100 text-green-800' : u.status === 'Uploaded' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'}">${u.status}</span></td>
                <td class="px-6 py-3">${u.upload_date}</td>
                <td class="px-6 py-3 flex gap-2"><button onclick='updateStatus(${u.id})' class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Update Status</button><button onclick="deleteUpload(${u.id})" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Delete</button></td>
            </tr>
        `).join('');
    }

    function updateStatus(id) {
        const newStatus = prompt('Enter new status (Uploaded/Verified/Rejected):');
        if (newStatus) {
            fetch('../api/upload_document_tracking.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status: newStatus })
            }).then(res => res.json()).then(result => {
                if (result.status === 'success') {
                    Toastify({ text: result.message, duration: 2500, backgroundColor: '#10b981' }).showToast();
                    fetchUploads();
                } else {
                    Toastify({ text: result.message, duration: 3000, backgroundColor: '#ef4444' }).showToast();
                }
            });
        }
    }

    async function deleteUpload(id) {
        if (!confirm('Delete this upload?')) return;
        try {
            const res = await fetch('../api/upload_document_tracking.php', { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({ text: 'Upload deleted', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                fetchUploads();
            } else throw new Error(data.message || 'Delete failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error deleting upload', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('document', document.getElementById('document').files[0]);
        formData.append('request_id', document.getElementById('request_id').value);
        formData.append('status', document.getElementById('status').value);

        try {
            const res = await fetch('../api/upload_document_tracking.php', { method: 'POST', body: formData });
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({ text: result.message || 'Uploaded', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                closeModal();
                fetchUploads();
            } else {
                throw new Error(result.message || 'Failed');
            }
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error uploading document', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    });

    applyFilterBtn.addEventListener('click', fetchUploads);
    clearFilterBtn.addEventListener('click', () => {
        document.getElementById('filterStatus').value = '';
        fetchUploads();
    });

    window.updateStatus = updateStatus;
    window.deleteUpload = deleteUpload;

    fetchUploads();
});
</script>
HTML;
adminLayout($children);
?>