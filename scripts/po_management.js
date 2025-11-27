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
                        <td class="px-6 py-3">${p.po_number}</td>
                        <td class="px-6 py-3">${p.supplier}</td>
                        <td class="px-6 py-3">$${parseFloat(p.total_value).toFixed(2)}</td>
                        <td class="px-6 py-3"><span class="px-2 py-1 rounded text-sm ${statusColor}">${p.status}</span></td>
                        <td class="px-6 py-3">${p.due_date || ''}</td>
                        <td class="px-6 py-3"><button onclick="deletePO(${p.id})" class="text-red-600 hover:underline">Delete</button></td>
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
