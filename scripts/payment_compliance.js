document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openBtn = document.getElementById('openModal');
    const closeBtn = document.getElementById('closeModal');
    const closeBtnX = document.getElementById('closeModalBtn');
    const form = document.getElementById('invoiceForm');
    const table = document.getElementById('invoiceTable');
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
    async function fetchPOsForPayment() {
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

    function fetchInvoices(status = 'all') {
        const url = status && status !== 'all' ? `../api/payment_compliance.php?status=${status}` : '../api/payment_compliance.php';
        fetch(url).then(r => r.json()).then(d => {
            table.innerHTML = '';
            if (d.invoices && d.invoices.length) {
                d.invoices.forEach(inv => {
                    const row = table.insertRow();
                    const statusColor = inv.status === 'Paid' ? 'bg-green-200 text-green-800' : inv.status === 'Rejected' ? 'bg-red-200 text-red-800' : inv.status === 'Approved' ? 'bg-blue-200 text-blue-800' : 'bg-yellow-200 text-yellow-800';
                    row.innerHTML = `
                        <td class="px-6 py-3">${inv.invoice_number}</td>
                        <td class="px-6 py-3">${inv.po_number}</td>
                        <td class="px-6 py-3">${inv.supplier}</td>
                        <td class="px-6 py-3">$${parseFloat(inv.amount).toFixed(2)}</td>
                        <td class="px-6 py-3"><span class="px-2 py-1 rounded text-sm ${statusColor}">${inv.status}</span></td>
                        <td class="px-6 py-3">${inv.due_date || ''}</td>
                        <td class="px-6 py-3"><button onclick="deleteInvoice(${inv.id})" class="text-red-600 hover:underline">Delete</button></td>
                    `;
                });
                empty.classList.add('hidden');
            } else {
                empty.classList.remove('hidden');
            }
        });
    }

    // Populate supplier dropdown from Supplier Relationship module
    async function fetchSuppliersForInvoice() {
        try {
            const res = await fetch('../api/supplier_relationship.php');
            const data = await res.json();
            const supplierSelect = document.getElementById('supplier');
            if (!supplierSelect) return;
            supplierSelect.innerHTML = '<option value="">Select Supplier</option>';
            if (data.suppliers && data.suppliers.length) {
                data.suppliers.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.supplier_name || s.id;
                    opt.textContent = s.supplier_name || s.id;
                    supplierSelect.appendChild(opt);
                });
            }
        } catch (err) {
            console.error('Failed to load suppliers:', err);
        }
    }

    form.addEventListener('submit', e => {
        e.preventDefault();
        const poNumber = document.getElementById('po_number').value;
        const supplier = document.getElementById('supplier').value;
        
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
        
        if (!supplier) {
            Toastify({
                text: 'Please select a supplier',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444',
            }).showToast();
            return;
        }
        
        const data = {
            po_number: poNumber,
            supplier: supplier,
            amount: document.getElementById('amount').value,
            due_date: document.getElementById('due_date').value,
            status: document.getElementById('status').value,
            compliance_notes: document.getElementById('compliance_notes').value
        };
        
        console.log('Submitting invoice data:', data);
        
        fetch('../api/payment_compliance.php', { method: 'POST', body: JSON.stringify(data) })
            .then(r => r.json())
            .then(result => {
                console.log('Invoice response:', result);
                if(result.status === 'success') {
                    if(result.invoice_number) {
                        document.getElementById('invoice_number').value = result.invoice_number;
                    }
                    Toastify({
                        text: result.message || 'Invoice created successfully',
                        duration: 3000,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: '#10b981',
                    }).showToast();
                    closeModal(); 
                    form.reset(); 
                    fetchInvoices();
                } else {
                    throw new Error(result.message || 'Failed to create invoice');
                }
            })
            .catch(error => {
                console.error('Invoice creation error:', error);
                Toastify({
                    text: 'Error: ' + error.message,
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#ef4444',
                }).showToast();
            });
    });

    applyFilter.addEventListener('click', () => fetchInvoices(filterStatus.value));
    clearFilter.addEventListener('click', () => { filterStatus.value = 'all'; fetchInvoices(); });

    window.deleteInvoice = (id) => {
        if (confirm('Delete this invoice?')) {
            fetch(`../api/payment_compliance.php?id=${id}`, { method: 'DELETE' })
                .then(r => r.json())
                .then(() => fetchInvoices());
        }
    };

    // load supplier and PO dropdowns then invoices
    fetchPOsForPayment();
    fetchSuppliersForInvoice().then(() => fetchInvoices());
});
