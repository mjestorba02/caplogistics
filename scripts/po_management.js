document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openBtn = document.getElementById('openModal');
    const closeBtn = document.getElementById('closeModal');
    const closeBtnX = document.getElementById('closeModalBtn');
    const form = document.getElementById('poForm');
    const table = document.getElementById('poTable');
    const empty = document.getElementById('emptyState');
    const filterStatus = document.getElementById('filterStatus');
    const applyFilter = document.getElementById('applyFilter');
    const clearFilter = document.getElementById('clearFilter');

    function openModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
    function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    closeBtnX.addEventListener('click', closeModal);

    function fetchPOs(status = 'all') {
        const url = status && status !== 'all' ? `../api/po_management.php?status=${status}` : '../api/po_management.php';
        fetch(url).then(r => r.json()).then(d => {
            table.innerHTML = '';
            if (d.pos && d.pos.length) {
                d.pos.forEach(p => {
                    const row = table.insertRow();
                    const statusColor = p.status === 'Confirmed' ? 'bg-green-200 text-green-800' : p.status === 'Cancelled' ? 'bg-red-200 text-red-800' : 'bg-blue-200 text-blue-800';
                    row.innerHTML = `
                        <td class="px-6 py-3 font-semibold text-indigo-600">${p.po_number}</td>
                        <td class="px-6 py-3">${p.supplier}</td>
                        <td class="px-6 py-3">$${parseFloat(p.total_value).toFixed(2)}</td>
                        <td class="px-6 py-3"><span class="px-2 py-1 rounded text-sm font-semibold ${statusColor}">${p.status}</span></td>
                        <td class="px-6 py-3">${p.due_date || ''}</td>
                        <td class="px-6 py-3 flex gap-1">
                            <button onclick="editPO(${p.id})" class="bg-indigo-600 text-white px-2 py-1 rounded text-xs hover:bg-indigo-700" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button onclick="deletePO(${p.id})" class="bg-red-600 text-white px-2 py-1 rounded text-xs hover:bg-red-700" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </td>
                    `;
                });
                empty.classList.add('hidden');
            } else {
                empty.classList.remove('hidden');
            }
        });
    }

    form.addEventListener('submit', e => {
        e.preventDefault();
        const data = {
            supplier: document.getElementById('supplier').value,
            description: document.getElementById('description').value,
            total_value: document.getElementById('total_value').value,
            due_date: document.getElementById('due_date').value,
            status: document.getElementById('status').value
        };
        fetch('../api/po_management.php', { method: 'POST', body: JSON.stringify(data) })
            .then(r => r.json())
            .then(result => {
                if(result.po_number) {
                    document.getElementById('po_number').value = result.po_number;
                }
                closeModal(); 
                form.reset(); 
                fetchPOs();
            });
    });

    applyFilter.addEventListener('click', () => fetchPOs(filterStatus.value));
    clearFilter.addEventListener('click', () => { filterStatus.value = 'all'; fetchPOs(); });

    window.deletePO = (id) => {
        if (confirm('Delete this PO?')) {
            fetch(`../api/po_management.php?id=${id}`, { method: 'DELETE' })
                .then(r => r.json())
                .then(() => fetchPOs());
        }
    };

    fetchPOs();
});
