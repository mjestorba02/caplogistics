document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openBtn = document.getElementById('openModal');
    const closeBtn = document.getElementById('closeModal');
    const closeBtnX = document.getElementById('closeModalBtn');
    const form = document.getElementById('receiptForm');
    const table = document.getElementById('receiptTable');
    const empty = document.getElementById('emptyState');
    const filterStatus = document.getElementById('filterStatus');
    const applyFilter = document.getElementById('applyFilter');
    const clearFilter = document.getElementById('clearFilter');

    function openModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
    function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    closeBtnX.addEventListener('click', closeModal);

    // Fetch and populate PO dropdown from PO Management
    async function fetchPOsForReceiving() {
        try {
            const res = await fetch('../api/po_management.php');
            const data = await res.json();
            const poSelect = document.getElementById('po_number');
            if (data.status === 'success' && Array.isArray(data.pos)) {
                const currentValue = poSelect.value;
                poSelect.innerHTML = '<option value="">Select PO</option>';
                data.pos.forEach(po => {
                    const opt = document.createElement('option');
                    opt.value = po.po_number;
                    opt.textContent = po.po_number;
                    poSelect.appendChild(opt);
                });
                if (currentValue) poSelect.value = currentValue;
            }
        } catch (err) {
            console.error('Error fetching POs:', err);
        }
    }

    function fetchReceipts(status = 'all') {
        const url = status && status !== 'all' ? `../api/receiving_quality.php?status=${status}` : '../api/receiving_quality.php';
        fetch(url).then(r => r.json()).then(d => {
            table.innerHTML = '';
            if (d.receipts && d.receipts.length) {
                d.receipts.forEach(r => {
                    const row = table.insertRow();
                    const statusColor = r.status === 'Accepted' ? 'bg-green-200 text-green-800' : r.status === 'Rejected' ? 'bg-red-200 text-red-800' : 'bg-blue-200 text-blue-800';
                    row.innerHTML = `
                        <td class="px-6 py-3">${r.receipt_number}</td>
                        <td class="px-6 py-3">${r.po_number}</td>
                        <td class="px-6 py-3">${r.quantity_received}</td>
                        <td class="px-6 py-3">${r.condition}</td>
                        <td class="px-6 py-3"><span class="px-2 py-1 rounded text-sm ${statusColor}">${r.status}</span></td>
                        <td class="px-6 py-3">${r.created_at ? r.created_at.split(' ')[0] : ''}</td>
                        <td class="px-6 py-3"><button onclick="deleteReceipt(${r.id})" class="text-red-600 hover:underline">Delete</button></td>
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
        const poNumber = document.getElementById('po_number').value;
        
        if (!poNumber) {
            Toastify({
                text: 'Please select a PO number',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444',
            }).showToast();
            return;
        }
        
        const data = {
            po_number: poNumber,
            quantity_received: document.getElementById('quantity_received').value,
            quantity_inspected: document.getElementById('quantity_inspected').value,
            item_condition: document.getElementById('condition').value,
            status: document.getElementById('status').value
        };
        
        console.log('Submitting receipt data:', data);
        
        fetch('../api/receiving_quality.php', { method: 'POST', body: JSON.stringify(data) })
            .then(r => r.json())
            .then(result => {
                console.log('Receipt response:', result);
                if(result.status === 'success') {
                    if(result.receipt_number) {
                        document.getElementById('receipt_number').value = result.receipt_number;
                    }
                    Toastify({
                        text: result.message || 'Receipt created successfully',
                        duration: 3000,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: '#10b981',
                    }).showToast();
                    closeModal(); 
                    form.reset(); 
                    fetchReceipts();
                } else {
                    throw new Error(result.message || 'Failed to create receipt');
                }
            })
            .catch(error => {
                console.error('Receipt creation error:', error);
                Toastify({
                    text: 'Error: ' + error.message,
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#ef4444',
                }).showToast();
            });
    });

    applyFilter.addEventListener('click', () => fetchReceipts(filterStatus.value));
    clearFilter.addEventListener('click', () => { filterStatus.value = 'all'; fetchReceipts(); });

    window.deleteReceipt = (id) => {
        if (confirm('Delete this receipt?')) {
            fetch(`../api/receiving_quality.php?id=${id}`, { method: 'DELETE' })
                .then(r => r.json())
                .then(() => fetchReceipts());
        }
    };

    fetchPOsForReceiving();
    fetchReceipts();
});
