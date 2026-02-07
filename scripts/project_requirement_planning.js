document.addEventListener('DOMContentLoaded', function() {
    loadProjects();
    document.getElementById('addProjectBtn').addEventListener('click', function() {
        document.getElementById('projectId').value = '';
        document.getElementById('projectForm').reset();
        openProjectModal();
    });
    document.getElementById('projectForm').addEventListener('submit', saveProject);
    document.getElementById('searchInput').addEventListener('keyup', searchProjects);
});

async function loadProjects() {
    try {
        const response = await fetch('../api/project_requirement_planning.php', { method: 'GET' });
        const data = await response.json();
        if (data.status === 'success') renderProjects(data.projects);
    } catch (error) { Toastify({ text: 'Error loading projects', backgroundColor: '#ff4757' }).showToast(); }
}

function renderProjects(projects) {
    const tbody = document.getElementById('projectTable');
    tbody.innerHTML = projects.map(p => `
        <tr class="border-t">
            <td class="px-6 py-4">${p.project_id}</td>
            <td class="px-6 py-4">${p.project_name}</td>
            <td class="px-6 py-4">${p.start_date}</td>
            <td class="px-6 py-4">${p.end_date}</td>
            <td class="px-6 py-4">$${parseFloat(p.total_budget || 0).toFixed(2)}</td>
            <td class="px-6 py-4"><span class="bg-blue-200 text-blue-800 px-2 py-1 rounded">${p.project_status}</span></td>
            <td class="px-6 py-4">
                <button onclick="editProject(${p.id})" class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                <button onclick="archiveProject(${p.id})" class="text-orange-600 hover:text-orange-800">Archive</button>
            </td>
        </tr>
    `).join('') || '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No projects found</td></tr>';
}

function openProjectModal() {
    document.getElementById('projectModal').classList.remove('hidden');
}

function closeProjectModal() { document.getElementById('projectModal').classList.add('hidden'); }

async function saveProject(e) {
    e.preventDefault();
    const id = document.getElementById('projectId').value;
    // generate a safe project_id if none provided
    let generatedProjectId = '';
    if (!id) {
        const name = (document.getElementById('projectName').value || '').trim();
        const base = name ? name.replace(/[^A-Za-z0-9]/g, '').toUpperCase().slice(0,8) : 'PRJ';
        generatedProjectId = base + '-' + Date.now().toString().slice(-6);
    }

    const payload = {
        id: id,
        project_id: id ? undefined : generatedProjectId,
        project_name: document.getElementById('projectName').value,
        start_date: document.getElementById('startDate').value || null,
        end_date: document.getElementById('endDate').value || null,
        total_budget: parseFloat(document.getElementById('totalBudget').value) || 0,
        logistics_scope: document.getElementById('projectScope').value || 'Multi-Phase',
        project_status: document.getElementById('projectStatus').value
    };
    
    try {
        const response = await fetch('../api/project_requirement_planning.php', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.status === 'success') {
            Toastify({ text: data.message || 'Saved', backgroundColor: '#2ed573' }).showToast();
            closeProjectModal();
            loadProjects();
        } else {
            throw new Error(data.message || 'Failed to save project');
        }
    } catch (error) { Toastify({ text: error.message, backgroundColor: '#ff4757' }).showToast(); }
}

async function editProject(id) {
    try {
        const response = await fetch('../api/project_requirement_planning.php', { method: 'GET' });
        const data = await response.json();
        const project = data.projects.find(p => p.id == id);
        if (project) {
            document.getElementById('projectId').value = project.id;
            document.getElementById('projectName').value = project.project_name;
            document.getElementById('startDate').value = project.start_date;
            document.getElementById('endDate').value = project.end_date;
            document.getElementById('totalBudget').value = project.total_budget;
            document.getElementById('projectScope').value = project.logistics_scope;
            document.getElementById('projectStatus').value = project.project_status;
            openProjectModal();
        }
    } catch (error) { Toastify({ text: 'Error loading project', backgroundColor: '#ff4757' }).showToast(); }
}

async function archiveProject(id) {
    if (!confirm('Are you sure?')) return;
    try {
        const response = await fetch('../api/archive_management.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ archive_type: 'project', item_id: id, original_table: 'project_requirement_planning', reason: 'Archived from project planning' })
        });
        const data = await response.json();
        if (data.status === 'success') {
            Toastify({ text: 'Project archived', backgroundColor: '#2ed573' }).showToast();
            loadProjects();
        } else throw new Error(data.message);
    } catch (error) { Toastify({ text: error.message, backgroundColor: '#ff4757' }).showToast(); }
}

function searchProjects() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#projectTable tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}
