// Vendor Portal JavaScript - Simplified and Fixed

document.addEventListener('DOMContentLoaded', () => {
    const API_BASE = '../api/vendor_portal.php';

    // Modal elements (matching HTML IDs exactly)
    const vendorModal = document.getElementById('vendorModal');
    const validationModal = document.getElementById('validationModal');
    const verificationModal = document.getElementById('verificationModal');
    const requirementModal = document.getElementById('requirementModal');

    // Forms
    const vendorForm = document.getElementById('vendorForm');
    const validationForm = document.getElementById('validationForm');
    const verificationForm = document.getElementById('verificationForm');
    const requirementForm = document.getElementById('requirementForm');

    // ===== MODAL MANAGEMENT =====
    function openModal(modal) {
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    function closeModal(modal) {
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Toast notifications
    function showToast(message, type = 'success') {
        Toastify({
            text: message,
            duration: 3000,
            gravity: 'top',
            position: 'right',
            backgroundColor: type === 'success' ? '#10b981' : '#ef4444'
        }).showToast();
    }

    // ===== TAB SWITCHING =====
    window.switchTab = function(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });

        // Remove active class from all buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Show selected tab
        const selectedTab = document.getElementById(`${tabName}-tab`);
        if (selectedTab) {
            selectedTab.classList.remove('hidden');
        }

        // Mark button as active
        event.target.closest('.tab-btn').classList.add('active');

        // Load data for active tab
        if (tabName === 'vendors') {
            loadVendors();
        } else if (tabName === 'validation') {
            loadValidations();
        } else if (tabName === 'verification') {
            loadVerifications();
        } else if (tabName === 'requirements') {
            loadRequirements();
        }
    };

    // ===== VENDOR MODAL FUNCTIONS =====
    window.addNewVendor = function() {
        document.getElementById('modalTitle').textContent = 'Register New Vendor';
        document.getElementById('vendorId').value = '';
        vendorForm.reset();
        openModal(vendorModal);
    };

    window.closeVendorModal = function() {
        closeModal(vendorModal);
    };

    window.submitVendorForm = async function(event) {
        event.preventDefault();
        
        const vendorId = document.getElementById('vendorId').value;
        const payload = {
            vendor_name: document.getElementById('vendor_name').value,
            vendor_type: document.getElementById('vendor_type').value,
            company_name: document.getElementById('company_name').value,
            contact_person: document.getElementById('contact_person').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            address: document.getElementById('address').value,
            status: document.getElementById('status').value
        };

        try {
            const method = vendorId ? 'PUT' : 'POST';
            const url = vendorId ? `${API_BASE}?id=${vendorId}` : API_BASE;

            const response = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            if (data.status === 'success') {
                showToast(vendorId ? 'Vendor updated successfully' : 'Vendor added successfully');
                closeModal(vendorModal);
                loadVendors();
                loadVendorDropdowns();
            } else {
                showToast(data.message || 'Error saving vendor', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error saving vendor', 'error');
        }
    };

    // ===== VENDOR TAB FUNCTIONS =====
    async function loadVendors(search = '', status = '') {
        try {
            let url = `${API_BASE}?action=get_vendors`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (status) url += `&status=${encodeURIComponent(status)}`;

            const response = await fetch(url);
            const data = await response.json();

            const tableBody = document.getElementById('vendorsTable');
            const emptyState = document.getElementById('vendorsEmpty');

            if (data.status === 'success' && data.vendors && data.vendors.length > 0) {
                emptyState.classList.add('hidden');
                tableBody.innerHTML = data.vendors.map(vendor => `
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3 font-semibold">${vendor.vendor_name}</td>
                        <td class="px-6 py-3 text-sm">${vendor.email}</td>
                        <td class="px-6 py-3 text-sm">${vendor.vendor_type || 'N/A'}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold status-badge status-${vendor.status.toLowerCase()}">${vendor.status}</span>
                        </td>
                        <td class="px-6 py-3 text-sm">${new Date(vendor.created_at).toLocaleDateString()}</td>
                        <td class="px-6 py-3 flex gap-2">
                            <button onclick="window.editVendor(${vendor.id})" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Edit</button>
                            <button onclick="window.deleteVendor(${vendor.id})" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Delete</button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            console.error(err);
            showToast('Error loading vendors', 'error');
        }
    }

    window.editVendor = async function(vendorId) {
        try {
            const response = await fetch(`${API_BASE}?action=get_vendor_details&id=${vendorId}`);
            const data = await response.json();

            if (data.status === 'success' && data.vendor) {
                const vendor = data.vendor;
                document.getElementById('modalTitle').textContent = 'Edit Vendor';
                document.getElementById('vendorId').value = vendor.id;
                document.getElementById('vendor_name').value = vendor.vendor_name;
                document.getElementById('vendor_type').value = vendor.vendor_type || '';
                document.getElementById('company_name').value = vendor.company_name || '';
                document.getElementById('contact_person').value = vendor.contact_person || '';
                document.getElementById('email').value = vendor.email;
                document.getElementById('phone').value = vendor.phone || '';
                document.getElementById('address').value = vendor.address || '';
                document.getElementById('status').value = vendor.status;
                
                openModal(vendorModal);
            }
        } catch (err) {
            console.error(err);
            showToast('Error loading vendor', 'error');
        }
    };

    window.deleteVendor = async function(vendorId) {
        if (!confirm('Are you sure you want to delete this vendor?')) return;
        try {
            const response = await fetch(`${API_BASE}?id=${vendorId}`, { method: 'DELETE' });
            const data = await response.json();
            if (data.status === 'success') {
                showToast('Vendor deleted successfully');
                loadVendors();
            } else {
                showToast(data.message || 'Error deleting vendor', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error deleting vendor', 'error');
        }
    };

    // ===== VALIDATION TAB FUNCTIONS =====
    window.loadValidations = async function() {
        try {
            const response = await fetch(`${API_BASE}?action=get_validations`);
            const data = await response.json();

            if (data.status === 'success' && data.validations && data.validations.length > 0) {
                document.getElementById('validationEmpty').classList.add('hidden');
                document.getElementById('validationContent').innerHTML = data.validations.map(val => `
                    <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-600">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="font-semibold text-lg">${val.vendor_name}</h3>
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">Validation</span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div><strong>Business License:</strong> ${val.business_license_verified ? '✅' : '❌'}</div>
                            <div><strong>Tax Compliance:</strong> ${val.tax_compliance_verified ? '✅' : '❌'}</div>
                            <div><strong>Financial Stability:</strong> ${val.financial_stability_verified ? '✅' : '❌'}</div>
                        </div>
                        <button onclick="window.editValidation(${val.id})" class="mt-3 w-full bg-indigo-600 text-white px-3 py-2 rounded text-xs hover:bg-indigo-700">Edit Validation</button>
                    </div>
                `).join('');
            } else {
                document.getElementById('validationEmpty').classList.remove('hidden');
                document.getElementById('validationContent').innerHTML = '';
            }
        } catch (err) {
            console.error(err);
            showToast('Error loading validations', 'error');
        }
    };

    window.editValidation = async function(validationId) {
        const vendorId = document.getElementById('validationVendor').value;
        if (!vendorId) {
            showToast('Please select a vendor first', 'error');
            return;
        }

        document.getElementById('validationVendorId').value = vendorId;
        openModal(validationModal);
    };

    window.submitValidationForm = async function(event) {
        event.preventDefault();
        
        const payload = {
            action: 'save_validation',
            vendor_id: document.getElementById('validationVendorId').value,
            business_license: document.getElementById('business_license').value,
            tax_compliance: document.getElementById('tax_compliance').value,
            financial_stability: document.getElementById('financial_stability').value
        };

        try {
            const response = await fetch(API_BASE, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            if (data.status === 'success') {
                showToast('Validation saved successfully');
                closeModal(validationModal);
                loadValidations();
            } else {
                showToast(data.message || 'Error saving validation', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error saving validation', 'error');
        }
    };

    window.closeValidationModal = function() {
        closeModal(validationModal);
    };

    // ===== VERIFICATION TAB FUNCTIONS =====
    window.loadVerifications = async function() {
        try {
            const response = await fetch(`${API_BASE}?action=get_verifications`);
            const data = await response.json();

            const tableBody = document.getElementById('verificationsTable');
            const emptyState = document.getElementById('verificationsEmpty');

            if (data.status === 'success' && data.verifications && data.verifications.length > 0) {
                emptyState.classList.add('hidden');
                tableBody.innerHTML = data.verifications.map(ver => `
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3">${ver.vendor_name}</td>
                        <td class="px-6 py-3 text-sm">${ver.type || 'N/A'}</td>
                        <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">${ver.status || 'Pending'}</span></td>
                        <td class="px-6 py-3 text-sm">${ver.date || '-'}</td>
                        <td class="px-6 py-3 text-sm">${ver.notes || '-'}</td>
                        <td class="px-6 py-3 flex gap-2">
                            <button onclick="window.editVerification(${ver.id})" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Edit</button>
                            <button onclick="window.deleteVerification(${ver.id})" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Delete</button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            console.error(err);
            showToast('Error loading verifications', 'error');
        }
    };

    window.addNewVerification = function() {
        document.getElementById('verificationVendorSelect').innerHTML = '<option value="">-- Select Vendor --</option>';
        loadVendorDropdownForModal('verificationVendorSelect');
        openModal(verificationModal);
    };

    window.editVerification = async function(verificationId) {
        try {
            const response = await fetch(`${API_BASE}?action=get_verification&id=${verificationId}`);
            const data = await response.json();

            if (data.status === 'success' && data.verification) {
                const ver = data.verification;
                document.getElementById('verificationVendorSelect').value = ver.vendor_id;
                document.getElementById('verification_type').value = ver.type || '';
                document.getElementById('verification_status').value = ver.status || 'Pending';
                document.getElementById('verification_date').value = ver.date || '';
                document.getElementById('verification_notes').value = ver.notes || '';
                
                openModal(verificationModal);
            }
        } catch (err) {
            console.error(err);
            showToast('Error loading verification', 'error');
        }
    };

    window.deleteVerification = async function(verificationId) {
        if (!confirm('Delete this verification?')) return;
        try {
            const response = await fetch(`${API_BASE}?action=delete_verification&id=${verificationId}`, { method: 'DELETE' });
            const data = await response.json();
            if (data.status === 'success') {
                showToast('Verification deleted');
                loadVerifications();
            } else {
                showToast(data.message || 'Error deleting', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error deleting verification', 'error');
        }
    };

    window.submitVerificationForm = async function(event) {
        event.preventDefault();
        
        const payload = {
            action: 'save_verification',
            vendor_id: document.getElementById('verificationVendorSelect').value,
            type: document.getElementById('verification_type').value,
            status: document.getElementById('verification_status').value,
            date: document.getElementById('verification_date').value,
            notes: document.getElementById('verification_notes').value
        };

        try {
            const response = await fetch(API_BASE, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            if (data.status === 'success') {
                showToast('Verification saved');
                closeModal(verificationModal);
                loadVerifications();
            } else {
                showToast(data.message || 'Error saving', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error saving verification', 'error');
        }
    };

    window.closeVerificationModal = function() {
        closeModal(verificationModal);
    };

    // ===== REQUIREMENTS TAB FUNCTIONS =====
    window.loadRequirements = async function() {
        try {
            const response = await fetch(`${API_BASE}?action=get_requirements`);
            const data = await response.json();

            const tableBody = document.getElementById('requirementsTable');
            const emptyState = document.getElementById('requirementsEmpty');

            if (data.status === 'success' && data.requirements && data.requirements.length > 0) {
                emptyState.classList.add('hidden');
                tableBody.innerHTML = data.requirements.map(req => `
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3">${req.vendor_name}</td>
                        <td class="px-6 py-3 text-sm">${req.type || 'N/A'}</td>
                        <td class="px-6 py-3 text-sm">${req.description || '-'}</td>
                        <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">${req.status || 'Pending'}</span></td>
                        <td class="px-6 py-3 text-sm">${req.deadline || '-'}</td>
                        <td class="px-6 py-3 flex gap-2">
                            <button onclick="window.editRequirement(${req.id})" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Edit</button>
                            <button onclick="window.deleteRequirement(${req.id})" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Delete</button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            console.error(err);
            showToast('Error loading requirements', 'error');
        }
    };

    window.addNewRequirement = function() {
        document.getElementById('requirementVendorSelect').innerHTML = '<option value="">-- Select Vendor --</option>';
        loadVendorDropdownForModal('requirementVendorSelect');
        openModal(requirementModal);
    };

    window.editRequirement = async function(requirementId) {
        try {
            const response = await fetch(`${API_BASE}?action=get_requirement&id=${requirementId}`);
            const data = await response.json();

            if (data.status === 'success' && data.requirement) {
                const req = data.requirement;
                document.getElementById('requirementVendorSelect').value = req.vendor_id;
                document.getElementById('requirement_type').value = req.type || '';
                document.getElementById('requirement_description').value = req.description || '';
                document.getElementById('requirement_status').value = req.status || 'Pending';
                document.getElementById('requirement_deadline').value = req.deadline || '';
                
                openModal(requirementModal);
            }
        } catch (err) {
            console.error(err);
            showToast('Error loading requirement', 'error');
        }
    };

    window.deleteRequirement = async function(requirementId) {
        if (!confirm('Delete this requirement?')) return;
        try {
            const response = await fetch(`${API_BASE}?action=delete_requirement&id=${requirementId}`, { method: 'DELETE' });
            const data = await response.json();
            if (data.status === 'success') {
                showToast('Requirement deleted');
                loadRequirements();
            } else {
                showToast(data.message || 'Error deleting', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error deleting requirement', 'error');
        }
    };

    window.submitRequirementForm = async function(event) {
        event.preventDefault();
        
        const payload = {
            action: 'save_requirement',
            vendor_id: document.getElementById('requirementVendorSelect').value,
            type: document.getElementById('requirement_type').value,
            description: document.getElementById('requirement_description').value,
            status: document.getElementById('requirement_status').value,
            deadline: document.getElementById('requirement_deadline').value
        };

        try {
            const response = await fetch(API_BASE, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            if (data.status === 'success') {
                showToast('Requirement saved');
                closeModal(requirementModal);
                loadRequirements();
            } else {
                showToast(data.message || 'Error saving', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error saving requirement', 'error');
        }
    };

    window.closeRequirementModal = function() {
        closeModal(requirementModal);
    };

    // ===== HELPER FUNCTIONS =====
    async function loadVendorDropdowns() {
        try {
            const response = await fetch(`${API_BASE}?action=get_vendors&limit=1000`);
            const data = await response.json();

            if (data.status === 'success' && data.vendors) {
                const dropdowns = ['validationVendor', 'verificationVendor', 'requirementVendor'];
                dropdowns.forEach(dropId => {
                    const select = document.getElementById(dropId);
                    if (select) {
                        const currentValue = select.value;
                        select.innerHTML = '<option value="">-- All Vendors --</option>' + 
                            data.vendors.map(v => `<option value="${v.id}">${v.vendor_name}</option>`).join('');
                        select.value = currentValue;
                    }
                });
            }
        } catch (err) {
            console.error('Error loading vendor dropdowns:', err);
        }
    }

    async function loadVendorDropdownForModal(dropdownId) {
        try {
            const response = await fetch(`${API_BASE}?action=get_vendors&limit=1000`);
            const data = await response.json();

            if (data.status === 'success' && data.vendors) {
                const select = document.getElementById(dropdownId);
                if (select) {
                    select.innerHTML = '<option value="">-- Select Vendor --</option>' + 
                        data.vendors.map(v => `<option value="${v.id}">${v.vendor_name}</option>`).join('');
                }
            }
        } catch (err) {
            console.error('Error loading vendor dropdown:', err);
        }
    }

    // Logout function
    window.logout = function() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = '../api/auth.php?action=logout';
        }
    };

    // Initial load
    loadVendors();
    loadVendorDropdowns();
});
