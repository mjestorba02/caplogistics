document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const form = document.getElementById('contractForm');
    const tableBody = document.getElementById('contractsTable');
    const emptyState = document.getElementById('emptyState');
    const applyFilterBtn = document.getElementById('applyFilter');
    const clearFilterBtn = document.getElementById('clearFilter');
    const generateReportBtn = document.getElementById('generateReport');
    const supplierSelect = document.getElementById('supplier_name');

    // Load approved vendors on page load
    async function loadApprovedVendors() {
        try {
            const res = await fetch('../api/vendor_portal.php?action=get_approved_vendors');
            const data = await res.json();
            
            if (data.status === 'success' && data.vendors.length) {
                // Clear existing options except the default
                const defaultOption = supplierSelect.querySelector('option:first-child');
                supplierSelect.innerHTML = '';
                supplierSelect.appendChild(defaultOption);
                
                // Add approved vendors
                data.vendors.forEach(vendor => {
                    const option = document.createElement('option');
                    option.value = vendor.vendor_name;
                    option.textContent = vendor.vendor_name;
                    option.dataset.vendorId = vendor.id;
                    supplierSelect.appendChild(option);
                });
            }
        } catch (err) {
            console.error('Error loading vendors:', err);
        }
    }

    // Update vendor_id when supplier is selected
    supplierSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const vendorId = selectedOption.dataset.vendorId || '';
        document.getElementById('vendor_id').value = vendorId;
    });

    function openModal() {
        document.getElementById('modalTitle').textContent = 'Create Contract';
        document.getElementById('contractId').value = '';
        document.getElementById('vendor_id').value = '';
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

    async function fetchContracts() {
        try {
            const dateFrom = document.getElementById('dateFrom')?.value || '';
            const dateTo = document.getElementById('dateTo')?.value || '';

            const params = new URLSearchParams();
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);

            const url = `../api/create_contract_reports.php?${params.toString()}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.status === 'success' && data.contracts.length) {
                renderContracts(data.contracts);
            } else {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            console.error(err);
            Toastify({
                text: 'Error loading contracts',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444'
            }).showToast();
        }
    }

    function renderContracts(contracts) {
        emptyState.classList.add('hidden');
        tableBody.innerHTML = contracts.map(c => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-3">${c.id}</td>
                <td class="px-6 py-3">${c.contract_title}</td>
                <td class="px-6 py-3">${c.supplier_name}</td>
                <td class="px-6 py-3">${c.start_date}</td>
                <td class="px-6 py-3">${c.end_date}</td>
                <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${c.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${c.status}</span></td>
                <td class="px-6 py-3 flex gap-2"><button onclick='editContract(${JSON.stringify(c).replace(/"/g, '&quot;')})' class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Edit</button><button onclick="archiveContract(${c.id})" class="bg-yellow-600 text-white px-3 py-1 rounded text-xs hover:bg-yellow-700">Archive</button></td>
            </tr>
        `).join('');
    }

    function editContract(contract) {
        document.getElementById('modalTitle').textContent = 'Edit Contract';
        document.getElementById('contractId').value = contract.id;
        document.getElementById('vendor_id').value = contract.vendor_id || '';
        document.getElementById('contract_title').value = contract.contract_title;
        document.getElementById('supplier_name').value = contract.supplier_name;
        document.getElementById('start_date').value = contract.start_date;
        document.getElementById('end_date').value = contract.end_date;
        document.getElementById('contract_value').value = contract.contract_value;
        document.getElementById('details').value = contract.details || '';
        openModal();
    }

    async function archiveContract(id) {
        if (!confirm('Archive this contract? It will be removed from the list but recoverable from Archive.')) return;
        try {
            const payload = { archive_type: 'contract', item_id: id, original_table: 'procurement_contracts', reason: 'Archived from UI' };
            const res = await fetch('../api/archive_management.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({ text: 'Contract archived', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                fetchContracts();
            } else throw new Error(data.message || 'Archive failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error archiving contract', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const contractId = document.getElementById('contractId').value;
        const payload = {
            id: contractId || undefined,
            vendor_id: document.getElementById('vendor_id').value || null,
            contract_title: document.getElementById('contract_title').value,
            supplier_name: document.getElementById('supplier_name').value,
            start_date: document.getElementById('start_date').value,
            end_date: document.getElementById('end_date').value,
            contract_value: document.getElementById('contract_value').value,
            details: document.getElementById('details').value
        };
        try {
            const method = contractId ? 'PUT' : 'POST';
            const res = await fetch('../api/create_contract_reports.php', { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({ text: result.message || 'Saved', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                closeModal();
                fetchContracts();
            } else throw new Error(result.message || 'Save failed');
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error saving contract', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    });

    applyFilterBtn.addEventListener('click', fetchContracts);
    clearFilterBtn.addEventListener('click', () => {
        if (document.getElementById('dateFrom')) document.getElementById('dateFrom').value = '';
        if (document.getElementById('dateTo')) document.getElementById('dateTo').value = '';
        fetchContracts();
    });

    generateReportBtn.addEventListener('click', () => {
        // Show password verification modal
        const passwordModal = document.createElement('div');
        passwordModal.className = 'fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50';
        passwordModal.innerHTML = `
            <div class="bg-white rounded-lg shadow-lg w-96 p-6">
                <h2 class="text-2xl font-bold mb-4">Verify Your Identity</h2>
                <p class="text-gray-600 mb-4">Please enter your password to generate the report</p>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="reportPassword" class="w-full border rounded px-3 py-2" placeholder="Enter your password" />
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400" id="cancelPasswordBtn">Cancel</button>
                    <button type="button" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700" id="confirmPasswordBtn">Generate Report</button>
                </div>
            </div>
        `;

        document.body.appendChild(passwordModal);

        const passwordInput = document.getElementById('reportPassword');
        const confirmBtn = document.getElementById('confirmPasswordBtn');
        const cancelBtn = document.getElementById('cancelPasswordBtn');

        function closePasswordModal() {
            passwordModal.remove();
        }

        cancelBtn.addEventListener('click', closePasswordModal);

        confirmBtn.addEventListener('click', async () => {
            const password = passwordInput.value.trim();
            if (!password) {
                Toastify({ text: 'Please enter your password', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
                return;
            }

            const dateFrom = document.getElementById('dateFrom')?.value || '';
            const dateTo = document.getElementById('dateTo')?.value || '';
            const url = `../api/create_contract_reports.php?action=report&date_from=${dateFrom}&date_to=${dateTo}&password=${encodeURIComponent(password)}`;
            
            try {
                // Test if password is correct first
                const response = await fetch(url, { method: 'HEAD' });
                if (response.status === 401) {
                    Toastify({ text: 'Incorrect password', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
                } else {
                    closePasswordModal();
                    window.location.href = url;
                    Toastify({ text: 'Report generating...', duration: 2000, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                }
            } catch (err) {
                // If error, try to generate anyway
                closePasswordModal();
                window.location.href = url;
                Toastify({ text: 'Report generating...', duration: 2000, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
            }
        });

        // Focus on password input
        passwordInput.focus();

        // Allow Enter key to submit
        passwordInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') confirmBtn.click();
        });

        // Close on background click
        passwordModal.addEventListener('click', (e) => {
            if (e.target === passwordModal) closePasswordModal();
        });
    });

    window.archiveContract = archiveContract;
    window.editContract = editContract;

    // Load vendors and contracts on page load
    loadApprovedVendors();
    fetchContracts();
});