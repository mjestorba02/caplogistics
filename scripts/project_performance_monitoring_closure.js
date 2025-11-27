document.addEventListener('DOMContentLoaded', function() {
    loadPerformance();
    document.getElementById('addPerformanceBtn').addEventListener('click', function() {
        document.getElementById('performanceId').value = '';
        document.getElementById('performanceForm').reset();
        openPerformanceModal();
    });
    document.getElementById('performanceForm').addEventListener('submit', savePerformance);
    document.getElementById('searchInput').addEventListener('keyup', searchPerformance);
});

async function loadPerformance() {
    try {
        const response = await fetch('../api/project_performance_monitoring_closure.php', { method: 'GET' });
        const data = await response.json();
        if (data.status === 'success') renderPerformance(data.records);
    } catch (error) { Toastify({ text: 'Error loading records', backgroundColor: '#ff4757' }).showToast(); }
}

function renderPerformance(records) {
    const tbody = document.getElementById('performanceTable');
    tbody.innerHTML = records.map(r => `
        <tr class="border-t">
            <td class="px-6 py-4">${r.performance_id}</td>
            <td class="px-6 py-4">${r.project_id}</td>
            <td class="px-6 py-4"><span class="bg-pink-200 text-pink-800 px-2 py-1 rounded">${r.monitoring_status}</span></td>
            <td class="px-6 py-4">${r.on_time_delivery_rate}%</td>
            <td class="px-6 py-4">${r.cost_performance_index}</td>
            <td class="px-6 py-4">
                <button onclick="editPerformance(${r.id})" class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                <button onclick="deletePerformance(${r.id})" class="text-red-600 hover:text-red-800">Delete</button>
            </td>
        </tr>
    `).join('') || '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No records found</td></tr>';
}

function openPerformanceModal() {
    document.getElementById('performanceModal').classList.remove('hidden');
}

function closePerformanceModal() { document.getElementById('performanceModal').classList.add('hidden'); }

async function savePerformance(e) {
    e.preventDefault();
    const id = document.getElementById('performanceId').value;
    const payload = {
        id: id,
        performance_id: document.getElementById('performanceIdInput').value,
        project_id: document.getElementById('projectId').value,
        monitoring_status: document.getElementById('monitoringStatus').value,
        on_time_delivery_rate: document.getElementById('onTimeDeliveryRate').value,
        cost_performance_index: document.getElementById('costPerformanceIndex').value,
        remarks: document.getElementById('remarks').value
    };
    
    try {
        const response = await fetch('../api/project_performance_monitoring_closure.php', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.status === 'success') {
            Toastify({ text: data.message, backgroundColor: '#2ed573' }).showToast();
            closePerformanceModal();
            loadPerformance();
        } else throw new Error(data.message);
    } catch (error) { Toastify({ text: error.message, backgroundColor: '#ff4757' }).showToast(); }
}

async function editPerformance(id) {
    try {
        const response = await fetch('../api/project_performance_monitoring_closure.php', { method: 'GET' });
        const data = await response.json();
        const record = data.records.find(r => r.id == id);
        if (record) {
            document.getElementById('performanceId').value = record.id;
            document.getElementById('performanceIdInput').value = record.performance_id;
            document.getElementById('projectId').value = record.project_id;
            document.getElementById('monitoringStatus').value = record.monitoring_status;
            document.getElementById('onTimeDeliveryRate').value = record.on_time_delivery_rate;
            document.getElementById('costPerformanceIndex').value = record.cost_performance_index;
            document.getElementById('remarks').value = record.remarks;
            openPerformanceModal();
        }
    } catch (error) { Toastify({ text: 'Error loading record', backgroundColor: '#ff4757' }).showToast(); }
}

async function deletePerformance(id) {
    if (!confirm('Are you sure?')) return;
    try {
        const response = await fetch('../api/project_performance_monitoring_closure.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await response.json();
        if (data.status === 'success') {
            Toastify({ text: 'Record deleted', backgroundColor: '#2ed573' }).showToast();
            loadPerformance();
        } else throw new Error(data.message);
    } catch (error) { Toastify({ text: error.message, backgroundColor: '#ff4757' }).showToast(); }
}

function searchPerformance() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#performanceTable tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}
