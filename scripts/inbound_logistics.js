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

    const statusFilter = document.getElementById('statusFilter');

    const searchInput = document.getElementById('searchInput');
    const dateFromInput = document.getElementById('dateFrom');
    const dateToInput = document.getElementById('dateTo');

    function openModal() {
        document.getElementById('modalTitle').textContent = 'Add Shipment';
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

    // 🔹 FETCH WITH SEARCH + DATE RANGE
    async function fetchShipments(search = '', fromDate = '', toDate = '', status = '') {
        try {
            const params = new URLSearchParams();

            if (search) params.append('search', search);
            if (fromDate) params.append('from_date', fromDate);
            if (toDate) params.append('to_date', toDate);
            if (status) params.append('status', status);

            const url = `../api/inbound_logistics.php?${params.toString()}`;
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
            Toastify({
                text: 'Error loading shipments',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444'
            }).showToast();
        }
    }

    function renderShipments(shipments) {
        emptyState.classList.add('hidden');
        tableBody.innerHTML = shipments.map(s => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-3">${s.id}</td>
                <td class="px-6 py-3 font-semibold">${s.shipment_id}</td>
                <td class="px-6 py-3">${s.po_number || '-'}</td>
                <td class="px-6 py-3">${s.supplier_name}</td>
                <td class="px-6 py-3">${s.items_received}/${s.total_items}</td>
                <td class="px-6 py-3">
                    <span class="px-2 py-1 rounded text-xs font-semibold
                        ${s.quality_status === 'Good'
                            ? 'bg-green-100 text-green-800'
                            : s.quality_status === 'Damaged'
                            ? 'bg-red-100 text-red-800'
                            : 'bg-yellow-100 text-yellow-800'}">
                        ${s.quality_status}
                    </span>
                </td>
                <td class="px-6 py-3">
                    <span class="px-2 py-1 rounded text-xs font-semibold
                        ${s.status === 'Putaway Complete'
                            ? 'bg-green-100 text-green-800'
                            : s.status === 'Verified'
                            ? 'bg-blue-100 text-blue-800'
                            : 'bg-orange-100 text-orange-800'}">
                        ${s.status}
                    </span>
                </td>
                <td class="px-6 py-3 flex gap-2">
                    <button onclick='editShipment(${JSON.stringify(s).replace(/"/g, '&quot;')})'
                        class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">
                        Edit
                    </button>
                    <button onclick="deleteShipment(${s.id})"
                        class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">
                        Delete
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function editShipment(s) {
        document.getElementById('modalTitle').textContent = 'Edit Shipment';
        document.getElementById('shipmentId').value = s.id;
        document.getElementById('shipment_id').value = s.shipment_id;
        document.getElementById('po_number').value = s.po_number || '';
        document.getElementById('supplier_name').value = s.supplier_name;
        document.getElementById('total_items').value = s.total_items;
        document.getElementById('quality_status').value = s.quality_status;
        document.getElementById('handler_name').value = s.handler_name || '';
        document.getElementById('status').value = s.status;
        document.getElementById('notes').value = s.notes || '';
        openModal();
    }

    async function deleteShipment(id) {
        if (!confirm('Delete this shipment?')) return;
        try {
            const res = await fetch('../api/inbound_logistics.php', {
                method: 'DELETE',
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({
                    text: 'Shipment deleted',
                    duration: 2500,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#10b981'
                }).showToast();
                fetchShipments();
            } else throw new Error(data.message || 'Delete failed');
        } catch (err) {
            console.error(err);
            Toastify({
                text: 'Error deleting shipment',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444'
            }).showToast();
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const shipmentId = document.getElementById('shipmentId').value;

        const payload = {
            id: shipmentId || undefined,
            shipment_id: document.getElementById('shipment_id').value,
            po_number: document.getElementById('po_number').value,
            supplier_name: document.getElementById('supplier_name').value,
            total_items: document.getElementById('total_items').value,
            quality_status: document.getElementById('quality_status').value,
            handler_name: document.getElementById('handler_name').value,
            status: document.getElementById('status').value,
            notes: document.getElementById('notes').value
        };

        try {
            const method = shipmentId ? 'PUT' : 'POST';
            const res = await fetch('../api/inbound_logistics.php', {
                method,
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({
                    text: result.message || 'Saved',
                    duration: 2500,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#10b981'
                }).showToast();
                closeModal();
                fetchShipments();
            } else throw new Error(result.message || 'Save failed');
        } catch (err) {
            console.error(err);
            Toastify({
                text: 'Error saving shipment',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444'
            }).showToast();
        }
    });

    // 🔹 APPLY FILTER
    applySearchBtn.addEventListener('click', () => {
        fetchShipments(
            searchInput.value,
            dateFromInput.value,
            dateToInput.value,
            statusFilter.value
        );
    });

    // 🔹 CLEAR FILTER
    clearSearchBtn.addEventListener('click', () => {
        searchInput.value = '';
        dateFromInput.value = '';
        dateToInput.value = '';
        statusFilter.value = '';
        fetchShipments();
    });

    window.deleteShipment = deleteShipment;
    window.editShipment = editShipment;

    fetchShipments();
});