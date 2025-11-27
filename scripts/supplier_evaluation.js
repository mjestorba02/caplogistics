document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openBtn = document.getElementById('openModal');
    const closeBtn = document.getElementById('closeModal');
    const closeBtnX = document.getElementById('closeModalBtn');
    const form = document.getElementById('rfqForm');
    const table = document.getElementById('rfqTable');
    const empty = document.getElementById('emptyState');
    const filterStatus = document.getElementById('filterStatus');
    const applyFilter = document.getElementById('applyFilter');
    const clearFilter = document.getElementById('clearFilter');

    function openModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
    function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    closeBtnX.addEventListener('click', closeModal);

    function fetchRFQs(status = 'all') {
        const url = status && status !== 'all' ? `../api/supplier_evaluation.php?status=${status}` : '../api/supplier_evaluation.php';
        fetch(url).then(r => r.json()).then(d => {
            table.innerHTML = '';
            if (d.rfqs && d.rfqs.length) {
                d.rfqs.forEach(r => {
                    const row = table.insertRow();
                    row.innerHTML = `
                        <td class="px-6 py-3">${r.id}</td>
                        <td class="px-6 py-3">${r.item_description}</td>
                        <td class="px-6 py-3">${r.suppliers}</td>
                        <td class="px-6 py-3"><span class="px-2 py-1 rounded text-sm ${r.status === 'Selected' ? 'bg-green-200 text-green-800' : 'bg-blue-200 text-blue-800'}">${r.status}</span></td>
                        <td class="px-6 py-3">${r.created_at ? r.created_at.split(' ')[0] : ''}</td>
                        <td class="px-6 py-3"><button onclick="deleteRFQ(${r.id})" class="text-red-600 hover:underline">Delete</button></td>
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
            item_description: document.getElementById('item_description').value,
            quantity: document.getElementById('quantity').value,
            budget: document.getElementById('budget').value,
            suppliers: document.getElementById('suppliers').value,
            status: document.getElementById('status').value
        };
        fetch('../api/supplier_evaluation.php', { method: 'POST', body: JSON.stringify(data) })
            .then(r => r.json())
            .then(() => { closeModal(); form.reset(); fetchRFQs(); });
    });

    applyFilter.addEventListener('click', () => fetchRFQs(filterStatus.value));
    clearFilter.addEventListener('click', () => { filterStatus.value = 'all'; fetchRFQs(); });

    window.deleteRFQ = (id) => {
        if (confirm('Delete this RFQ?')) {
            fetch(`../api/supplier_evaluation.php?id=${id}`, { method: 'DELETE' })
                .then(r => r.json())
                .then(() => fetchRFQs());
        }
    };

    fetchRFQs();
});
