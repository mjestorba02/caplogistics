document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const form = document.getElementById('itemForm');
    const tableBody = document.getElementById('itemsTable');
    const emptyState = document.getElementById('emptyState');
    const applySearchBtn = document.getElementById('applySearch');
    const clearSearchBtn = document.getElementById('clearSearch');

    function openModal() {
        document.getElementById('modalTitle').textContent = 'Edit Item';
        document.getElementById('itemId').value = '';
        form.reset();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        form.reset();
    }

    // Disable Add Item functionality - items come from Inbound Logistics
    if (openModalBtn) {
        openModalBtn.style.display = 'none';
    }
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    async function fetchItems() {
        try {
            const search   = document.getElementById('searchInput').value;
            const dateFrom = document.getElementById('dateFrom')?.value || '';
            const dateTo   = document.getElementById('dateTo')?.value || '';

            const params = new URLSearchParams();

            if (search)   params.append('search', search);
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo)   params.append('date_to', dateTo);

            const url = `../api/storage_inventory.php?${params.toString()}`;

            const res = await fetch(url);
            const data = await res.json();

            if (data.status === 'success' && data.items.length) {
                renderItems(data.items);
            } else {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            console.error(err);
            Toastify({
                text: 'Error loading items',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444'
            }).showToast();
        }
    }

    function renderItems(items) {
        emptyState.classList.add('hidden');
        tableBody.innerHTML = items.map(item => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-3 text-sm text-gray-500">#${item.id}</td>
                <td class="px-6 py-3 font-semibold text-indigo-600">${item.sku}</td>
                <td class="px-6 py-3">${item.product_name}</td>
                <td class="px-6 py-3">${item.current_stock}</td>
                <td class="px-6 py-3">${item.available_stock || item.current_stock}</td>
                <td class="px-6 py-3 text-sm">${item.bin_location}</td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${item.movement_frequency === 'Fast' ? 'bg-red-100 text-red-800' : item.movement_frequency === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'}">${item.movement_frequency}</span></td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${item.stock_status === 'Optimal' ? 'bg-green-100 text-green-800' : item.stock_status === 'Low' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800'}">${item.stock_status}</span></td>
                <td class="px-6 py-3 flex gap-1">
                    <button onclick='editItem(${JSON.stringify(item).replace(/"/g, '&quot;')})' class="bg-indigo-600 text-white px-2 py-1 rounded text-xs hover:bg-indigo-700" title="Edit">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    ${item.stock_status === 'Low' || item.stock_status === 'Critical' ? `<button onclick="requestSupply(${item.id}, '${item.product_name}', '${item.sku}', ${item.current_stock})" class="bg-orange-500 text-white px-2 py-1 rounded text-xs hover:bg-orange-600" title="Request Supplies">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>` : ''}
                    <button onclick="archiveItem(${item.id})" class="bg-orange-600 text-white px-2 py-1 rounded text-xs hover:bg-orange-700" title="Archive">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function editItem(item) {
        document.getElementById('modalTitle').textContent = 'Edit Item';
        document.getElementById('itemId').value = item.id;
        document.getElementById('sku').value = item.sku;
        document.getElementById('product_name').value = item.product_name;
        document.getElementById('category').value = item.category || '';
        document.getElementById('bin_location').value = item.bin_location;
        document.getElementById('warehouse_zone').value = item.warehouse_zone || '';
        document.getElementById('current_stock').value = item.current_stock;
        document.getElementById('movement_frequency').value = item.movement_frequency;
        document.getElementById('supplier_name').value = item.supplier_name || '';
        openModal();
    }

    async function archiveItem(id) {
        if (!confirm('Archive this item?')) return;
        try {
            const res = await fetch('../api/archive_management.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ archive_type: 'storage_inventory', item_id: id, original_table: 'storage_inventory', reason: 'Archived from storage inventory' }) });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({ text: 'Item archived', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                fetchItems();
            } else throw new Error(data.message || 'Archive failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error archiving item', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const itemId = document.getElementById('itemId').value;
        
        // Only allow editing, not creating new items
        if (!itemId) {
            Toastify({ 
                text: 'Items can only be edited, not created. Add new items via Inbound Logistics.', 
                duration: 3000, 
                gravity: 'top', 
                position: 'right', 
                backgroundColor: '#f59e0b' 
            }).showToast();
            return;
        }

        const payload = {
            id: itemId,
            sku: document.getElementById('sku').value,
            product_name: document.getElementById('product_name').value,
            category: document.getElementById('category').value,
            bin_location: document.getElementById('bin_location').value,
            warehouse_zone: document.getElementById('warehouse_zone').value,
            current_stock: document.getElementById('current_stock').value,
            reserved_stock: parseInt(document.getElementById('current_stock').value) - (parseInt(document.getElementById('available_stock')?.value || 0)),
            movement_frequency: document.getElementById('movement_frequency').value,
            supplier_name: document.getElementById('supplier_name').value,
            stock_status: 'Optimal'
        };
        
        try {
            const res = await fetch('../api/storage_inventory.php', { method: 'PUT', body: JSON.stringify(payload) });
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({ text: result.message || 'Item updated', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                closeModal();
                fetchItems();
            } else throw new Error(result.message || 'Save failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error saving item', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    });

    applySearchBtn.addEventListener('click', fetchItems);

    clearSearchBtn.addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        if (document.getElementById('dateFrom')) document.getElementById('dateFrom').value = '';
        if (document.getElementById('dateTo')) document.getElementById('dateTo').value = '';
        fetchItems();
    });

    async function requestSupply(itemId, productName, sku, currentStock) {
        const quantityNeeded = Math.ceil(currentStock * 0.5); // Request 50% of current stock
        const urgency = currentStock === 0 ? 'High' : 'Medium';
        
        try {
            const payload = {
                storage_item_id: itemId,
                item_name: productName,
                sku: sku,
                quantity: quantityNeeded,
                urgency: urgency,
                description: `Auto-request for low stock: Current stock ${currentStock}`,
                request_type: 'Auto-Low-Stock',
                requester_id: 1, // Will be replaced with actual user session
                requester_name: 'System'
            };
            
            const res = await fetch('../api/request_supplies.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({
                    text: `Supply request submitted: ${quantityNeeded} units of ${productName}`,
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#10b981'
                }).showToast();
            } else throw new Error(result.message || 'Request failed');
        } catch (err) {
            console.error(err);
            Toastify({
                text: 'Error submitting supply request: ' + (err.message || 'Unknown error'),
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444'
            }).showToast();
        }
    }

    window.archiveItem = archiveItem;
    window.editItem = editItem;
    window.requestSupply = requestSupply;

    fetchItems();
});
