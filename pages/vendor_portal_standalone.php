<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location:../index.php');
    exit();
}

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'staff';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Portal - Supplier Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f9fafb; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .vendor-portal-container { min-height: 100vh; display: flex; flex-direction: column; }
        .vendor-portal-main { flex: 1; }
        .tab-btn { position: relative; padding-bottom: 1rem; }
        .tab-btn.active { color: #667eea; font-weight: 600; }
        .tab-btn.active::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: #667eea; }
        .modal { transition: opacity 0.3s ease, visibility 0.3s ease; }
        .modal.hidden { opacity: 0; visibility: hidden; }
        .modal:not(.hidden) { opacity: 1; visibility: visible; }
        .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 0.375rem; font-weight: 600; font-size: 0.75rem; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-approved { background-color: #d1fae5; color: #065f46; }
        .status-rejected { background-color: #fee2e2; color: #7f1d1d; }
        .fade-in { animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body class="bg-gray-50">

<!-- HEADER/NAVBAR -->
<nav class="navbar shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <!-- Logo Section -->
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-md">
                    <i class="fas fa-building text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Vendor Portal</h1>
                    <p class="text-purple-100 text-xs">Supplier Management System</p>
                </div>
            </div>
            
            <!-- Navigation & User Menu -->
            <div class="flex items-center gap-6">
                <a href="../index.php" class="text-white hover:text-purple-200 transition flex items-center gap-2">
                    <i class="fas fa-home"></i> <span class="hidden sm:inline">Dashboard</span>
                </a>
                <a href="../pages/create_contract_reports.php" class="text-white hover:text-purple-200 transition flex items-center gap-2">
                    <i class="fas fa-file-contract"></i> <span class="hidden sm:inline">Contracts</span>
                </a>
                
                <div class="flex items-center gap-3 pl-6 border-l border-purple-400">
                    <div class="text-right hidden sm:block">
                        <p class="text-white font-semibold text-sm"><?php echo htmlspecialchars($user_name); ?></p>
                        <p class="text-purple-100 text-xs"><?php echo ucfirst(htmlspecialchars($user_role)); ?></p>
                    </div>
                    <button onclick="logout()" class="text-white hover:text-red-300 transition" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- MAIN CONTENT -->
<div class="vendor-portal-container">
    <div class="vendor-portal-main container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 fade-in">
            <!-- TAB NAVIGATION -->
            <div class="border-b border-gray-200 mb-8">
                <div class="flex gap-8 overflow-x-auto">
                    <button onclick="switchTab('vendors')" class="tab-btn active text-gray-700 hover:text-purple-600 transition whitespace-nowrap">
                        <i class="fas fa-users"></i> Vendors
                    </button>
                    <button onclick="switchTab('validation')" class="tab-btn text-gray-700 hover:text-purple-600 transition whitespace-nowrap">
                        <i class="fas fa-check-circle"></i> Validation
                    </button>
                    <button onclick="switchTab('verification')" class="tab-btn text-gray-700 hover:text-purple-600 transition whitespace-nowrap">
                        <i class="fas fa-shield-alt"></i> Verification
                    </button>
                    <button onclick="switchTab('requirements')" class="tab-btn text-gray-700 hover:text-purple-600 transition whitespace-nowrap">
                        <i class="fas fa-tasks"></i> Requirements
                    </button>
                </div>
            </div>

            <!-- VENDORS TAB -->
            <div id="vendors-tab" class="tab-content">
                <div class="mb-6 flex flex-col md:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Search Vendors</label>
                        <input type="text" id="vendorSearch" placeholder="Search by vendor name, email, or type..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select id="vendorStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">All Statuses</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <button onclick="addNewVendor()" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-semibold transition whitespace-nowrap">
                        <i class="fas fa-plus"></i> Add Vendor
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr class="text-left text-sm font-semibold text-gray-700">
                                <th class="px-6 py-3">Vendor Name</th>
                                <th class="px-6 py-3">Contact</th>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Registration Date</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="vendorsTable" class="divide-y divide-gray-200">
                            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="vendorsEmpty" class="hidden text-center py-12">
                    <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No vendors found</p>
                </div>
            </div>

            <!-- VALIDATION TAB -->
            <div id="validation-tab" class="tab-content hidden">
                <div class="mb-6 flex flex-col md:flex-row gap-4 items-end">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Select Vendor</label>
                        <select id="validationVendor" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent min-w-64">
                            <option value="">-- Choose a vendor --</option>
                        </select>
                    </div>
                    <button onclick="loadValidations()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold whitespace-nowrap">
                        <i class="fas fa-refresh"></i> Load
                    </button>
                </div>

                <div id="validationContent" class="grid grid-cols-1 md:grid-cols-2 gap-6"></div>
                <div id="validationEmpty" class="hidden text-center py-12">
                    <i class="fas fa-clipboard-check text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Select a vendor to view validation details</p>
                </div>
            </div>

            <!-- VERIFICATION TAB -->
            <div id="verification-tab" class="tab-content hidden">
                <div class="mb-6 flex flex-col md:flex-row gap-4 items-end">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Vendor</label>
                        <select id="verificationVendor" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent min-w-64">
                            <option value="">-- All Vendors --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Type</label>
                        <select id="verificationType" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">-- All Types --</option>
                            <option value="Document">Document</option>
                            <option value="Financial">Financial</option>
                            <option value="Site Inspection">Site Inspection</option>
                            <option value="Reference Check">Reference Check</option>
                        </select>
                    </div>
                    <button onclick="loadVerifications()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold whitespace-nowrap">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button onclick="addNewVerification()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-semibold whitespace-nowrap">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr class="text-left text-sm font-semibold text-gray-700">
                                <th class="px-6 py-3">Vendor</th>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Notes</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="verificationsTable" class="divide-y divide-gray-200">
                            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="verificationsEmpty" class="hidden text-center py-12">
                    <i class="fas fa-check-double text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No verifications found</p>
                </div>
            </div>

            <!-- REQUIREMENTS TAB -->
            <div id="requirements-tab" class="tab-content hidden">
                <div class="mb-6 flex flex-col md:flex-row gap-4 items-end">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Vendor</label>
                        <select id="requirementsVendor" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent min-w-64">
                            <option value="">-- All Vendors --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Type</label>
                        <select id="requirementsType" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">-- All Types --</option>
                            <option value="Certification">Certification</option>
                            <option value="Documentation">Documentation</option>
                            <option value="Insurance">Insurance</option>
                            <option value="Quality">Quality</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <button onclick="loadRequirements()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold whitespace-nowrap">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button onclick="addNewRequirement()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-semibold whitespace-nowrap">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr class="text-left text-sm font-semibold text-gray-700">
                                <th class="px-6 py-3">Vendor</th>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Description</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Deadline</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="requirementsTable" class="divide-y divide-gray-200">
                            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="requirementsEmpty" class="hidden text-center py-12">
                    <i class="fas fa-list text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No requirements found</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- VENDOR REGISTRATION MODAL -->
<div id="vendorModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 px-6 py-4 flex items-center justify-between sticky top-0">
            <h2 id="modalTitle" class="text-xl font-bold text-white">Register New Vendor</h2>
            <button onclick="closeVendorModal()" class="text-white hover:text-gray-200"><i class="fas fa-times text-lg"></i></button>
        </div>
        <form id="vendorForm" onsubmit="submitVendorForm(event)" class="p-6">
            <input type="hidden" id="vendorId">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Vendor Name *</label>
                    <input type="text" id="vendor_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Vendor Type *</label>
                    <select id="vendor_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">-- Select Type --</option>
                        <option value="Supplier">Supplier</option>
                        <option value="Contractor">Contractor</option>
                        <option value="Service Provider">Service Provider</option>
                        <option value="Distributor">Distributor</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Person *</label>
                    <input type="text" id="contact_person" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                    <input type="email" id="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Phone *</label>
                    <input type="tel" id="phone" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                    <input type="text" id="address" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3 mt-6 pt-4 border-t">
                <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white py-2 rounded-lg font-semibold transition">
                    <i class="fas fa-save"></i> Save Vendor
                </button>
                <button type="button" onclick="closeVendorModal()" class="flex-1 bg-gray-400 hover:bg-gray-500 text-white py-2 rounded-lg font-semibold transition">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- VALIDATION MODAL -->
<div id="validationModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full">
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 px-6 py-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">Vendor Validation</h2>
            <button onclick="closeValidationModal()" class="text-white hover:text-gray-200"><i class="fas fa-times text-lg"></i></button>
        </div>
        <form id="validationForm" onsubmit="submitValidationForm(event)" class="p-6">
            <input type="hidden" id="validationId">
            <input type="hidden" id="validationVendorId">
            
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Business License Review</label>
                    <select id="business_license" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tax Compliance</label>
                    <select id="tax_compliance" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Financial Stability</label>
                    <select id="financial_stability" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3 mt-6 pt-4 border-t">
                <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white py-2 rounded-lg font-semibold transition">
                    <i class="fas fa-check"></i> Update
                </button>
                <button type="button" onclick="closeValidationModal()" class="flex-1 bg-gray-400 hover:bg-gray-500 text-white py-2 rounded-lg font-semibold transition">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- VERIFICATION MODAL -->
<div id="verificationModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full">
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 px-6 py-4 flex items-center justify-between">
            <h2 id="verificationModalTitle" class="text-xl font-bold text-white">Add Verification</h2>
            <button onclick="closeVerificationModal()" class="text-white hover:text-gray-200"><i class="fas fa-times text-lg"></i></button>
        </div>
        <form id="verificationForm" onsubmit="submitVerificationForm(event)" class="p-6">
            <input type="hidden" id="verificationId">
            
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Vendor</label>
                    <select id="verificationVendorSelect" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">-- Select Vendor --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Verification Type *</label>
                    <select id="verification_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">-- Select Type --</option>
                        <option value="Document">Document</option>
                        <option value="Financial">Financial</option>
                        <option value="Site Inspection">Site Inspection</option>
                        <option value="Reference Check">Reference Check</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select id="verification_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Verified">Verified</option>
                        <option value="Failed">Failed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Verification Date</label>
                    <input type="date" id="verification_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Notes</label>
                    <textarea id="verification_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-6 pt-4 border-t">
                <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white py-2 rounded-lg font-semibold transition">
                    <i class="fas fa-save"></i> Save Verification
                </button>
                <button type="button" onclick="closeVerificationModal()" class="flex-1 bg-gray-400 hover:bg-gray-500 text-white py-2 rounded-lg font-semibold transition">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- REQUIREMENTS MODAL -->
<div id="requirementModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full">
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 px-6 py-4 flex items-center justify-between">
            <h2 id="requirementModalTitle" class="text-xl font-bold text-white">Add Requirement</h2>
            <button onclick="closeRequirementModal()" class="text-white hover:text-gray-200"><i class="fas fa-times text-lg"></i></button>
        </div>
        <form id="requirementForm" onsubmit="submitRequirementForm(event)" class="p-6">
            <input type="hidden" id="requirementId">
            
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Vendor</label>
                    <select id="requirementVendorSelect" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">-- Select Vendor --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Requirement Type *</label>
                    <select id="requirement_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">-- Select Type --</option>
                        <option value="Certification">Certification</option>
                        <option value="Documentation">Documentation</option>
                        <option value="Insurance">Insurance</option>
                        <option value="Quality">Quality</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea id="requirement_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select id="requirement_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deadline</label>
                    <input type="date" id="requirement_deadline" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
            </div>

            <div class="flex gap-3 mt-6 pt-4 border-t">
                <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white py-2 rounded-lg font-semibold transition">
                    <i class="fas fa-save"></i> Save Requirement
                </button>
                <button type="button" onclick="closeRequirementModal()" class="flex-1 bg-gray-400 hover:bg-gray-500 text-white py-2 rounded-lg font-semibold transition">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- FOOTER -->
<footer class="bg-gray-800 text-gray-300 mt-8">
    <div class="container mx-auto px-4 py-6 text-center text-sm">
        <p>&copy; 2024 Vendor Portal. All rights reserved.</p>
    </div>
</footer>

<script src="../scripts/vendor_portal.js"></script>

<script>
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = '../api/auth.php?action=logout';
    }
}
</script>

</body>
</html>
