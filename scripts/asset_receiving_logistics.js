let receivingData = [];

const addReceivingBtn = document.getElementById('addReceivingBtn');
const receivingModal = document.getElementById('receivingModal');
const receivingForm = document.getElementById('receivingForm');
const closeModal = document.getElementById('closeModal');
const closeModalBtn = document.getElementById('closeModalBtn');
const tableBody = document.getElementById('tableBody');
const emptyState = document.getElementById('emptyState');
const filterInput = document.getElementById('filterInput');
const applyFilter = document.getElementById('applyFilter');
const clearFilter = document.getElementById('clearFilter');
const modalTitle = document.getElementById('modalTitle');

function renderTable(data = receivingData) {
    tableBody.innerHTML = '';

    if (!data || data.length === 0) {
        emptyState.classList.remove('hidden');
        return;
    }

    emptyState.classList.add('hidden');

    data.forEach(row => {
        const statusColor = row.status === 'Received' ? 'bg-green-100 text-green-800' : 
                            row.status === 'Pending' ? 'bg-yellow-100 text-yellow-800' :
                            row.status === 'Damaged' ? 'bg-red-100 text-red-800' :
                            'bg-orange-100 text-orange-800';

        const tr = document.createElement('tr');
        tr.className = 'border-b hover:bg-gray-50';
        tr.innerHTML = `
            <td class="px-6 py-3 font-mono font-semibold">${row.po_number}</td>
            <td class="px-6 py-3">${row.received_date}</td>
            <td class="px-6 py-3">${row.received_by}</td>
            <td class="px-6 py-3">${row.supplier_name || '-'}</td>
            <td class="px-6 py-3 text-sm truncate">${row.item_description || '-'}</td>
            <td class="px-6 py-3 text-center">${row.quantity_received}</td>
            <td class="px-6 py-3 text-center">${row.quantity_expected}</td>
            <td class="px-6 py-3">
                <span class="px-2 py-1 rounded text-xs font-semibold ${statusColor}">${row.status}</span>
            </td>
            <td class="px-6 py-3">
                <button onclick="editReceiving(${row.id})" class="text-indigo-600 hover:underline mr-2">Edit</button>
                <button onclick="archiveReceiving(${row.id})" class="text-orange-600 hover:underline">Archive</button>
            </td>
        `;
        tableBody.appendChild(tr);
    });
}

function fetchReceiving() {
    fetch('../api/asset_receiving_logistics.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                receivingData = Array.isArray(data.data) ? data.data : [];
                renderTable(receivingData);
            } else {
                Toastify({
                    text: 'Error loading data',
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)'
                }).showToast();
            }
        })
        .catch(err => {
            console.error('Error:', err);
            Toastify({
                text: 'Error loading receiving records',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)'
            }).showToast();
        });
}

addReceivingBtn.addEventListener('click', () => {
    receivingForm.reset();
    document.getElementById('receivingId').value = '';
    modalTitle.textContent = 'Add Receiving';
    receivingModal.classList.remove('hidden');
    receivingModal.classList.add('flex');
});

closeModal.addEventListener('click', () => {
    receivingModal.classList.add('hidden');
    receivingModal.classList.remove('flex');
});

closeModalBtn.addEventListener('click', () => {
    receivingModal.classList.add('hidden');
    receivingModal.classList.remove('flex');
});

receivingModal.addEventListener('click', (e) => {
    if (e.target === receivingModal) {
        receivingModal.classList.add('hidden');
        receivingModal.classList.remove('flex');
    }
});

filterInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') applyFilter.click();
});

applyFilter.addEventListener('click', () => {
    const query = filterInput.value.toLowerCase();
    if (!query) {
        renderTable(receivingData);
        return;
    }

    const filtered = receivingData.filter(row =>
        row.po_number.toLowerCase().includes(query) ||
        row.supplier_name.toLowerCase().includes(query) ||
        row.received_by.toLowerCase().includes(query) ||
        row.received_date.toLowerCase().includes(query) ||
        row.item_description.toLowerCase().includes(query)
    );
    renderTable(filtered);
});

clearFilter.addEventListener('click', () => {
    filterInput.value = '';
    renderTable(receivingData);
});

receivingForm.addEventListener('submit', (e) => {
    e.preventDefault();

    const id = document.getElementById('receivingId').value;
    const payload = {
        po_number: document.getElementById('po_number').value.trim(),
        received_date: document.getElementById('received_date').value.trim(),
        received_by: document.getElementById('received_by').value.trim(),
        supplier_name: document.getElementById('supplier_name').value.trim(),
        item_description: document.getElementById('item_description').value.trim(),
        quantity_received: parseInt(document.getElementById('quantity_received').value) || 0,
        quantity_expected: parseInt(document.getElementById('quantity_expected').value) || 0,
        damage_notes: document.getElementById('damage_notes').value.trim(),
        discrepancy_notes: document.getElementById('discrepancy_notes').value.trim(),
        status: document.getElementById('status').value
    };

    // Validation
    if (!payload.po_number) {
        Toastify({ text: 'PO Number is required', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
        return;
    }
    if (!payload.received_date) {
        Toastify({ text: 'Received Date is required', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
        return;
    }
    if (!payload.received_by) {
        Toastify({ text: 'Received By is required', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
        return;
    }
    if (payload.quantity_received <= 0) {
        Toastify({ text: 'Quantity Received must be greater than 0', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
        return;
    }
    if (payload.quantity_expected <= 0) {
        Toastify({ text: 'Quantity Expected must be greater than 0', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
        return;
    }

    const method = id ? 'PUT' : 'POST';
    if (id) payload.id = id;

    fetch('../api/asset_receiving_logistics.php', {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(res => res.json())
        .then(result => {
            if (result.status === 'success') {
                Toastify({
                    text: id ? 'Updated successfully' : 'Created successfully',
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #16a34a, #86efac)'
                }).showToast();
                receivingModal.classList.add('hidden');
                receivingModal.classList.remove('flex');
                fetchReceiving();
            } else {
                throw new Error(result.message || 'Save failed');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            Toastify({
                text: 'Error: ' + err.message,
                duration: 5000,
                gravity: 'top',
                position: 'right',
                backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)'
            }).showToast();
        });
});

window.editReceiving = (id) => {
    const row = receivingData.find(r => r.id == id);
    if (!row) return;

    document.getElementById('receivingId').value = row.id;
    document.getElementById('po_number').value = row.po_number;
    document.getElementById('received_date').value = row.received_date;
    document.getElementById('received_by').value = row.received_by;
    document.getElementById('supplier_name').value = row.supplier_name;
    document.getElementById('item_description').value = row.item_description;
    document.getElementById('quantity_received').value = row.quantity_received;
    document.getElementById('quantity_expected').value = row.quantity_expected;
    document.getElementById('damage_notes').value = row.damage_notes;
    document.getElementById('discrepancy_notes').value = row.discrepancy_notes;
    document.getElementById('status').value = row.status;

    modalTitle.textContent = 'Edit Receiving';
    receivingModal.classList.remove('hidden');
    receivingModal.classList.add('flex');
};

window.archiveReceiving = (id) => {
    if (!confirm('Are you sure you want to archive this record?')) return;

    fetch('../api/archive_management.php', { 
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ archive_type: 'receiving', item_id: id, original_table: 'asset_receiving_logistics', reason: 'Archived from receiving' })
    })
        .then(res => res.json())
        .then(result => {
            if (result.status === 'success') {
                Toastify({
                    text: 'Archived successfully',
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #16a34a, #86efac)'
                }).showToast();
                fetchReceiving();
            } else {
                throw new Error(result.message || 'Archive failed');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            Toastify({
                text: 'Error: ' + err.message,
                duration: 5000,
                gravity: 'top',
                position: 'right',
                backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)'
            }).showToast();
        });
};

// Load data on page load
fetchReceiving();
