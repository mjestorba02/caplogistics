document.addEventListener('DOMContentLoaded', function() {
    loadCompliance();
    document.getElementById('addComplianceBtn').addEventListener('click', function() {
        document.getElementById('complianceId').value = '';
        document.getElementById('complianceForm').reset();
        openComplianceModal();
    });
    document.getElementById('complianceForm').addEventListener('submit', saveCompliance);
    document.getElementById('searchInput').addEventListener('keyup', searchCompliance);
});

async function loadCompliance() {
    try {
        const response = await fetch('../api/customs_regulatory_compliance.php', { method: 'GET' });
        const data = await response.json();
        if (data.status === 'success') renderCompliance(data.records);
    } catch (error) { Toastify({ text: 'Error loading records', backgroundColor: '#ff4757' }).showToast(); }
}

function renderCompliance(records) {
    const tbody = document.getElementById('complianceTable');
    tbody.innerHTML = records.map(r => `
        <tr class="border-t">
            <td class="px-6 py-4">${r.compliance_id}</td>
            <td class="px-6 py-4">${r.shipment_id}</td>
            <td class="px-6 py-4"><span class="bg-indigo-200 text-indigo-800 px-2 py-1 rounded">${r.declaration_status}</span></td>
            <td class="px-6 py-4"><span class="bg-yellow-200 text-yellow-800 px-2 py-1 rounded">${r.customs_clearance_status}</span></td>
            <td class="px-6 py-4">${r.permits_obtained ? 'Yes' : 'No'}</td>
            <td class="px-6 py-4">
                <button onclick="editCompliance(${r.id})" class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                <button onclick="archiveCompliance(${r.id})" class="text-orange-600 hover:text-orange-800">Archive</button>
            </td>
        </tr>
    `).join('') || '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No records found</td></tr>';
}

function openComplianceModal() {
    document.getElementById('complianceModal').classList.remove('hidden');
}

function closeComplianceModal() { document.getElementById('complianceModal').classList.add('hidden'); }

async function saveCompliance(e) {
    e.preventDefault();
    const id = document.getElementById('complianceId').value;
    const payload = {
        id: id,
        compliance_id: document.getElementById('complianceIdInput').value,
        shipment_id: document.getElementById('shipmentId').value,
        declaration_status: document.getElementById('declarationStatus').value,
        customs_clearance_status: document.getElementById('customsClearanceStatus').value,
        permits_obtained: document.getElementById('permitsObtained').checked ? 1 : 0
    };
    
    try {
        const response = await fetch('../api/customs_regulatory_compliance.php', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.status === 'success') {
            Toastify({ text: data.message, backgroundColor: '#2ed573' }).showToast();
            closeComplianceModal();
            loadCompliance();
        } else throw new Error(data.message);
    } catch (error) { Toastify({ text: error.message, backgroundColor: '#ff4757' }).showToast(); }
}

async function editCompliance(id) {
    try {
        const response = await fetch('../api/customs_regulatory_compliance.php', { method: 'GET' });
        const data = await response.json();
        const record = data.records.find(r => r.id == id);
        if (record) {
            document.getElementById('complianceId').value = record.id;
            document.getElementById('complianceIdInput').value = record.compliance_id;
            document.getElementById('shipmentId').value = record.shipment_id;
            document.getElementById('declarationStatus').value = record.declaration_status;
            document.getElementById('customsClearanceStatus').value = record.customs_clearance_status;
            document.getElementById('permitsObtained').checked = record.permits_obtained;
            openComplianceModal();
        }
    } catch (error) { Toastify({ text: 'Error loading record', backgroundColor: '#ff4757' }).showToast(); }
}

async function archiveCompliance(id) {
    if (!confirm('Are you sure?')) return;
    try {
        const response = await fetch('../api/archive_management.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ archive_type: 'compliance', item_id: id, original_table: 'customs_regulatory_compliance', reason: 'Archived from compliance' })
        });
        const data = await response.json();
        if (data.status === 'success') {
            Toastify({ text: 'Record archived', backgroundColor: '#2ed573' }).showToast();
            loadCompliance();
        } else throw new Error(data.message);
    } catch (error) { Toastify({ text: error.message, backgroundColor: '#ff4757' }).showToast(); }
}

function searchCompliance() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#complianceTable tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}
