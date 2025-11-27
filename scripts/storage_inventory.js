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
        document.getElementById('modalTitle').textContent = 'Add Item';
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

    openModalBtn.addEventListener('click', openModal);
    closeModalBtn.addEventListener('click', closeModal);
    closeBtn.addEventListener('click', closeModal);

    async function fetchItems(search = '') {
        try {
            const url = `../api/storage_inventory.php${search ? '?search=' + encodeURIComponent(search) : ''}`;
            const res = await fetch(url);
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.items) && data.items.length) {
                renderItems(data.items);
            } else {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            console.error('Error fetching items:', err);
            Toastify({ text: 'Error loading items', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    }

    function renderItems(items) {
        emptyState.classList.add('hidden');
        tableBody.innerHTML = items.map(item => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-3">${item.id}</td>
                <td class="px-6 py-3 font-semibold">${item.sku}</td>
                <td class="px-6 py-3">${item.product_name}</td>
                <td class="px-6 py-3">${item.current_stock}</td>
                <td class="px-6 py-3">${item.available_stock || item.current_stock}</td>
                <td class="px-6 py-3 text-sm">${item.bin_location}</td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${item.movement_frequency === 'Fast' ? 'bg-red-100 text-red-800' : item.movement_frequency === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'}">${item.movement_frequency}</span></td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${item.stock_status === 'Optimal' ? 'bg-green-100 text-green-800' : item.stock_status === 'Low' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800'}">${item.stock_status}</span></td>
                <td class="px-6 py-3 flex gap-2"><button onclick='editItem(${JSON.stringify(item).replace(/"/g, '&quot;')})' class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Edit</button><button onclick="deleteItem(${item.id})" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Delete</button></td>
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

    async function deleteItem(id) {
        if (!confirm('Delete this item?')) return;
        try {
            const res = await fetch('../api/storage_inventory.php', { method: 'DELETE', body: JSON.stringify({ id }) });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({ text: 'Item deleted', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                fetchItems();
            } else throw new Error(data.message || 'Delete failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error deleting item', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const itemId = document.getElementById('itemId').value;
        const payload = {
            id: itemId || undefined,
            sku: document.getElementById('sku').value,
            product_name: document.getElementById('product_name').value,
            category: document.getElementById('category').value,
            bin_location: document.getElementById('bin_location').value,
            warehouse_zone: document.getElementById('warehouse_zone').value,
            current_stock: document.getElementById('current_stock').value,
            movement_frequency: document.getElementById('movement_frequency').value,
            supplier_name: document.getElementById('supplier_name').value,
            stock_status: 'Optimal'
        };
        try {
            const method = itemId ? 'PUT' : 'POST';
            const res = await fetch('../api/storage_inventory.php', { method, body: JSON.stringify(payload) });
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({ text: result.message || 'Saved', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                closeModal();
                fetchItems();
            } else throw new Error(result.message || 'Save failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error saving item', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    });

    applySearchBtn.addEventListener('click', () => fetchItems(document.getElementById('searchInput').value));
    clearSearchBtn.addEventListener('click', () => { document.getElementById('searchInput').value = ''; fetchItems(); });

    window.deleteItem = deleteItem;
    window.editItem = editItem;

    fetchItems();
});
