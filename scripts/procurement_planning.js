document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openBtn = document.getElementById('openModal');
    const closeBtn = document.getElementById('closeModal');
    const closeBtnX = document.getElementById('closeModalBtn');
    const form = document.getElementById('requisitionForm');
    const table = document.getElementById('requisitionTable');
    const empty = document.getElementById('emptyState');
    const filterStatus = document.getElementById('filterStatus');
    const applyFilter = document.getElementById('applyFilter');
    const clearFilter = document.getElementById('clearFilter');

    function openModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
    function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    closeBtnX.addEventListener('click', closeModal);

    function fetchRequisitions(status = 'all') {
        const url = status && status !== 'all' ? `../api/procurement_planning.php?status=${status}` : '../api/procurement_planning.php';
        fetch(url).then(r => r.json()).then(d => {
            table.innerHTML = '';
            if (d.requisitions && d.requisitions.length) {
                d.requisitions.forEach(r => {
                    const row = table.insertRow();
                    const statusColor = r.status === 'Approved' ? 'bg-green-200 text-green-800' : r.status === 'Rejected' ? 'bg-red-200 text-red-800' : 'bg-blue-200 text-blue-800';
                    row.innerHTML = `
                        <td class="px-6 py-3">${r.requisition_number}</td>
                        <td class="px-6 py-3">${r.department}</td>
                        <td class="px-6 py-3">$${parseFloat(r.total_amount).toFixed(2)}</td>
                        <td class="px-6 py-3"><span class="px-2 py-1 rounded text-sm ${statusColor}">${r.status}</span></td>
                        <td class="px-6 py-3">${r.created_at ? r.created_at.split(' ')[0] : ''}</td>
                        <td class="px-6 py-3"><button onclick="deleteReq(${r.id})" class="text-red-600 hover:underline">Delete</button></td>
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
            department: document.getElementById('department').value,
            description: document.getElementById('description').value,
            total_amount: document.getElementById('total_amount').value,
            status: document.getElementById('status').value
        };
        fetch('../api/procurement_planning.php', { method: 'POST', body: JSON.stringify(data) })
            .then(r => r.json())
            .then(result => { 
                if(result.requisition_number) {
                    document.getElementById('requisition_number').value = result.requisition_number;
                }
                closeModal(); 
                form.reset(); 
                fetchRequisitions(); 
            });
    });

    applyFilter.addEventListener('click', () => fetchRequisitions(filterStatus.value));
    clearFilter.addEventListener('click', () => { filterStatus.value = 'all'; fetchRequisitions(); });

    window.deleteReq = (id) => {
        if (confirm('Delete this requisition?')) {
            fetch(`../api/procurement_planning.php?id=${id}`, { method: 'DELETE' })
                .then(r => r.json())
                .then(() => fetchRequisitions());
        }
    };

    fetchRequisitions();
});
