document.addEventListener('DOMContentLoaded', function() {
    loadDelivery();
    document.getElementById('addDeliveryBtn').addEventListener('click', function() {
        document.getElementById('deliveryId').value = '';
        document.getElementById('deliveryForm').reset();
        openDeliveryModal();
    });
    document.getElementById('deliveryForm').addEventListener('submit', saveDelivery);
    document.getElementById('searchInput').addEventListener('keyup', searchDelivery);
});

async function loadDelivery() {
    try {
        const response = await fetch('../api/delivery_site_coordination.php', { method: 'GET' });
        const data = await response.json();
        if (data.status === 'success') renderDelivery(data.records);
    } catch (error) { Toastify({ text: 'Error loading records', backgroundColor: '#ff4757' }).showToast(); }
}

function renderDelivery(records) {
    const tbody = document.getElementById('deliveryTable');
    tbody.innerHTML = records.map(r => `
        <tr class="border-t">
            <td class="px-6 py-4">${r.delivery_id}</td>
            <td class="px-6 py-4">${r.project_id}</td>
            <td class="px-6 py-4">${r.site_address}</td>
            <td class="px-6 py-4"><span class="bg-teal-200 text-teal-800 px-2 py-1 rounded">${r.delivery_status}</span></td>
            <td class="px-6 py-4"><span class="bg-gray-200 text-gray-800 px-2 py-1 rounded">${r.site_preparation}</span></td>
            <td class="px-6 py-4">
                <button onclick="editDelivery(${r.id})" class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                <button onclick="archiveDelivery(${r.id})" class="text-orange-600 hover:text-orange-800">Archive</button>
            </td>
        </tr>
    `).join('') || '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No records found</td></tr>';
}

function openDeliveryModal() {
    document.getElementById('deliveryModal').classList.remove('hidden');
}

function closeDeliveryModal() { document.getElementById('deliveryModal').classList.add('hidden'); }

async function saveDelivery(e) {
    e.preventDefault();
    const id = document.getElementById('deliveryId').value;
    const payload = {
        id: id,
        delivery_id: document.getElementById('deliveryIdInput').value,
        project_id: document.getElementById('projectId').value,
        site_address: document.getElementById('siteAddress').value,
        delivery_status: document.getElementById('deliveryStatus').value,
        site_preparation: document.getElementById('sitePreparation').value,
        receiving_team_assigned: document.getElementById('receivingTeamAssigned').checked ? 1 : 0
    };
    
    try {
        const response = await fetch('../api/delivery_site_coordination.php', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.status === 'success') {
            Toastify({ text: data.message, backgroundColor: '#2ed573' }).showToast();
            closeDeliveryModal();
            loadDelivery();
        } else throw new Error(data.message);
    } catch (error) { Toastify({ text: error.message, backgroundColor: '#ff4757' }).showToast(); }
}

async function editDelivery(id) {
    try {
        const response = await fetch('../api/delivery_site_coordination.php', { method: 'GET' });
        const data = await response.json();
        const record = data.records.find(r => r.id == id);
        if (record) {
            document.getElementById('deliveryId').value = record.id;
            document.getElementById('deliveryIdInput').value = record.delivery_id;
            document.getElementById('projectId').value = record.project_id;
            document.getElementById('siteAddress').value = record.site_address;
            document.getElementById('deliveryStatus').value = record.delivery_status;
            document.getElementById('sitePreparation').value = record.site_preparation;
            document.getElementById('receivingTeamAssigned').checked = record.receiving_team_assigned;
            openDeliveryModal();
        }
    } catch (error) { Toastify({ text: 'Error loading record', backgroundColor: '#ff4757' }).showToast(); }
}

async function archiveDelivery(id) {
    if (!confirm('Are you sure?')) return;
    try {
        const response = await fetch('../api/archive_management.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ archive_type: 'delivery', item_id: id, original_table: 'delivery_site_coordination', reason: 'Archived from delivery coordination' })
        });
        const data = await response.json();
        if (data.status === 'success') {
            Toastify({ text: 'Record archived', backgroundColor: '#2ed573' }).showToast();
            loadDelivery();
        } else throw new Error(data.message);
    } catch (error) { Toastify({ text: error.message, backgroundColor: '#ff4757' }).showToast(); }
}

function searchDelivery() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#deliveryTable tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}
