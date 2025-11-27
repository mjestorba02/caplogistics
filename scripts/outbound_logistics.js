document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const form = document.getElementById('shipmentForm');
    const tableBody = document.getElementById('shipmentsTable');
    const emptyState = document.getElementById('emptyState');
    const applySearchBtn = document.getElementById('applySearch');
    const clearSearchBtn = document.getElementById('clearSearch');

    function openModal() {
        document.getElementById('modalTitle').textContent = 'Create Shipment';
        document.getElementById('shipmentId').value = '';
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

    async function fetchShipments(search = '') {
        try {
            const url = `../api/outbound_logistics.php${search ? '?search=' + encodeURIComponent(search) : ''}`;
            const res = await fetch(url);
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.shipments) && data.shipments.length) {
                renderShipments(data.shipments);
            } else {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            console.error('Error fetching shipments:', err);
            Toastify({ text: 'Error loading shipments', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    }

    function renderShipments(shipments) {
        emptyState.classList.add('hidden');
        tableBody.innerHTML = shipments.map(s => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-3">${s.id}</td>
                <td class="px-6 py-3 font-semibold">${s.shipment_number}</td>
                <td class="px-6 py-3">${s.order_id || '-'}</td>
                <td class="px-6 py-3">${s.customer_name}</td>
                <td class="px-6 py-3">${s.total_items}</td>
                <td class="px-6 py-3">${s.carrier_name || '-'}</td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${s.delivery_status === 'Delivered' ? 'bg-green-100 text-green-800' : s.delivery_status === 'Dispatched' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800'}">${s.delivery_status}</span></td>
                <td class="px-6 py-3 flex gap-2"><button onclick='editShipment(${JSON.stringify(s).replace(/"/g, '&quot;')})' class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Edit</button><button onclick="deleteShipment(${s.id})" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Delete</button></td>
            </tr>
        `).join('');
    }

    function editShipment(s) {
        document.getElementById('modalTitle').textContent = 'Edit Shipment';
        document.getElementById('shipmentId').value = s.id;
        document.getElementById('shipment_number').value = s.shipment_number;
        document.getElementById('order_id').value = s.order_id || '';
        document.getElementById('customer_name').value = s.customer_name;
        document.getElementById('customer_email').value = s.customer_email || '';
        document.getElementById('delivery_address').value = s.delivery_address || '';
        document.getElementById('total_items').value = s.total_items;
        document.getElementById('carrier_name').value = s.carrier_name || '';
        document.getElementById('delivery_status').value = s.delivery_status;
        openModal();
    }

    async function deleteShipment(id) {
        if (!confirm('Delete this shipment?')) return;
        try {
            const res = await fetch('../api/outbound_logistics.php', { method: 'DELETE', body: JSON.stringify({ id }) });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({ text: 'Shipment deleted', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                fetchShipments();
            } else throw new Error(data.message || 'Delete failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error deleting shipment', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const shipmentId = document.getElementById('shipmentId').value;
        const payload = {
            id: shipmentId || undefined,
            shipment_number: document.getElementById('shipment_number').value,
            order_id: document.getElementById('order_id').value,
            customer_name: document.getElementById('customer_name').value,
            customer_email: document.getElementById('customer_email').value,
            delivery_address: document.getElementById('delivery_address').value,
            total_items: document.getElementById('total_items').value,
            carrier_name: document.getElementById('carrier_name').value,
            delivery_status: document.getElementById('delivery_status').value,
            notes: ''
        };
        try {
            const method = shipmentId ? 'PUT' : 'POST';
            const res = await fetch('../api/outbound_logistics.php', { method, body: JSON.stringify(payload) });
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({ text: result.message || 'Saved', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                closeModal();
                fetchShipments();
            } else throw new Error(result.message || 'Save failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error saving shipment', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    });

    applySearchBtn.addEventListener('click', () => fetchShipments(document.getElementById('searchInput').value));
    clearSearchBtn.addEventListener('click', () => { document.getElementById('searchInput').value = ''; fetchShipments(); });

    window.deleteShipment = deleteShipment;
    window.editShipment = editShipment;

    fetchShipments();
});
