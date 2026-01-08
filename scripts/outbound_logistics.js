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
            <tr>
                <td class="px-6 py-3">${s.id}</td>
                <td class="px-6 py-3">${s.shipment_number}</td>
                <td class="px-6 py-3">${s.order_id || ''}</td>
                <td class="px-6 py-3">${s.customer_name}</td>
                <td class="px-6 py-3">${s.total_items}</td>
                <td class="px-6 py-3">${s.carrier_name || ''}</td>
                <td class="px-6 py-3">${s.delivery_status}</td>
                <td class="px-6 py-3"><button onclick="viewShipment(${s.id})" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">View</button></td>
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
});