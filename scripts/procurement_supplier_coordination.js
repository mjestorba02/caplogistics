document.addEventListener('DOMContentLoaded', function() {
    loadCoordination();
    document.getElementById('addCoordinationBtn').addEventListener('click', function() {
        document.getElementById('coordinationId').value = '';
        document.getElementById('coordinationForm').reset();
        openCoordinationModal();
    });
    document.getElementById('coordinationForm').addEventListener('submit', saveCoordination);
    document.getElementById('searchInput').addEventListener('keyup', searchCoordination);
});

async function loadCoordination() {
    try {
        const response = await fetch('../api/procurement_supplier_coordination.php', { method: 'GET' });
        const data = await response.json();
        if (data.status === 'success') renderCoordination(data.records);
    } catch (error) { Toastify({ text: 'Error loading records', backgroundColor: '#ff4757' }).showToast(); }
}

function renderCoordination(records) {
    const tbody = document.getElementById('coordinationTable');
    tbody.innerHTML = records.map(r => `
        <tr class="border-t">
            <td class="px-6 py-4">${r.coordination_id}</td>
            <td class="px-6 py-4">${r.project_id}</td>
            <td class="px-6 py-4">${r.supplier_name}</td>
            <td class="px-6 py-4">${r.po_number}</td>
            <td class="px-6 py-4"><span class="bg-green-200 text-green-800 px-2 py-1 rounded">${r.status}</span></td>
            <td class="px-6 py-4">
                <button onclick="editCoordination(${r.id})" class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                <button onclick="deleteCoordination(${r.id})" class="text-red-600 hover:text-red-800">Delete</button>
            </td>
        </tr>
    `).join('') || '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No records found</td></tr>';
}

function openCoordinationModal() {
    document.getElementById('coordinationModal').classList.remove('hidden');
}

function closeCoordinationModal() { document.getElementById('coordinationModal').classList.add('hidden'); }

async function saveCoordination(e) {
    e.preventDefault();
    const id = document.getElementById('coordinationId').value;
    const payload = {
        id: id,
        project_id: document.getElementById('projectId').value,
        supplier_name: document.getElementById('supplierName').value,
        po_number: document.getElementById('poNumber').value,
        delivery_date: document.getElementById('deliveryDate').value,
        po_amount: document.getElementById('poAmount').value,
        status: document.getElementById('status').value
    };
    
    try {
        const response = await fetch('../api/procurement_supplier_coordination.php', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.status === 'success') {
            Toastify({ text: data.message, backgroundColor: '#2ed573' }).showToast();
            closeCoordinationModal();
            loadCoordination();
        } else throw new Error(data.message);
    } catch (error) { Toastify({ text: error.message, backgroundColor: '#ff4757' }).showToast(); }
}

async function editCoordination(id) {
    try {
        const response = await fetch('../api/procurement_supplier_coordination.php', { method: 'GET' });
        const data = await response.json();
        const record = data.records.find(r => r.id == id);
        if (record) {
            document.getElementById('coordinationId').value = record.id;
            document.getElementById('projectId').value = record.project_id;
            document.getElementById('supplierName').value = record.supplier_name;
            document.getElementById('poNumber').value = record.po_number;
            document.getElementById('deliveryDate').value = record.delivery_date;
            document.getElementById('poAmount').value = record.po_amount;
            document.getElementById('status').value = record.status;
            openCoordinationModal();
        }
    } catch (error) { Toastify({ text: 'Error loading record', backgroundColor: '#ff4757' }).showToast(); }
}

async function deleteCoordination(id) {
    if (!confirm('Are you sure?')) return;
    try {
        const response = await fetch('../api/procurement_supplier_coordination.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await response.json();
        if (data.status === 'success') {
            Toastify({ text: 'Record deleted', backgroundColor: '#2ed573' }).showToast();
            loadCoordination();
        } else throw new Error(data.message);
    } catch (error) { Toastify({ text: error.message, backgroundColor: '#ff4757' }).showToast(); }
}

function searchCoordination() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#coordinationTable tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}
