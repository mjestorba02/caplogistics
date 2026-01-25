document.addEventListener('DOMContentLoaded', () => {
    const API_BASE = '../api/vendor_portal.php';

    // Modal elements
    const registrationModal = document.getElementById('registrationModal');
    const validationModal = document.getElementById('validationModal');
    const verificationModal = document.getElementById('verificationModal');
    const requirementModal = document.getElementById('requirementModal');
    const vendorDetailsModal = document.getElementById('vendorDetailsModal');

    // Forms
    const registrationForm = document.getElementById('registrationForm');
    const validationForm = document.getElementById('validationForm');
    const verificationForm = document.getElementById('verificationForm');
    const requirementForm = document.getElementById('requirementForm');

    // Tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    // Helper function to show toast
    function showToast(message, type = 'success') {
        Toastify({
            text: message,
            duration: 3000,
            gravity: 'top',
            position: 'right',
            backgroundColor: type === 'success' ? '#10b981' : '#ef4444'
        }).showToast();
    }

    // Modal management functions
    function openModal(modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal(modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Tab switching
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'border-indigo-600', 'text-indigo-600');
                btn.classList.add('border-transparent', 'text-gray-600');
            });
            button.classList.add('active', 'border-indigo-600', 'text-indigo-600');

            const tabId = button.id.replace('tab-', '');
            tabContents.forEach(content => {
                if (content.id === `content-${tabId}`) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            });

            // Load data for the active tab
            if (tabId === 'vendors') {
                loadVendors();
            } else if (tabId === 'validation') {
                loadValidations();
            } else if (tabId === 'verification') {
                loadVerifications();
            } else if (tabId === 'requirements') {
                loadRequirements();
            }
        });
    });

    // ===== REGISTRATION MANAGEMENT =====
    document.getElementById('openRegistrationModal').addEventListener('click', () => {
        document.getElementById('registrationModalTitle').textContent = 'Register New Vendor';
        document.getElementById('vendorId').value = '';
        registrationForm.reset();
        openModal(registrationModal);
    });

    document.getElementById('closeRegistrationModal').addEventListener('click', () => {
        closeModal(registrationModal);
    });

    registrationForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const vendorId = document.getElementById('vendorId').value;
        
        const payload = {
            vendor_name: document.getElementById('vendor_name').value,
            company_name: document.getElementById('company_name').value,
            contact_person: document.getElementById('contact_person').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            address: document.getElementById('address').value,
            city: document.getElementById('city').value,
            state_province: document.getElementById('state_province').value,
            country: document.getElementById('country').value,
            postal_code: document.getElementById('postal_code').value,
            tax_id: document.getElementById('tax_id').value,
            registration_number: document.getElementById('registration_number').value,
            business_type: document.getElementById('business_type').value,
            annual_revenue: document.getElementById('annual_revenue').value,
            employees_count: document.getElementById('employees_count').value,
            website_url: document.getElementById('website_url').value,
            years_in_business: document.getElementById('years_in_business').value,
            status: vendorId ? undefined : 'Draft'
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
                showToast(data.message || 'Vendor saved successfully');
                closeModal(registrationModal);
                loadVendors();
                // Reload vendor lists in other modals
                loadVendorDropdowns();
            } else {
                showToast(data.message || 'Error saving vendor', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error saving vendor', 'error');
        }
    });

    // ===== VENDORS TAB =====
    async function loadVendors(search = '', status = '') {
        try {
            let url = `${API_BASE}?action=get_vendors`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (status) url += `&status=${encodeURIComponent(status)}`;

            const response = await fetch(url);
            const data = await response.json();

            const tableBody = document.getElementById('vendorTable');
            const emptyState = document.getElementById('emptyStateVendors');

            if (data.status === 'success' && data.vendors && data.vendors.length > 0) {
                emptyState.classList.add('hidden');
                tableBody.innerHTML = data.vendors.map(vendor => `
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3 font-semibold">${vendor.vendor_name}</td>
                        <td class="px-6 py-3">${vendor.company_name}</td>
                        <td class="px-6 py-3 text-sm">${vendor.email}</td>
                        <td class="px-6 py-3 text-sm">${vendor.business_type}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold ${getStatusBadgeClass(vendor.status)}">${vendor.status}</span>
                        </td>
                        <td class="px-6 py-3 text-sm">${new Date(vendor.created_at).toLocaleDateString()}</td>
                        <td class="px-6 py-3 flex gap-2">
                            <button onclick="window.viewVendorDetails(${vendor.id})" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">View</button>
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

    window.viewVendorDetails = async (vendorId) => {
        try {
            const response = await fetch(`${API_BASE}?action=get_vendor_details&id=${vendorId}`);
            const data = await response.json();

            if (data.status === 'success' && data.vendor) {
                const vendor = data.vendor;
                const detailsContent = document.getElementById('vendorDetailsContent');
                detailsContent.innerHTML = `
                    <div class="grid grid-cols-2 gap-4">
                        <div><strong>Vendor Name:</strong> ${vendor.vendor_name}</div>
                        <div><strong>Company:</strong> ${vendor.company_name}</div>
                        <div><strong>Contact Person:</strong> ${vendor.contact_person}</div>
                        <div><strong>Email:</strong> ${vendor.email}</div>
                        <div><strong>Phone:</strong> ${vendor.phone}</div>
                        <div><strong>Business Type:</strong> ${vendor.business_type}</div>
                        <div><strong>Status:</strong> <span class="px-2 py-1 rounded text-xs font-semibold ${getStatusBadgeClass(vendor.status)}">${vendor.status}</span></div>
                        <div><strong>Years in Business:</strong> ${vendor.years_in_business || 'N/A'}</div>
                    </div>
                    <hr class="my-4" />
                    <div class="grid grid-cols-2 gap-4">
                        <div><strong>Address:</strong> ${vendor.address || 'N/A'}</div>
                        <div><strong>City:</strong> ${vendor.city || 'N/A'}</div>
                        <div><strong>State:</strong> ${vendor.state_province || 'N/A'}</div>
                        <div><strong>Country:</strong> ${vendor.country || 'N/A'}</div>
                        <div><strong>Postal Code:</strong> ${vendor.postal_code || 'N/A'}</div>
                        <div><strong>Tax ID:</strong> ${vendor.tax_id || 'N/A'}</div>
                    </div>
                    <hr class="my-4" />
                    <div class="grid grid-cols-2 gap-4">
                        <div><strong>Annual Revenue:</strong> $${parseFloat(vendor.annual_revenue || 0).toLocaleString()}</div>
                        <div><strong>Employees:</strong> ${vendor.employees_count || 'N/A'}</div>
                        <div><strong>Website:</strong> ${vendor.website_url ? `<a href="${vendor.website_url}" target="_blank" class="text-indigo-600 hover:underline">${vendor.website_url}</a>` : 'N/A'}</div>
                        <div><strong>Registered:</strong> ${new Date(vendor.created_at).toLocaleDateString()}</div>
                    </div>
                `;
                
                document.getElementById('vendorId').value = vendorId;
                openModal(vendorDetailsModal);
            }
        } catch (err) {
            console.error(err);
            showToast('Error loading vendor details', 'error');
        }
    };

    window.editVendor = async (vendorId) => {
        try {
            const response = await fetch(`${API_BASE}?action=get_vendor_details&id=${vendorId}`);
            const data = await response.json();

            if (data.status === 'success' && data.vendor) {
                const vendor = data.vendor;
                document.getElementById('registrationModalTitle').textContent = 'Edit Vendor';
                document.getElementById('vendorId').value = vendor.id;
                document.getElementById('vendor_name').value = vendor.vendor_name;
                document.getElementById('company_name').value = vendor.company_name;
                document.getElementById('contact_person').value = vendor.contact_person;
                document.getElementById('email').value = vendor.email;
                document.getElementById('phone').value = vendor.phone;
                document.getElementById('address').value = vendor.address || '';
                document.getElementById('city').value = vendor.city || '';
                document.getElementById('state_province').value = vendor.state_province || '';
                document.getElementById('country').value = vendor.country || '';
                document.getElementById('postal_code').value = vendor.postal_code || '';
                document.getElementById('tax_id').value = vendor.tax_id || '';
                document.getElementById('registration_number').value = vendor.registration_number || '';
                document.getElementById('business_type').value = vendor.business_type;
                document.getElementById('annual_revenue').value = vendor.annual_revenue || '';
                document.getElementById('employees_count').value = vendor.employees_count || '';
                document.getElementById('website_url').value = vendor.website_url || '';
                document.getElementById('years_in_business').value = vendor.years_in_business || '';
                
                closeModal(vendorDetailsModal);
                openModal(registrationModal);
            }
        } catch (err) {
            console.error(err);
            showToast('Error loading vendor', 'error');
        }
    };

    window.deleteVendor = async (vendorId) => {
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

    // Vendor filter
    document.getElementById('applyVendorFilter').addEventListener('click', () => {
        const search = document.getElementById('vendorSearch').value;
        const status = document.getElementById('vendorStatus').value;
        loadVendors(search, status);
    });

    document.getElementById('clearVendorFilter').addEventListener('click', () => {
        document.getElementById('vendorSearch').value = '';
        document.getElementById('vendorStatus').value = '';
        loadVendors();
    });

    // ===== VALIDATION TAB =====
    async function loadValidations(vendorId = '', status = '') {
        try {
            let url = `${API_BASE}?action=get_validations`;
            if (vendorId) url += `&vendor_id=${vendorId}`;
            if (status) url += `&status=${encodeURIComponent(status)}`;

            const response = await fetch(url);
            const data = await response.json();

            const grid = document.getElementById('validationGrid');
            const emptyState = document.getElementById('emptyStateValidation');

            if (data.status === 'success' && data.validations && data.validations.length > 0) {
                emptyState.classList.add('hidden');
                grid.innerHTML = data.validations.map(val => `
                    <div class="bg-white p-4 rounded-lg shadow border-l-4 border-indigo-600">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-semibold">${val.vendor_name}</h3>
                            <span class="px-2 py-1 rounded text-xs font-semibold ${getValidationStatusClass(val.validation_status)}">${val.validation_status}</span>
                        </div>
                        <div class="text-sm space-y-1 text-gray-600">
                            <div class="flex items-center gap-2"><i class="fas ${val.business_license_verified ? 'fa-check text-green-500' : 'fa-times text-red-500'}"></i> Business License</div>
                            <div class="flex items-center gap-2"><i class="fas ${val.tax_compliance_verified ? 'fa-check text-green-500' : 'fa-times text-red-500'}"></i> Tax Compliance</div>
                            <div class="flex items-center gap-2"><i class="fas ${val.financial_statements_verified ? 'fa-check text-green-500' : 'fa-times text-red-500'}"></i> Financial Statements</div>
                            <div class="flex items-center gap-2"><i class="fas ${val.references_checked ? 'fa-check text-green-500' : 'fa-times text-red-500'}"></i> References</div>
                            <div class="flex items-center gap-2"><i class="fas ${val.insurance_documents_verified ? 'fa-check text-green-500' : 'fa-times text-red-500'}"></i> Insurance Docs</div>
                            <div class="flex items-center gap-2"><i class="fas ${val.compliance_documents_verified ? 'fa-check text-green-500' : 'fa-times text-red-500'}"></i> Compliance Docs</div>
                            <div class="flex items-center gap-2"><i class="fas ${val.background_check_done ? 'fa-check text-green-500' : 'fa-times text-red-500'}"></i> Background Check</div>
                        </div>
                        <button onclick="window.editValidation(${val.id})" class="mt-3 w-full bg-indigo-600 text-white px-3 py-2 rounded text-xs hover:bg-indigo-700">Edit Validation</button>
                    </div>
                `).join('');
            } else {
                grid.innerHTML = '';
                emptyState.classList.remove('hidden');
            }
        } catch (err) {
            console.error(err);
            showToast('Error loading validations', 'error');
        }
    }

    window.editValidation = async (validationId) => {
        try {
            const response = await fetch(`${API_BASE}?action=get_validation&id=${validationId}`);
            const data = await response.json();

            if (data.status === 'success' && data.validation) {
                const val = data.validation;
                document.getElementById('validation_vendor_id').value = val.vendor_id;
                document.getElementById('business_license').checked = !!val.business_license_verified;
                document.getElementById('tax_compliance').checked = !!val.tax_compliance_verified;
                document.getElementById('financial_statements').checked = !!val.financial_statements_verified;
                document.getElementById('references_checked').checked = !!val.references_checked;
                document.getElementById('insurance_documents').checked = !!val.insurance_documents_verified;
                document.getElementById('compliance_documents').checked = !!val.compliance_documents_verified;
                document.getElementById('background_check').checked = !!val.background_check_done;
                document.getElementById('validation_status_select').value = val.validation_status;
                document.getElementById('validation_notes').value = val.validation_notes || '';
                
                openModal(validationModal);
            }
        } catch (err) {
            console.error(err);
            showToast('Error loading validation', 'error');
        }
    };

    validationForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const vendorId = document.getElementById('validation_vendor_id').value;
        
        const payload = {
            action: 'save_validation',
            vendor_id: vendorId,
            business_license_verified: document.getElementById('business_license').checked ? 1 : 0,
            tax_compliance_verified: document.getElementById('tax_compliance').checked ? 1 : 0,
            financial_statements_verified: document.getElementById('financial_statements').checked ? 1 : 0,
            references_checked: document.getElementById('references_checked').checked ? 1 : 0,
            insurance_documents_verified: document.getElementById('insurance_documents').checked ? 1 : 0,
            compliance_documents_verified: document.getElementById('compliance_documents').checked ? 1 : 0,
            background_check_done: document.getElementById('background_check').checked ? 1 : 0,
            validation_status: document.getElementById('validation_status_select').value,
            validation_notes: document.getElementById('validation_notes').value
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
    });

    document.querySelectorAll('.closeValidationModal').forEach(btn => {
        btn.addEventListener('click', () => closeModal(validationModal));
    });

    document.getElementById('applyValidationFilter').addEventListener('click', () => {
        const vendorId = document.getElementById('validationVendor').value;
        const status = document.getElementById('validationStatus').value;
        loadValidations(vendorId, status);
    });

    document.getElementById('clearValidationFilter').addEventListener('click', () => {
        document.getElementById('validationVendor').value = '';
        document.getElementById('validationStatus').value = '';
        loadValidations();
    });

    // ===== VERIFICATION TAB =====
    async function loadVerifications(vendorId = '', type = '') {
        try {
            let url = `${API_BASE}?action=get_verifications`;
            if (vendorId) url += `&vendor_id=${vendorId}`;
            if (type) url += `&type=${encodeURIComponent(type)}`;

            const response = await fetch(url);
            const data = await response.json();

            const tableBody = document.getElementById('verificationTable');
            const emptyState = document.getElementById('emptyStateVerification');

            if (data.status === 'success' && data.verifications && data.verifications.length > 0) {
                emptyState.classList.add('hidden');
                tableBody.innerHTML = data.verifications.map(ver => `
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3 font-semibold">${ver.vendor_name}</td>
                        <td class="px-6 py-3 text-sm">${ver.verification_type}</td>
                        <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${getVerificationStatusClass(ver.verification_status)}">${ver.verification_status}</span></td>
                        <td class="px-6 py-3 text-sm">${ver.verification_date ? new Date(ver.verification_date).toLocaleDateString() : 'Pending'}</td>
                        <td class="px-6 py-3 text-sm">${ver.verified_by || 'N/A'}</td>
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
    }

    window.editVerification = async (verificationId) => {
        try {
            const response = await fetch(`${API_BASE}?action=get_verification&id=${verificationId}`);
            const data = await response.json();

            if (data.status === 'success' && data.verification) {
                const ver = data.verification;
                document.getElementById('verification_vendor_id').value = ver.vendor_id;
                document.getElementById('verification_type_select').value = ver.verification_type;
                document.getElementById('verification_status_select').value = ver.verification_status;
                document.getElementById('verification_notes').value = ver.verification_notes || '';
                
                openModal(verificationModal);
            }
        } catch (err) {
            console.error(err);
            showToast('Error loading verification', 'error');
        }
    };

    window.deleteVerification = async (verificationId) => {
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

    verificationForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            action: 'save_verification',
            vendor_id: document.getElementById('verification_vendor_id').value,
            verification_type: document.getElementById('verification_type_select').value,
            verification_status: document.getElementById('verification_status_select').value,
            verification_notes: document.getElementById('verification_notes').value
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
    });

    document.querySelectorAll('.closeVerificationModal').forEach(btn => {
        btn.addEventListener('click', () => closeModal(verificationModal));
    });

    document.getElementById('applyVerificationFilter').addEventListener('click', () => {
        const vendorId = document.getElementById('verificationVendor').value;
        const type = document.getElementById('verificationType').value;
        loadVerifications(vendorId, type);
    });

    document.getElementById('clearVerificationFilter').addEventListener('click', () => {
        document.getElementById('verificationVendor').value = '';
        document.getElementById('verificationType').value = '';
        loadVerifications();
    });

    // ===== REQUIREMENTS TAB =====
    async function loadRequirements(vendorId = '', type = '') {
        try {
            let url = `${API_BASE}?action=get_requirements`;
            if (vendorId) url += `&vendor_id=${vendorId}`;
            if (type) url += `&type=${encodeURIComponent(type)}`;

            const response = await fetch(url);
            const data = await response.json();

            const tableBody = document.getElementById('requirementTable');
            const emptyState = document.getElementById('emptyStateRequirements');

            if (data.status === 'success' && data.requirements && data.requirements.length > 0) {
                emptyState.classList.add('hidden');
                tableBody.innerHTML = data.requirements.map(req => `
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3 font-semibold">${req.vendor_name}</td>
                        <td class="px-6 py-3 text-sm">${req.requirement_name}</td>
                        <td class="px-6 py-3 text-sm">${req.requirement_type}</td>
                        <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs ${req.is_mandatory ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'}">${req.is_mandatory ? 'Yes' : 'No'}</span></td>
                        <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold ${getRequirementStatusClass(req.requirement_status)}">${req.requirement_status}</span></td>
                        <td class="px-6 py-3 text-sm">${req.expires_date || 'No expiry'}</td>
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
    }

    window.editRequirement = async (requirementId) => {
        try {
            const response = await fetch(`${API_BASE}?action=get_requirement&id=${requirementId}`);
            const data = await response.json();

            if (data.status === 'success' && data.requirement) {
                const req = data.requirement;
                document.getElementById('requirement_vendor_id').value = req.vendor_id;
                document.getElementById('requirement_type_select').value = req.requirement_type;
                document.getElementById('requirement_name').value = req.requirement_name;
                document.getElementById('requirement_description').value = req.requirement_description || '';
                document.getElementById('is_mandatory').checked = !!req.is_mandatory;
                document.getElementById('requirement_status_select').value = req.requirement_status;
                document.getElementById('expires_date').value = req.expires_date || '';
                
                openModal(requirementModal);
            }
        } catch (err) {
            console.error(err);
            showToast('Error loading requirement', 'error');
        }
    };

    window.deleteRequirement = async (requirementId) => {
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

    requirementForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            action: 'save_requirement',
            vendor_id: document.getElementById('requirement_vendor_id').value,
            requirement_type: document.getElementById('requirement_type_select').value,
            requirement_name: document.getElementById('requirement_name').value,
            requirement_description: document.getElementById('requirement_description').value,
            is_mandatory: document.getElementById('is_mandatory').checked ? 1 : 0,
            requirement_status: document.getElementById('requirement_status_select').value,
            expires_date: document.getElementById('expires_date').value
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
    });

    document.querySelectorAll('.closeRequirementModal').forEach(btn => {
        btn.addEventListener('click', () => closeModal(requirementModal));
    });

    document.getElementById('applyRequirementFilter').addEventListener('click', () => {
        const vendorId = document.getElementById('requirementVendor').value;
        const type = document.getElementById('requirementType').value;
        loadRequirements(vendorId, type);
    });

    document.getElementById('clearRequirementFilter').addEventListener('click', () => {
        document.getElementById('requirementVendor').value = '';
        document.getElementById('requirementType').value = '';
        loadRequirements();
    });

    // ===== VENDOR DETAILS MODAL =====
    document.getElementById('editVendorBtn').addEventListener('click', () => {
        const vendorId = document.getElementById('vendorId').value;
        window.editVendor(vendorId);
    });

    document.querySelectorAll('.closeDetailsModal').forEach(btn => {
        btn.addEventListener('click', () => closeModal(vendorDetailsModal));
    });

    // ===== LOAD VENDOR DROPDOWNS =====
    async function loadVendorDropdowns() {
        try {
            const response = await fetch(`${API_BASE}?action=get_vendors&limit=1000`);
            const data = await response.json();

            if (data.status === 'success' && data.vendors) {
                const dropdowns = ['validationVendor', 'verificationVendor', 'requirementVendor'];
                dropdowns.forEach(dropId => {
                    const select = document.getElementById(dropId);
                    const currentValue = select.value;
                    select.innerHTML = '<option value="">All Vendors</option>' + 
                        data.vendors.map(v => `<option value="${v.id}">${v.vendor_name}</option>`).join('');
                    select.value = currentValue;
                });
            }
        } catch (err) {
            console.error('Error loading vendor dropdowns:', err);
        }
    }

    // ===== STATUS BADGE CLASSES =====
    function getStatusBadgeClass(status) {
        const classes = {
            'Draft': 'bg-gray-100 text-gray-800',
            'Submitted': 'bg-blue-100 text-blue-800',
            'Under Review': 'bg-yellow-100 text-yellow-800',
            'Approved': 'bg-green-100 text-green-800',
            'Rejected': 'bg-red-100 text-red-800',
            'Inactive': 'bg-gray-100 text-gray-800',
            'Active': 'bg-green-100 text-green-800',
            'Archived': 'bg-gray-100 text-gray-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    function getValidationStatusClass(status) {
        const classes = {
            'Pending': 'bg-yellow-100 text-yellow-800',
            'In Progress': 'bg-blue-100 text-blue-800',
            'Approved': 'bg-green-100 text-green-800',
            'Failed': 'bg-red-100 text-red-800',
            'Incomplete': 'bg-gray-100 text-gray-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    function getVerificationStatusClass(status) {
        const classes = {
            'Pending': 'bg-yellow-100 text-yellow-800',
            'In Progress': 'bg-blue-100 text-blue-800',
            'Verified': 'bg-green-100 text-green-800',
            'Failed': 'bg-red-100 text-red-800',
            'Expired': 'bg-red-100 text-red-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    function getRequirementStatusClass(status) {
        const classes = {
            'Not Started': 'bg-gray-100 text-gray-800',
            'In Progress': 'bg-blue-100 text-blue-800',
            'Submitted': 'bg-yellow-100 text-yellow-800',
            'Approved': 'bg-green-100 text-green-800',
            'Rejected': 'bg-red-100 text-red-800',
            'Expired': 'bg-red-100 text-red-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    // Initial load
    loadVendors();
    loadVendorDropdowns();
});
