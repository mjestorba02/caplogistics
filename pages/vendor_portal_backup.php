<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location:../index.php');
    exit();
}
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
    <!-- Breadcrumb -->
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <span>Vendor Portal</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800">Vendor Portal</h1>
        <button id="openRegistrationModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
            + Register New Vendor
        </button>
    </div>

    <!-- Tab Navigation -->
    <div class="flex gap-4 mb-6 border-b">
        <button id="tab-vendors" class="tab-button active px-4 py-2 border-b-2 border-indigo-600 text-indigo-600 font-semibold">
            <i class="fas fa-users"></i> Vendors
        </button>
        <button id="tab-validation" class="tab-button px-4 py-2 border-b-2 border-transparent text-gray-600 font-semibold hover:text-indigo-600">
            <i class="fas fa-check-circle"></i> Validation
        </button>
        <button id="tab-verification" class="tab-button px-4 py-2 border-b-2 border-transparent text-gray-600 font-semibold hover:text-indigo-600">
            <i class="fas fa-shield-alt"></i> Verification
        </button>
        <button id="tab-requirements" class="tab-button px-4 py-2 border-b-2 border-transparent text-gray-600 font-semibold hover:text-indigo-600">
            <i class="fas fa-clipboard-list"></i> Requirements
        </button>
    </div>

    <!-- Tab Content: Vendors -->
    <div id="content-vendors" class="tab-content">
        <!-- Filter Section -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="flex flex-col md:flex-row items-center gap-4">
                <label class="text-gray-700 font-medium whitespace-nowrap">Search Vendor:</label>
                <input id="vendorSearch" type="text" placeholder="Search by name or email..." class="w-full md:w-64 border rounded px-3 py-2" />
                <label class="text-gray-700 font-medium whitespace-nowrap">Status:</label>
                <select id="vendorStatus" class="w-full md:w-48 border rounded px-3 py-2">
                    <option value="">All Status</option>
                    <option value="Draft">Draft</option>
                    <option value="Submitted">Submitted</option>
                    <option value="Under Review">Under Review</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Inactive">Inactive</option>
                </select>
                <button id="applyVendorFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Apply</button>
                <button id="clearVendorFilter" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
            </div>
        </div>

        <!-- Vendors Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-200 border-b">
                    <tr>
                        <th class="px-6 py-3">Vendor Name</th>
                        <th class="px-6 py-3">Company</th>
                        <th class="px-6 py-3">Contact</th>
                        <th class="px-6 py-3">Business Type</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Registered</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="vendorTable"></tbody>
            </table>
            <div id="emptyStateVendors" class="hidden text-center py-8 text-gray-600">No vendors found</div>
        </div>
    </div>

    <!-- Tab Content: Validation -->
    <div id="content-validation" class="tab-content hidden">
        <!-- Filter Section -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="flex flex-col md:flex-row items-center gap-4">
                <label class="text-gray-700 font-medium whitespace-nowrap">Vendor:</label>
                <select id="validationVendor" class="w-full md:w-64 border rounded px-3 py-2">
                    <option value="">All Vendors</option>
                </select>
                <label class="text-gray-700 font-medium whitespace-nowrap">Validation Status:</label>
                <select id="validationStatus" class="w-full md:w-48 border rounded px-3 py-2">
                    <option value="">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Approved">Approved</option>
                    <option value="Failed">Failed</option>
                    <option value="Incomplete">Incomplete</option>
                </select>
                <button id="applyValidationFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Apply</button>
                <button id="clearValidationFilter" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
            </div>
        </div>

        <!-- Validation Cards -->
        <div id="validationGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>
        <div id="emptyStateValidation" class="hidden text-center py-8 text-gray-600">No validations found</div>
    </div>

    <!-- Tab Content: Verification -->
    <div id="content-verification" class="tab-content hidden">
        <!-- Filter Section -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="flex flex-col md:flex-row items-center gap-4">
                <label class="text-gray-700 font-medium whitespace-nowrap">Vendor:</label>
                <select id="verificationVendor" class="w-full md:w-64 border rounded px-3 py-2">
                    <option value="">All Vendors</option>
                </select>
                <label class="text-gray-700 font-medium whitespace-nowrap">Verification Type:</label>
                <select id="verificationType" class="w-full md:w-48 border rounded px-3 py-2">
                    <option value="">All Types</option>
                    <option value="Email">Email</option>
                    <option value="Phone">Phone</option>
                    <option value="Address">Address</option>
                    <option value="Business">Business</option>
                    <option value="Financial">Financial</option>
                    <option value="Compliance">Compliance</option>
                    <option value="References">References</option>
                </select>
                <button id="applyVerificationFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Apply</button>
                <button id="clearVerificationFilter" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
            </div>
        </div>

        <!-- Verification Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-200 border-b">
                    <tr>
                        <th class="px-6 py-3">Vendor</th>
                        <th class="px-6 py-3">Verification Type</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Verification Date</th>
                        <th class="px-6 py-3">Verified By</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="verificationTable"></tbody>
            </table>
            <div id="emptyStateVerification" class="hidden text-center py-8 text-gray-600">No verifications found</div>
        </div>
    </div>

    <!-- Tab Content: Requirements -->
    <div id="content-requirements" class="tab-content hidden">
        <!-- Filter Section -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="flex flex-col md:flex-row items-center gap-4">
                <label class="text-gray-700 font-medium whitespace-nowrap">Vendor:</label>
                <select id="requirementVendor" class="w-full md:w-64 border rounded px-3 py-2">
                    <option value="">All Vendors</option>
                </select>
                <label class="text-gray-700 font-medium whitespace-nowrap">Requirement Type:</label>
                <select id="requirementType" class="w-full md:w-48 border rounded px-3 py-2">
                    <option value="">All Types</option>
                    <option value="Certification">Certification</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Compliance">Compliance</option>
                    <option value="Quality Standard">Quality Standard</option>
                    <option value="Technical">Technical</option>
                    <option value="Financial">Financial</option>
                    <option value="Legal">Legal</option>
                </select>
                <button id="applyRequirementFilter" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Apply</button>
                <button id="clearRequirementFilter" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">Clear</button>
            </div>
        </div>

        <!-- Requirements Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-200 border-b">
                    <tr>
                        <th class="px-6 py-3">Vendor</th>
                        <th class="px-6 py-3">Requirement Name</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Mandatory</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Expires</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="requirementTable"></tbody>
            </table>
            <div id="emptyStateRequirements" class="hidden text-center py-8 text-gray-600">No requirements found</div>
        </div>
    </div>
</div>

<!-- MODALS -->

<!-- Registration Modal -->
<div id="registrationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 my-8">
        <h2 id="registrationModalTitle" class="text-2xl font-bold mb-4">Register New Vendor</h2>
        <form id="registrationForm" class="space-y-4 max-h-[70vh] overflow-y-auto">
            <input type="hidden" id="vendorId" />
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium">Vendor Name *</label>
                    <input id="vendor_name" type="text" class="w-full border rounded px-3 py-2" placeholder="Full vendor name" required />
                </div>
                <div>
                    <label class="block text-gray-700 font-medium">Company Name *</label>
                    <input id="company_name" type="text" class="w-full border rounded px-3 py-2" placeholder="Registered company name" required />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium">Contact Person *</label>
                    <input id="contact_person" type="text" class="w-full border rounded px-3 py-2" placeholder="Full name" required />
                </div>
                <div>
                    <label class="block text-gray-700 font-medium">Email *</label>
                    <input id="email" type="email" class="w-full border rounded px-3 py-2" placeholder="vendor@example.com" required />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium">Phone *</label>
                    <input id="phone" type="tel" class="w-full border rounded px-3 py-2" placeholder="+1-555-0000" required />
                </div>
                <div>
                    <label class="block text-gray-700 font-medium">Business Type *</label>
                    <select id="business_type" class="w-full border rounded px-3 py-2" required>
                        <option value="">Select type</option>
                        <option value="Manufacturer">Manufacturer</option>
                        <option value="Distributor">Distributor</option>
                        <option value="Retailer">Retailer</option>
                        <option value="Service Provider">Service Provider</option>
                        <option value="Wholesaler">Wholesaler</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Address</label>
                <input id="address" type="text" class="w-full border rounded px-3 py-2" placeholder="Street address" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium">City</label>
                    <input id="city" type="text" class="w-full border rounded px-3 py-2" placeholder="City" />
                </div>
                <div>
                    <label class="block text-gray-700 font-medium">State/Province</label>
                    <input id="state_province" type="text" class="w-full border rounded px-3 py-2" placeholder="State or Province" />
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium">Country</label>
                    <input id="country" type="text" class="w-full border rounded px-3 py-2" placeholder="Country" />
                </div>
                <div>
                    <label class="block text-gray-700 font-medium">Postal Code</label>
                    <input id="postal_code" type="text" class="w-full border rounded px-3 py-2" placeholder="Postal code" />
                </div>
                <div>
                    <label class="block text-gray-700 font-medium">Years in Business</label>
                    <input id="years_in_business" type="number" min="0" class="w-full border rounded px-3 py-2" placeholder="Years" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium">Tax ID</label>
                    <input id="tax_id" type="text" class="w-full border rounded px-3 py-2" placeholder="Tax identification" />
                </div>
                <div>
                    <label class="block text-gray-700 font-medium">Registration Number</label>
                    <input id="registration_number" type="text" class="w-full border rounded px-3 py-2" placeholder="Business registration number" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium">Annual Revenue</label>
                    <input id="annual_revenue" type="number" min="0" step="0.01" class="w-full border rounded px-3 py-2" placeholder="Revenue amount" />
                </div>
                <div>
                    <label class="block text-gray-700 font-medium">Employees Count</label>
                    <input id="employees_count" type="number" min="0" class="w-full border rounded px-3 py-2" placeholder="Number of employees" />
                </div>
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Website URL</label>
                <input id="website_url" type="url" class="w-full border rounded px-3 py-2" placeholder="https://example.com" />
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button type="button" id="closeRegistrationModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Vendor</button>
            </div>
        </form>
    </div>
</div>

<!-- Validation Checklist Modal -->
<div id="validationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 my-8">
        <h2 class="text-2xl font-bold mb-4">Validation Checklist</h2>
        <form id="validationForm" class="space-y-3">
            <input type="hidden" id="validation_vendor_id" />
            
            <label class="flex items-center gap-2 cursor-pointer">
                <input id="business_license" type="checkbox" class="validation-checkbox" />
                <span class="text-gray-700">Business License Verified</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input id="tax_compliance" type="checkbox" class="validation-checkbox" />
                <span class="text-gray-700">Tax Compliance Verified</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input id="financial_statements" type="checkbox" class="validation-checkbox" />
                <span class="text-gray-700">Financial Statements Verified</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input id="references_checked" type="checkbox" class="validation-checkbox" />
                <span class="text-gray-700">References Checked</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input id="insurance_documents" type="checkbox" class="validation-checkbox" />
                <span class="text-gray-700">Insurance Documents Verified</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input id="compliance_documents" type="checkbox" class="validation-checkbox" />
                <span class="text-gray-700">Compliance Documents Verified</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input id="background_check" type="checkbox" class="validation-checkbox" />
                <span class="text-gray-700">Background Check Done</span>
            </label>

            <div>
                <label class="block text-gray-700 font-medium mt-4">Validation Status</label>
                <select id="validation_status_select" class="w-full border rounded px-3 py-2">
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Approved">Approved</option>
                    <option value="Failed">Failed</option>
                    <option value="Incomplete">Incomplete</option>
                </select>
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Notes</label>
                <textarea id="validation_notes" class="w-full border rounded px-3 py-2" rows="3" placeholder="Validation notes..."></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button type="button" class="closeValidationModal px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Validation</button>
            </div>
        </form>
    </div>
</div>

<!-- Verification Modal -->
<div id="verificationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 my-8">
        <h2 class="text-2xl font-bold mb-4">Add Verification</h2>
        <form id="verificationForm" class="space-y-4">
            <input type="hidden" id="verification_vendor_id" />
            
            <div>
                <label class="block text-gray-700 font-medium">Verification Type *</label>
                <select id="verification_type_select" class="w-full border rounded px-3 py-2" required>
                    <option value="">Select type</option>
                    <option value="Email">Email</option>
                    <option value="Phone">Phone</option>
                    <option value="Address">Address</option>
                    <option value="Business">Business</option>
                    <option value="Financial">Financial</option>
                    <option value="Compliance">Compliance</option>
                    <option value="References">References</option>
                </select>
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Status *</label>
                <select id="verification_status_select" class="w-full border rounded px-3 py-2" required>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Verified">Verified</option>
                    <option value="Failed">Failed</option>
                    <option value="Expired">Expired</option>
                </select>
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Verification Notes</label>
                <textarea id="verification_notes" class="w-full border rounded px-3 py-2" rows="3" placeholder="Add notes..."></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button type="button" class="closeVerificationModal px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Verification</button>
            </div>
        </form>
    </div>
</div>

<!-- Requirements Modal -->
<div id="requirementModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 my-8">
        <h2 class="text-2xl font-bold mb-4">Add Requirement</h2>
        <form id="requirementForm" class="space-y-4">
            <input type="hidden" id="requirement_vendor_id" />
            
            <div>
                <label class="block text-gray-700 font-medium">Requirement Type *</label>
                <select id="requirement_type_select" class="w-full border rounded px-3 py-2" required>
                    <option value="">Select type</option>
                    <option value="Certification">Certification</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Compliance">Compliance</option>
                    <option value="Quality Standard">Quality Standard</option>
                    <option value="Technical">Technical</option>
                    <option value="Financial">Financial</option>
                    <option value="Legal">Legal</option>
                </select>
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Requirement Name *</label>
                <input id="requirement_name" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., ISO 9001 Certification" required />
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Description</label>
                <textarea id="requirement_description" class="w-full border rounded px-3 py-2" rows="2" placeholder="Describe the requirement..."></textarea>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input id="is_mandatory" type="checkbox" checked />
                    <span class="text-gray-700">Mandatory Requirement</span>
                </label>
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Status *</label>
                <select id="requirement_status_select" class="w-full border rounded px-3 py-2" required>
                    <option value="Not Started">Not Started</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Submitted">Submitted</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Expired">Expired</option>
                </select>
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Expiry Date</label>
                <input id="expires_date" type="date" class="w-full border rounded px-3 py-2" />
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button type="button" class="closeRequirementModal px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Requirement</button>
            </div>
        </form>
    </div>
</div>

<!-- Vendor Details Modal -->
<div id="vendorDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 my-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Vendor Details</h2>
            <button type="button" class="closeDetailsModal text-gray-500 hover:text-red-500 text-2xl">&times;</button>
        </div>
        
        <div id="vendorDetailsContent" class="space-y-3 text-gray-700 max-h-[70vh] overflow-y-auto">
            <!-- Details will be populated by JavaScript -->
        </div>

        <div class="flex justify-end gap-2 pt-4 mt-6 border-t">
            <button type="button" id="editVendorBtn" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Edit</button>
            <button type="button" class="closeDetailsModal px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Close</button>
        </div>
    </div>
</div>

<script src="../scripts/vendor_portal.js"></script>
HTML;
adminLayout($children);
?>
