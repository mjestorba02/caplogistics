document.addEventListener('DOMContentLoaded', function() {
    loadShipments();
    document.getElementById('addShipmentBtn').addEventListener('click', function() {
        document.getElementById('shipmentId').value = '';
        document.getElementById('shipmentForm').reset();
        openShipmentModal();
    });
    document.getElementById('shipmentForm').addEventListener('submit', saveShipment);
    document.getElementById('searchInput').addEventListener('keyup', searchShipments);
});

async function loadShipments() {
    try {
        const response = await fetch('../api/shipment_scheduling_route_planning.php', { method: 'GET' });
        const data = await response.json();
        if (data.status === 'success') renderShipments(data.records);
    } catch (error) { Toastify({ text: 'Error loading shipments', backgroundColor: '#ff4757' }).showToast(); }
}

function renderShipments(shipments) {
    const tbody = document.getElementById('shipmentTable');
    tbody.innerHTML = shipments.map(s => `
        <tr class="border-t">
            <td class="px-6 py-4">${s.shipment_id}</td>
            <td class="px-6 py-4">${s.project_id}</td>
            <td class="px-6 py-4">${s.origin_location}</td>
            <td class="px-6 py-4">${s.destination_location}</td>
            <td class="px-6 py-4">${s.transport_mode}</td>
            <td class="px-6 py-4"><span class="bg-purple-200 text-purple-800 px-2 py-1 rounded">${s.status}</span></td>
            <td class="px-6 py-4">
                <button onclick="editShipment(${s.id})" class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                <button onclick="archiveShipment(${s.id})" class="text-orange-600 hover:text-orange-800">Archive</button>
            </td>
        </tr>
    `).join('') || '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No shipments found</td></tr>';
}

function openShipmentModal() {
    document.getElementById('shipmentModal').classList.remove('hidden');
}

function closeShipmentModal() { document.getElementById('shipmentModal').classList.add('hidden'); }

async function saveShipment(e) {
    e.preventDefault();
    const id = document.getElementById('shipmentId').value;
    const payload = {
        id: id,
        project_id: document.getElementById('projectId').value,
        shipment_number: document.getElementById('shipmentNumber').value,
        origin_location: document.getElementById('originLocation').value,
        destination_location: document.getElementById('destinationLocation').value,
        transport_mode: document.getElementById('transportMode').value,
        carrier_name: document.getElementById('carrierName').value,
        total_cost: document.getElementById('totalCost').value,
        status: document.getElementById('status').value
    };
    
    try {
        const response = await fetch('../api/shipment_scheduling_route_planning.php', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.status === 'success') {
            Toastify({ text: data.message, backgroundColor: '#2ed573' }).showToast();
            closeShipmentModal();
            loadShipments();
        } else throw new Error(data.message);
    } catch (error) { Toastify({ text: error.message, backgroundColor: '#ff4757' }).showToast(); }
}

async function editShipment(id) {
    try {
        const response = await fetch('../api/shipment_scheduling_route_planning.php', { method: 'GET' });
        const data = await response.json();
        const shipment = data.records.find(s => s.id == id);
        if (shipment) {
            document.getElementById('shipmentId').value = shipment.id;
            document.getElementById('projectId').value = shipment.project_id;
            document.getElementById('shipmentNumber').value = shipment.shipment_number;
            document.getElementById('originLocation').value = shipment.origin_location;
            document.getElementById('destinationLocation').value = shipment.destination_location;
            document.getElementById('transportMode').value = shipment.transport_mode;
            document.getElementById('carrierName').value = shipment.carrier_name;
            document.getElementById('totalCost').value = shipment.total_cost;
            document.getElementById('status').value = shipment.status;
            openShipmentModal();
        }
    } catch (error) { Toastify({ text: 'Error loading shipment', backgroundColor: '#ff4757' }).showToast(); }
}

async function archiveShipment(id) {
    if (!confirm('Are you sure?')) return;
    try {
        const response = await fetch('../api/archive_management.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ archive_type: 'shipment_scheduling', item_id: id, original_table: 'shipment_scheduling_route_planning', reason: 'Archived from shipment scheduling' })
        });
        const data = await response.json();
        if (data.status === 'success') {
            Toastify({ text: 'Shipment archived', backgroundColor: '#2ed573' }).showToast();
            loadShipments();
        } else throw new Error(data.message);
    } catch (error) { Toastify({ text: error.message, backgroundColor: '#ff4757' }).showToast(); }
}

function searchShipments() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#shipmentTable tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}
