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
        <span>Logistic Tracking - Project Planning and Request</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Project Planning and Request</h1>
        <div class="flex gap-2">
            <button id="openProjectModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Add Project</button>
            <button id="openRequestModal" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Add Request</button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Projects</h2>
            <div id="projectsList" class="space-y-2"></div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Project Requests</h2>
            <div id="requestsList" class="space-y-2"></div>
        </div>
    </div>
</div>

<!-- Project Modal -->
<div id="projectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-[100]">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
        <h2 id="projectModalTitle" class="text-2xl font-bold mb-4">Add Project</h2>
        <form id="projectForm" class="space-y-4">
            <input type="hidden" id="projectId" />
            <div>
                <label class="block text-gray-700 font-medium">Project Name *</label>
                <input id="project_name" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Description</label>
                <textarea id="project_description" class="w-full border rounded px-3 py-2" rows="3"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium">Start Date</label>
                    <input id="start_date" type="date" class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="block text-gray-700 font-medium">End Date</label>
                    <input id="end_date" type="date" class="w-full border rounded px-3 py-2" />
                </div>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Status</label>
                <select id="project_status" class="w-full border rounded px-3 py-2">
                    <option value="Planning">Planning</option>
                    <option value="InProgress">In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeProjectModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
            </div>
        </form>
        <button id="closeProjectModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<!-- Request Modal -->
<div id="requestModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-[100]">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
        <h2 class="text-2xl font-bold mb-4">Add Request</h2>
        <form id="requestForm" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium">Project</label>
                <select id="request_project_id" class="w-full border rounded px-3 py-2">
                    <option value="">Select Project</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Request Type *</label>
                <input id="request_type" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., Material, Equipment" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Description</label>
                <textarea id="request_description" class="w-full border rounded px-3 py-2" rows="3"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeRequestModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Submit</button>
            </div>
        </form>
        <button id="closeRequestModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const projectModal = document.getElementById('projectModal');
    const requestModal = document.getElementById('requestModal');
    const openProjectModalBtn = document.getElementById('openProjectModal');
    const openRequestModalBtn = document.getElementById('openRequestModal');
    const closeProjectModalBtn = document.getElementById('closeProjectModal');
    const closeProjectModalBtn2 = document.getElementById('closeProjectModalBtn');
    const closeRequestModalBtn = document.getElementById('closeRequestModal');
    const closeRequestModalBtn2 = document.getElementById('closeRequestModalBtn');
    const projectForm = document.getElementById('projectForm');
    const requestForm = document.getElementById('requestForm');

    function closeProjectModal() {
        projectModal.classList.add('hidden');
        projectModal.classList.remove('flex');
        projectForm.reset();
    }

    function closeRequestModal() {
        requestModal.classList.add('hidden');
        requestModal.classList.remove('flex');
        requestForm.reset();
    }

    openProjectModalBtn.addEventListener('click', () => {
        document.getElementById('projectModalTitle').textContent = 'Add Project';
        document.getElementById('projectId').value = '';
        projectModal.classList.remove('hidden');
        projectModal.classList.add('flex');
    });

    openRequestModalBtn.addEventListener('click', () => {
        loadProjectsForRequest();
        requestModal.classList.remove('hidden');
        requestModal.classList.add('flex');
    });

    closeProjectModalBtn.addEventListener('click', closeProjectModal);
    closeProjectModalBtn2.addEventListener('click', closeProjectModal);
    closeRequestModalBtn.addEventListener('click', closeRequestModal);
    closeRequestModalBtn2.addEventListener('click', closeRequestModal);

    async function fetchData() {
        try {
            const res = await fetch('../api/project_planning_request.php');
            const data = await res.json();
            if (data.status === 'success') {
                renderProjects(data.projects);
                renderRequests(data.requests);
            }
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error loading data', duration: 3000, backgroundColor: '#ef4444' }).showToast();
        }
    }

    function renderProjects(projects) {
        const list = document.getElementById('projectsList');
        list.innerHTML = projects.map(p => `
            <div class="border rounded p-3">
                <h3 class="font-bold">${p.project_name}</h3>
                <p class="text-sm text-gray-600">${p.description || 'No description'}</p>
                <p class="text-sm">Status: <span class="px-2 py-1 rounded text-xs ${p.status === 'Completed' ? 'bg-green-100 text-green-800' : p.status === 'InProgress' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100'}">${p.status}</span></p>
                <div class="mt-2 flex gap-2">
                    <button onclick='editProject(${JSON.stringify(p)})' class="text-indigo-600 text-sm">Edit</button>
                    <button onclick="archiveProject(${p.id})" class="text-yellow-600 text-sm">Archive</button>
                </div>
            </div>
        `).join('');
    }

    function renderRequests(requests) {
        const list = document.getElementById('requestsList');
        list.innerHTML = requests.map(r => `
            <div class="border rounded p-3">
                <h3 class="font-bold">${r.request_type}</h3>
                <p class="text-sm text-gray-600">${r.description || 'No description'}</p>
                <p class="text-sm">Project: ${r.project_name || 'N/A'}</p>
                <p class="text-sm">Status: <span class="px-2 py-1 rounded text-xs ${r.status === 'Approved' ? 'bg-green-100 text-green-800' : r.status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100'}">${r.status}</span></p>
                <div class="mt-2 flex gap-2">
                    <button onclick='updateRequestStatus(${r.id})' class="text-indigo-600 text-sm">Update Status</button>
                    <button onclick="archiveRequest(${r.id})" class="text-yellow-600 text-sm">Archive</button>
                </div>
            </div>
        `).join('');
    }

    function editProject(project) {
        document.getElementById('projectModalTitle').textContent = 'Edit Project';
        document.getElementById('projectId').value = project.id;
        document.getElementById('project_name').value = project.project_name;
        document.getElementById('project_description').value = project.description || '';
        document.getElementById('start_date').value = project.start_date || '';
        document.getElementById('end_date').value = project.end_date || '';
        document.getElementById('project_status').value = project.status;
        projectModal.classList.remove('hidden');
        projectModal.classList.add('flex');
    }

    async function archiveProject(id) {
        if (!confirm('Archive this project? It will be recoverable from Archive.')) return;
        try {
            const payload = { archive_type: 'project', item_id: id, original_table: 'projects', reason: 'Archived from UI' };
            const res = await fetch('../api/archive_management.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({ text: data.message || 'Project archived', duration: 2500, backgroundColor: '#10b981' }).showToast();
                fetchData();
            }
        } catch (err) {
            Toastify({ text: 'Error archiving project', duration: 3000, backgroundColor: '#ef4444' }).showToast();
        }
    }

    function updateRequestStatus(id) {
        const status = prompt('Enter new status (Pending/Approved/Rejected):');
        if (status) {
            fetch('../api/project_planning_request.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ request_id: id, status })
            }).then(res => res.json()).then(data => {
                if (data.status === 'success') {
                    Toastify({ text: data.message, duration: 2500, backgroundColor: '#10b981' }).showToast();
                    fetchData();
                }
            });
        }
    }

    async function archiveRequest(id) {
        if (!confirm('Archive this request? It will be recoverable from Archive.')) return;
        try {
            const payload = { archive_type: 'request', item_id: id, original_table: 'project_requests', reason: 'Archived from UI' };
            const res = await fetch('../api/archive_management.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({ text: data.message || 'Request archived', duration: 2500, backgroundColor: '#10b981' }).showToast();
                fetchData();
            }
        } catch (err) {
            Toastify({ text: 'Error archiving request', duration: 3000, backgroundColor: '#ef4444' }).showToast();
        }
    }

    async function loadProjectsForRequest() {
        const res = await fetch('../api/project_planning_request.php');
        const data = await res.json();
        if (data.status === 'success') {
            const select = document.getElementById('request_project_id');
            select.innerHTML = '<option value="">Select Project</option>' + data.projects.map(p => `<option value="${p.id}">${p.project_name}</option>`).join('');
        }
    }

    projectForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            project_id: document.getElementById('projectId').value || undefined,
            project_name: document.getElementById('project_name').value,
            description: document.getElementById('project_description').value,
            start_date: document.getElementById('start_date').value,
            end_date: document.getElementById('end_date').value,
            status: document.getElementById('project_status').value
        };
        try {
            const res = await fetch('../api/project_planning_request.php', {
                method: payload.project_id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({ text: result.message, duration: 2500, backgroundColor: '#10b981' }).showToast();
                closeProjectModal();
                fetchData();
            }
        } catch (err) {
            Toastify({ text: 'Error saving project', duration: 3000, backgroundColor: '#ef4444' }).showToast();
        }
    });

    requestForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            project_id: document.getElementById('request_project_id').value,
            request_type: document.getElementById('request_type').value,
            description: document.getElementById('request_description').value
        };
        try {
            const res = await fetch('../api/project_planning_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({ text: result.message, duration: 2500, backgroundColor: '#10b981' }).showToast();
                closeRequestModal();
                fetchData();
            }
        } catch (err) {
            Toastify({ text: 'Error submitting request', duration: 3000, backgroundColor: '#ef4444' }).showToast();
        }
    });

    window.editProject = editProject;
    window.deleteProject = deleteProject;
    window.updateRequestStatus = updateRequestStatus;
    window.deleteRequest = deleteRequest;

    fetchData();
});
</script>
HTML;
adminLayout($children);
?>