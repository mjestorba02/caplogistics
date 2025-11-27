document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const form = document.getElementById('supplierForm');
    const tableBody = document.getElementById('supplierTable');
    const emptyState = document.getElementById('emptyState');
    const applyFilterBtn = document.getElementById('applyFilter');
    const clearFilterBtn = document.getElementById('clearFilter');

    function openModal() {
        document.getElementById('modalTitle').textContent = 'Add Supplier';
        document.getElementById('supplierId').value = '';
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

    async function fetchSuppliers(search = '') {
        try {
            const url = `../api/supplier_identification.php${search ? '?search=' + encodeURIComponent(search) : ''}`;
            const res = await fetch(url);
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.suppliers) && data.suppliers.length) {
                renderSuppliers(data.suppliers);
            } else {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            console.error('Error fetching suppliers:', err);
            Toastify({ text: 'Error loading suppliers', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    }

    function renderSuppliers(suppliers) {
        emptyState.classList.add('hidden');
        tableBody.innerHTML = suppliers.map(s => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-3">${s.id}</td>
                <td class="px-6 py-3 font-semibold">${s.supplier_name}</td>
                <td class="px-6 py-3 text-sm">${s.contact_email}</td>
                <td class="px-6 py-3 text-sm"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">${s.certifications || 'None'}</span></td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${s.risk_level === 'Low' ? 'bg-green-100 text-green-800' : s.risk_level === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">${s.risk_level}</span></td>
                <td class="px-6 py-3 text-sm">${s.created_at || 'N/A'}</td>
                <td class="px-6 py-3 flex gap-2"><button onclick='editSupplier(${JSON.stringify(s).replace(/"/g, '&quot;')})' class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Edit</button><button onclick="deleteSupplier(${s.id})" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Delete</button></td>
            </tr>
        `).join('');
    }

    function editSupplier(s) {
        document.getElementById('modalTitle').textContent = 'Edit Supplier';
        document.getElementById('supplierId').value = s.id;
        document.getElementById('supplier_name').value = s.supplier_name;
        document.getElementById('contact_email').value = s.contact_email;
        document.getElementById('risk_level').value = s.risk_level;
        document.getElementById('phone').value = s.phone || '';
        document.getElementById('notes').value = s.notes || '';
        const certSelect = document.getElementById('certifications');
        if (s.certifications) {
            const certs = s.certifications.split(',').map(c => c.trim());
            Array.from(certSelect.options).forEach(opt => opt.selected = certs.includes(opt.value));
        }
        openModal();
    }

    async function deleteSupplier(id) {
        if (!confirm('Delete this supplier?')) return;
        try {
            const res = await fetch('../api/supplier_identification.php', { method: 'DELETE', body: JSON.stringify({ id }) });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({ text: 'Supplier deleted', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                fetchSuppliers();
            } else throw new Error(data.message || 'Delete failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error deleting supplier', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const supplierId = document.getElementById('supplierId').value;
        const certSelect = document.getElementById('certifications');
        const certifications = Array.from(certSelect.selectedOptions).map(o => o.value);
        const payload = {
            id: supplierId || undefined,
            supplier_name: document.getElementById('supplier_name').value,
            contact_email: document.getElementById('contact_email').value,
            certifications: certifications,
            risk_level: document.getElementById('risk_level').value,
            phone: document.getElementById('phone').value,
            notes: document.getElementById('notes').value
        };
        try {
            const method = supplierId ? 'PUT' : 'POST';
            const res = await fetch('../api/supplier_identification.php', { method, body: JSON.stringify(payload) });
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({ text: result.message || 'Saved', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                closeModal();
                fetchSuppliers();
            } else throw new Error(result.message || 'Save failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error saving supplier', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    });

    applyFilterBtn.addEventListener('click', () => fetchSuppliers(document.getElementById('filterCert').value));
    clearFilterBtn.addEventListener('click', () => { document.getElementById('filterCert').value = ''; fetchSuppliers(); });

    window.deleteSupplier = deleteSupplier;
    window.editSupplier = editSupplier;

    // initial load
    fetchSuppliers();
});
