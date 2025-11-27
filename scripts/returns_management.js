document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const form = document.getElementById('returnForm');
    const tableBody = document.getElementById('returnsTable');
    const emptyState = document.getElementById('emptyState');
    const applySearchBtn = document.getElementById('applySearch');
    const clearSearchBtn = document.getElementById('clearSearch');

    function openModal() {
        document.getElementById('modalTitle').textContent = 'New Return';
        document.getElementById('returnId').value = '';
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

    async function fetchReturns(search = '') {
        try {
            const url = `../api/returns_management.php${search ? '?search=' + encodeURIComponent(search) : ''}`;
            const res = await fetch(url);
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.returns) && data.returns.length) {
                renderReturns(data.returns);
            } else {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            console.error('Error fetching returns:', err);
            Toastify({ text: 'Error loading returns', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    }

    function renderReturns(returns) {
        emptyState.classList.add('hidden');
        tableBody.innerHTML = returns.map(r => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-3">${r.id}</td>
                <td class="px-6 py-3 font-semibold">${r.return_id}</td>
                <td class="px-6 py-3">${r.customer_name}</td>
                <td class="px-6 py-3 text-sm">${r.return_reason.substring(0, 30)}...</td>
                <td class="px-6 py-3">${r.item_count}</td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${r.inspection_status === 'Complete' ? 'bg-green-100 text-green-800' : r.inspection_status === 'In Progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-orange-100 text-orange-800'}">${r.inspection_status}</span></td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${r.item_classification === 'Resellable' ? 'bg-blue-100 text-blue-800' : r.item_classification === 'Refurbish' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">${r.item_classification}</span></td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${r.return_status === 'Refunded' || r.return_status === 'Disposed' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'}">${r.return_status}</span></td>
                <td class="px-6 py-3 flex gap-2"><button onclick='editReturn(${JSON.stringify(r).replace(/"/g, '&quot;')})' class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Edit</button><button onclick="deleteReturn(${r.id})" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Delete</button></td>
            </tr>
        `).join('');
    }

    function editReturn(r) {
        document.getElementById('modalTitle').textContent = 'Edit Return';
        document.getElementById('returnId').value = r.id;
        document.getElementById('return_id').value = r.return_id;
        document.getElementById('order_id').value = r.order_id || '';
        document.getElementById('customer_name').value = r.customer_name;
        document.getElementById('customer_email').value = r.customer_email || '';
        document.getElementById('return_reason').value = r.return_reason;
        document.getElementById('item_count').value = r.item_count;
        document.getElementById('original_purchase_price').value = r.original_purchase_price;
        document.getElementById('return_status').value = r.return_status;
        openModal();
    }

    async function deleteReturn(id) {
        if (!confirm('Delete this return?')) return;
        try {
            const res = await fetch('../api/returns_management.php', { method: 'DELETE', body: JSON.stringify({ id }) });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({ text: 'Return deleted', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                fetchReturns();
            } else throw new Error(data.message || 'Delete failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error deleting return', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const returnId = document.getElementById('returnId').value;
        const payload = {
            id: returnId || undefined,
            return_id: document.getElementById('return_id').value,
            order_id: document.getElementById('order_id').value,
            customer_name: document.getElementById('customer_name').value,
            customer_email: document.getElementById('customer_email').value,
            return_reason: document.getElementById('return_reason').value,
            item_count: document.getElementById('item_count').value,
            original_purchase_price: document.getElementById('original_purchase_price').value,
            return_status: document.getElementById('return_status').value
        };
        try {
            const method = returnId ? 'PUT' : 'POST';
            const res = await fetch('../api/returns_management.php', { method, body: JSON.stringify(payload) });
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({ text: result.message || 'Saved', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                closeModal();
                fetchReturns();
            } else throw new Error(result.message || 'Save failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error saving return', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    });

    applySearchBtn.addEventListener('click', () => fetchReturns(document.getElementById('searchInput').value));
    clearSearchBtn.addEventListener('click', () => { document.getElementById('searchInput').value = ''; fetchReturns(); });

    window.deleteReturn = deleteReturn;
    window.editReturn = editReturn;

    fetchReturns();
});
