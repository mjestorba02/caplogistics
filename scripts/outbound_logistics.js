document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('modal');
    const form = document.getElementById('shipmentForm');
    const tableBody = document.getElementById('shipmentsTable');
    const emptyState = document.getElementById('emptyState');

    const viewModal = document.getElementById('viewModal');
    const closeViewModalBtn = document.getElementById('closeViewModal');

    const inventorySelect = document.getElementById('inventorySelect');
    const outQty = document.getElementById('outQty');
    const department = document.getElementById('department');
    const itemList = document.getElementById('itemList');

    let outboundItems = [];

    /* ===================== MODAL ===================== */
    document.getElementById('openModal').onclick = () => {
        form.reset();
        outboundItems = [];
        itemList.innerHTML = '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        loadInventory();
    };

    document.getElementById('closeModal').onclick =
    document.getElementById('closeModalBtn').onclick = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    closeViewModalBtn.onclick = () => {
        viewModal.classList.add('hidden');
        viewModal.classList.remove('flex');
    };

    /* ===================== INVENTORY ===================== */
    async function loadInventory() {
        const res = await fetch('../api/storage_inventory.php?mode=outbound');
        const data = await res.json();

        inventorySelect.innerHTML = data.items.map(i => `
            <option value="${i.id}" data-stock="${i.available_stock}">
                ${i.product_name} (Available: ${i.available_stock})
            </option>
        `).join('');
    }

    /* ===================== ADD ITEM ===================== */
    document.getElementById('addItem').onclick = () => {
        const opt = inventorySelect.selectedOptions[0];
        const qty = parseInt(outQty.value);

        if (!opt || isNaN(qty) || qty <= 0) {
            alert('Invalid item or quantity');
            return;
        }

        if (qty > opt.dataset.stock) {
            alert('Quantity exceeds stock');
            return;
        }

        outboundItems.push({
            inventory_id: opt.value,
            quantity: qty,
            department: department.value
        });

        renderItems();
        outQty.value = '';
    };

    function renderItems() {
        itemList.innerHTML = outboundItems.map((i, idx) => `
            <li class="flex justify-between">
                <span>${i.quantity} → ${i.department}</span>
                <button onclick="removeItem(${idx})" class="text-red-600">✕</button>
            </li>
        `).join('');
        document.getElementById('total_items').value = outboundItems.length;
    }

    window.removeItem = (i) => {
        outboundItems.splice(i,1);
        renderItems();
    };

    /* ===================== SAVE ===================== */
    form.onsubmit = async (e) => {
        e.preventDefault();

        if (outboundItems.length === 0) {
            alert('Add at least one outbound item');
            return;
        }

        const payload = {
            shipment_number: shipment_number.value,
            order_id: order_id.value,
            customer_name: customer_name.value,
            customer_email: customer_email.value,
            delivery_address: delivery_address.value,
            carrier_name: carrier_name.value,
            delivery_status: delivery_status.value,
            notes: '',
            items: outboundItems
        };

        const res = await fetch('../api/outbound_logistics.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        alert(data.message);
        modal.classList.add('hidden');
        fetchShipments();
    };

    /* ===================== LIST ===================== */
    async function fetchShipments() {
        const res = await fetch('../api/outbound_logistics.php');
        const data = await res.json();

        if (!data.shipments.length) {
            emptyState.classList.remove('hidden');
            tableBody.innerHTML = '';
            return;
        }

        emptyState.classList.add('hidden');
        tableBody.innerHTML = data.shipments.map(s => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-3 text-sm text-gray-500">#${s.id}</td>
                <td class="px-6 py-3 font-semibold">${s.shipment_number}</td>
                <td class="px-6 py-3">${s.order_id || ''}</td>
                <td class="px-6 py-3">${s.customer_name}</td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded bg-blue-100 text-blue-800 text-xs font-semibold">${s.total_items}</span></td>
                <td class="px-6 py-3">${s.carrier_name || ''}</td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${s.delivery_status === 'Dispatched' ? 'bg-green-100 text-green-800' : s.delivery_status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'}">${s.delivery_status}</span></td>
                <td class="px-6 py-3 flex gap-1">
                    ${s.delivery_status !== 'Dispatched' ? `<button onclick="shipShipment(${s.id})" class="bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700" title="Mark as Shipped">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>` : ''}
                    <button onclick="viewShipment(${s.id})" class="bg-indigo-600 text-white px-2 py-1 rounded text-xs hover:bg-indigo-700" title="View Details">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    fetchShipments();

    window.viewShipment = async (id) => {
        try {
            const res = await fetch(`../api/outbound_logistics.php?id=${id}`);
            const data = await res.json();
            if (data.status === 'success' && data.shipment) {
                const s = data.shipment;
                document.getElementById('view_shipment_number').textContent = s.shipment_number;
                document.getElementById('view_order_id').textContent = s.order_id || '';
                document.getElementById('view_customer_name').textContent = s.customer_name;
                document.getElementById('view_customer_email').textContent = s.customer_email || '';
                document.getElementById('view_delivery_address').textContent = s.delivery_address || '';
                document.getElementById('view_total_items').textContent = s.total_items;
                document.getElementById('view_carrier_name').textContent = s.carrier_name || '';
                document.getElementById('view_delivery_status').textContent = s.delivery_status;
                document.getElementById('view_notes').textContent = s.notes || '';
                document.getElementById('view_item_list').innerHTML = s.items.map(i => `<li>${i.quantity} x ${i.sku} → ${i.department}</li>`).join('');
                viewModal.classList.remove('hidden');
                viewModal.classList.add('flex');
            } else {
                alert('Shipment not found');
            }
        } catch (err) {
            console.error(err);
            alert('Error loading shipment details');
        }
    };

    window.shipShipment = async (id) => {
        try {
            const res = await fetch('../api/outbound_logistics.php', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({
                    text: data.message || 'Shipment marked as shipped!',
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#10b981'
                }).showToast();
                fetchShipments();
            } else throw new Error(data.message || 'Ship failed');
        } catch (err) {
            console.error('Ship Error:', err);
            Toastify({
                text: 'Error shipping: ' + (err.message || 'Unknown error'),
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444'
            }).showToast();
        }
    };
});