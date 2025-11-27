document.addEventListener('DOMContentLoaded', function() {
    loadTracking();
    document.getElementById('addTrackingBtn').addEventListener('click', function() {
        document.getElementById('trackingId').value = '';
        document.getElementById('trackingForm').reset();
        openTrackingModal();
    });
    document.getElementById('trackingForm').addEventListener('submit', saveTracking);
    document.getElementById('searchInput').addEventListener('keyup', searchTracking);
});

async function loadTracking() {
    try {
        const response = await fetch('../api/execution_realtime_tracking.php', { method: 'GET' });
        const data = await response.json();
        if (data.status === 'success') renderTracking(data.records);
    } catch (error) { Toastify({ text: 'Error loading tracking', backgroundColor: '#ff4757' }).showToast(); }
}

function renderTracking(records) {
    const tbody = document.getElementById('trackingTable');
    tbody.innerHTML = records.map(r => `
        <tr class="border-t">
            <td class="px-6 py-4">${r.tracking_id}</td>
            <td class="px-6 py-4">${r.shipment_id}</td>
            <td class="px-6 py-4">${r.current_location}</td>
            <td class="px-6 py-4">${r.gps_coordinates}</td>
            <td class="px-6 py-4"><span class="bg-orange-200 text-orange-800 px-2 py-1 rounded">${r.tracking_status}</span></td>
            <td class="px-6 py-4">
                <button onclick="editTracking(${r.id})" class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                <button onclick="deleteTracking(${r.id})" class="text-red-600 hover:text-red-800">Delete</button>
            </td>
        </tr>
    `).join('') || '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No records found</td></tr>';
}

function openTrackingModal() {
    document.getElementById('trackingModal').classList.remove('hidden');
}

function closeTrackingModal() { document.getElementById('trackingModal').classList.add('hidden'); }

async function saveTracking(e) {
    e.preventDefault();
    const id = document.getElementById('trackingId').value;
    const payload = {
        id: id,
        shipment_id: document.getElementById('shipmentId').value,
        current_location: document.getElementById('currentLocation').value,
        gps_coordinates: document.getElementById('gpsCoordinates').value,
        speed_kmh: document.getElementById('speedKmh').value,
        temperature_reading: document.getElementById('temperatureReading').value,
        vehicle_condition: document.getElementById('vehicleCondition').value,
        tracking_status: document.getElementById('trackingStatus').value
    };
    
    try {
        const response = await fetch('../api/execution_realtime_tracking.php', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.status === 'success') {
            Toastify({ text: data.message, backgroundColor: '#2ed573' }).showToast();
            closeTrackingModal();
            loadTracking();
        } else throw new Error(data.message);
    } catch (error) { Toastify({ text: error.message, backgroundColor: '#ff4757' }).showToast(); }
}

async function editTracking(id) {
    try {
        const response = await fetch('../api/execution_realtime_tracking.php', { method: 'GET' });
        const data = await response.json();
        const record = data.records.find(r => r.id == id);
        if (record) {
            document.getElementById('trackingId').value = record.id;
            document.getElementById('shipmentId').value = record.shipment_id;
            document.getElementById('currentLocation').value = record.current_location;
            document.getElementById('gpsCoordinates').value = record.gps_coordinates;
            document.getElementById('speedKmh').value = record.speed_kmh;
            document.getElementById('temperatureReading').value = record.temperature_reading;
            document.getElementById('vehicleCondition').value = record.vehicle_condition;
            document.getElementById('trackingStatus').value = record.tracking_status;
            openTrackingModal();
        }
    } catch (error) { Toastify({ text: 'Error loading record', backgroundColor: '#ff4757' }).showToast(); }
}

async function deleteTracking(id) {
    if (!confirm('Are you sure?')) return;
    try {
        const response = await fetch('../api/execution_realtime_tracking.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await response.json();
        if (data.status === 'success') {
            Toastify({ text: 'Record deleted', backgroundColor: '#2ed573' }).showToast();
            loadTracking();
        } else throw new Error(data.message);
    } catch (error) { Toastify({ text: error.message, backgroundColor: '#ff4757' }).showToast(); }
}

function searchTracking() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#trackingTable tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}
