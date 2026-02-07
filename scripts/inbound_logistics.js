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

    // Check if user is admin (account_type == 1)
    const isAdmin = document.body.dataset.accountType === '1';

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
        tableBody.innerHTML = shipments.map(s => {
            const expectedItems = parseInt(s.total_items) || 0;
            const receivedItems = parseInt(s.items_received) || 0;
            const percentageReceived = expectedItems > 0 ? Math.round((receivedItems / expectedItems) * 100) : 0;
            
            return `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-3 text-sm text-gray-500">#${s.id}</td>
                <td class="px-6 py-3 font-semibold text-indigo-600">${s.shipment_id}</td>
                <td class="px-6 py-3 text-sm">${s.po_number || '-'}</td>
                <td class="px-6 py-3">${s.supplier_name}</td>
                <td class="px-6 py-3 text-center">
                    <span class="px-2 py-1 rounded bg-blue-100 text-blue-800 text-xs font-semibold">${expectedItems}</span>
                </td>
                <td class="px-6 py-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold">${receivedItems}</span>
                        <div class="w-16 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: ${percentageReceived}%"></div>
                        </div>
                        <span class="text-xs text-gray-600">${percentageReceived}%</span>
                    </div>
                </td>
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
                            : s.status === 'Received'
                            ? 'bg-cyan-100 text-cyan-800'
                            : 'bg-orange-100 text-orange-800'}">
                        ${s.status}
                    </span>
                </td>
                <td class="px-6 py-3">
                    <div class="flex gap-1">
                        <button onclick='viewShipment(${JSON.stringify(s).replace(/"/g, '&quot;')})'
                            class="bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600" title="View Details">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                        <button onclick='editShipment(${JSON.stringify(s).replace(/"/g, '&quot;')})'
                            class="bg-indigo-600 text-white px-2 py-1 rounded text-xs hover:bg-indigo-700" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        ${isAdmin && s.status !== 'Putaway Complete' ? `<button onclick="approveShipment(${s.id}, '${s.shipment_id}')"
                            class="bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700" title="Approve">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>` : ''}
                        ${isAdmin ? `<button onclick="archiveShipment(${s.id})"
                            class="bg-orange-600 text-white px-2 py-1 rounded text-xs hover:bg-orange-700" title="Archive">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        </button>` : ''}
                    </div>
                </td>
            </tr>
        `}).join('');
    }

    function editShipment(s) {
        document.getElementById('modalTitle').textContent = 'Edit Shipment';
        document.getElementById('shipmentId').value = s.id;
        document.getElementById('shipment_id').value = s.shipment_id;
        document.getElementById('po_number').value = s.po_number || '';
        document.getElementById('supplier_name').value = s.supplier_name;
        document.getElementById('total_items').value = s.total_items || 0;
        document.getElementById('items_received').value = s.items_received || 0;
        document.getElementById('items_verified').value = s.items_verified || 0;
        document.getElementById('quality_status').value = s.quality_status || 'Pending';
        document.getElementById('handler_name').value = s.handler_name || '';
        document.getElementById('status').value = s.status || 'Pending';
        document.getElementById('category').value = s.category || '';
        document.getElementById('bin_location').value = s.bin_location || '';
        document.getElementById('warehouse_zone').value = s.warehouse_zone || '';
        document.getElementById('notes').value = s.notes || '';
        openModal();
    }

    function viewShipment(s) {
        const details = `
        <strong>Shipment ID:</strong> ${s.shipment_id}<br>
        <strong>PO Number:</strong> ${s.po_number || 'N/A'}<br>
        <strong>Supplier:</strong> ${s.supplier_name}<br>
        <strong>Total Expected:</strong> ${s.total_items}<br>
        <strong>Items Received:</strong> ${s.items_received || 0}<br>
        <strong>Items Verified:</strong> ${s.items_verified || 0}<br>
        <strong>Quality Status:</strong> ${s.quality_status}<br>
        <strong>Handler:</strong> ${s.handler_name || 'N/A'}<br>
        <strong>Status:</strong> ${s.status}<br>
        <strong>Location:</strong> ${s.bin_location || 'N/A'} (${s.warehouse_zone || 'N/A'})<br>
        <strong>Notes:</strong> ${s.notes || 'N/A'}<br>
        <strong>Created:</strong> ${new Date(s.created_at).toLocaleString()}
        `;
        
        Swal.fire({
            title: 'Shipment Details',
            html: details,
            icon: 'info',
            confirmButtonText: 'Close'
        });
    }

    async function archiveShipment(id) {
        if (!confirm('Archive this shipment?')) return;
        try {
            const res = await fetch('../api/archive_management.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    archive_type: 'inbound_logistics',
                    item_id: id,
                    original_table: 'inbound_logistics',
                    reason: 'Archived from inbound logistics'
                })
            });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({
                    text: 'Shipment archived',
                    duration: 2500,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#10b981'
                }).showToast();
                fetchShipments();
            } else throw new Error(data.message || 'Archive failed');
        } catch (err) {
            console.error(err);
            Toastify({
                text: 'Error archiving shipment',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444'
            }).showToast();
        }
    }

    async function approveShipment(id, shipmentId) {
        if (!confirm(`Approve shipment ${shipmentId}? Items will be moved to Storage & Inventory.`)) return;
        try {
            const res = await fetch('../api/inbound_logistics.php', {
                method: 'PATCH',
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({
                    text: data.message || 'Shipment approved successfully!',
                    duration: 2500,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#10b981'
                }).showToast();
                fetchShipments();
            } else throw new Error(data.message || 'Approval failed');
        } catch (err) {
            console.error(err);
            Toastify({
                text: 'Error approving shipment: ' + (err.message || 'Unknown error'),
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
            items_received: document.getElementById('items_received').value || 0,
            items_verified: document.getElementById('items_verified').value || 0,
            quality_status: document.getElementById('quality_status').value,
            handler_name: document.getElementById('handler_name').value,
            status: document.getElementById('status').value,
            category: document.getElementById('category').value || '',
            bin_location: document.getElementById('bin_location').value || '',
            warehouse_zone: document.getElementById('warehouse_zone').value || '',
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
                text: 'Error saving shipment: ' + (err.message || 'Unknown error'),
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

    window.archiveShipment = archiveShipment;
    window.editShipment = editShipment;
    window.approveShipment = approveShipment;
    window.viewShipment = viewShipment;
    window.editShipment = editShipment;

    fetchShipments();
});