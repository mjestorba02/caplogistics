<?php
session_start();
require_once __DIR__ . '/../api/db.php';

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Asset Maintenance</h1>
        <button id="scheduleMaintenanceBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            <i class='bx bx-plus-circle'></i> Schedule Maintenance
        </button>
    </div>

    <!-- Filters -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <select id="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
            <option value="">All Status</option>
            <option value="Scheduled">Scheduled</option>
            <option value="Completed">Completed</option>
            <option value="Cancelled">Cancelled</option>
            <option value="Archived">Archived</option>
        </select>

        <select id="filterType" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
            <option value="">All Types</option>
            <option value="Preventive">Preventive</option>
            <option value="Corrective">Corrective</option>
            <option value="Predictive">Predictive</option>
        </select>

        <input type="date" id="filterDate" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600" placeholder="Filter by date">

        <button id="clearFiltersBtn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
            Clear Filters
        </button>
    </div>

    <!-- Maintenance Records Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Asset ID</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Asset Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Type</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Maintenance Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Notes</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody id="maintenanceTableBody" class="divide-y divide-gray-200">
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Schedule Maintenance Modal -->
<div id="scheduleMaintenanceModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-screen overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Schedule Maintenance</h2>
        </div>

        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Asset ID *</label>
                    <input type="text" id="assetId" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Asset Name *</label>
                    <input type="text" id="assetName" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Maintenance Type *</label>
                    <select id="maintenanceType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600" required>
                        <option value="Preventive">Preventive</option>
                        <option value="Corrective">Corrective</option>
                        <option value="Predictive">Predictive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Maintenance Date</label>
                    <input type="date" id="maintenanceDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Notes</label>
                <textarea id="notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"></textarea>
            </div>
        </div>

        <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
            <button type="button" class="closeModal px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">Cancel</button>
            <button id="submitMaintenanceBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Schedule Maintenance</button>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div id="updateStatusModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Update Maintenance Status</h2>
        </div>

        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status *</label>
                <select id="updateStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600" required>
                    <option value="Scheduled">Scheduled</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                    <option value="Archived">Archived</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Notes</label>
                <textarea id="updateNotes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"></textarea>
            </div>
        </div>

        <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
            <button type="button" class="closeModal px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">Cancel</button>
            <button id="updateStatusBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Update Status</button>
        </div>
    </div>
</div>

<?php
$children = ob_get_clean();
require_once __DIR__ . '/../layout/adminLayout.php';
?>

<script>
let currentEditingId = null;
let allRecords = [];

function initializeEventListeners() {
    // Open schedule modal
    const scheduleBtn = document.getElementById('scheduleMaintenanceBtn');
    if (scheduleBtn) {
        scheduleBtn.addEventListener('click', () => {
            document.getElementById('scheduleMaintenanceModal').classList.remove('hidden');
        });
    }

    // Close modals
    document.querySelectorAll('.closeModal').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.fixed').classList.add('hidden');
        });
    });

    // Submit maintenance
    const submitBtn = document.getElementById('submitMaintenanceBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', () => {
            const data = {
                asset_id: document.getElementById('assetId').value,
                asset_name: document.getElementById('assetName').value,
                maintenance_type: document.getElementById('maintenanceType').value,
                maintenance_date: document.getElementById('maintenanceDate').value || new Date().toISOString().split('T')[0],
                notes: document.getElementById('notes').value
            };

            if (!data.asset_id || !data.asset_name) {
                alert('Asset ID and Asset Name are required');
                return;
            }

            fetch('/newcaplog1/api/asset_maintenance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('Maintenance scheduled successfully', 'success');
                    document.getElementById('scheduleMaintenanceModal').classList.add('hidden');
                    document.getElementById('assetId').value = '';
                    document.getElementById('assetName').value = '';
                    document.getElementById('notes').value = '';
                    loadMaintenanceRecords();
                } else {
                    showToast(data.message || 'Error scheduling maintenance', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error scheduling maintenance', 'error');
            });
        });
    }

    // Update status
    const updateStatusBtn = document.getElementById('updateStatusBtn');
    if (updateStatusBtn) {
        updateStatusBtn.addEventListener('click', () => {
            const data = {
                id: currentEditingId,
                status: document.getElementById('updateStatus').value,
                notes: document.getElementById('updateNotes').value
            };

            fetch('/newcaplog1/api/asset_maintenance.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('Maintenance record updated', 'success');
                    document.getElementById('updateStatusModal').classList.add('hidden');
                    loadMaintenanceRecords();
                } else {
                    showToast(data.message || 'Error updating record', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating record', 'error');
            });
        });
    }

    // Filter records
    const filterStatus = document.getElementById('filterStatus');
    const filterType = document.getElementById('filterType');
    const filterDate = document.getElementById('filterDate');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');

    if (filterStatus) filterStatus.addEventListener('change', applyFilters);
    if (filterType) filterType.addEventListener('change', applyFilters);
    if (filterDate) filterDate.addEventListener('change', applyFilters);
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => {
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterType').value = '';
            document.getElementById('filterDate').value = '';
            displayRecords(allRecords);
        });
    }
}

// Load maintenance records
function loadMaintenanceRecords() {
    fetch('/newcaplog1/api/asset_maintenance.php?action=all', {
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            allRecords = data.records;
            displayRecords(allRecords);
        }
    })
    .catch(error => console.error('Error loading records:', error));
}

// Display records in table
function displayRecords(records) {
    const tbody = document.getElementById('maintenanceTableBody');
    
    if (records.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No maintenance records found.</td></tr>';
        return;
    }

    tbody.innerHTML = records.map(record => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm text-gray-900">${record.asset_id}</td>
            <td class="px-6 py-4 text-sm text-gray-900">${record.asset_name || '-'}</td>
            <td class="px-6 py-4 text-sm">
                <span class="px-2 py-1 rounded-full text-xs font-semibold ${
                    record.type === 'Corrective' ? 'bg-yellow-100 text-yellow-800' :
                    record.type === 'Predictive' ? 'bg-purple-100 text-purple-800' :
                    'bg-blue-100 text-blue-800'
                }">
                    ${record.type}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">${record.maintenance_date || '-'}</td>
            <td class="px-6 py-4 text-sm">
                <span class="px-2 py-1 rounded-full text-xs font-semibold ${
                    record.status === 'Completed' ? 'bg-green-100 text-green-800' :
                    record.status === 'Cancelled' ? 'bg-gray-100 text-gray-800' :
                    record.status === 'Archived' ? 'bg-slate-100 text-slate-800' :
                    'bg-yellow-100 text-yellow-800'
                }">
                    ${record.status}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">${record.notes ? record.notes.substring(0, 30) + '...' : '-'}</td>
            <td class="px-6 py-4 text-center text-sm">
                <button class="editBtn text-indigo-600 hover:text-indigo-900 font-semibold" data-id="${record.id}">Edit</button>
                <button class="deleteBtn text-red-600 hover:text-red-900 font-semibold ml-3" data-id="${record.id}">Delete</button>
            </td>
        </tr>
    `).join('');

    // Attach event listeners
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => openEditModal(parseInt(btn.dataset.id)));
    });

    document.querySelectorAll('.deleteBtn').forEach(btn => {
        btn.addEventListener('click', () => deleteRecord(parseInt(btn.dataset.id)));
    });
}

// Open schedule modal
function openEditModal(id) {
    const record = allRecords.find(r => r.id === id);
    if (!record) return;

    currentEditingId = id;
    document.getElementById('updateStatus').value = record.status || 'Scheduled';
    document.getElementById('updateNotes').value = record.notes || '';

    document.getElementById('updateStatusModal').classList.remove('hidden');
}

// Delete record
function deleteRecord(id) {
    if (!confirm('Are you sure you want to delete this maintenance record?')) return;

    fetch('/newcaplog1/api/asset_maintenance.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('Maintenance record deleted', 'success');
            loadMaintenanceRecords();
        } else {
            showToast(data.message || 'Error deleting record', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error deleting record', 'error');
    });
}

// Filter records
function applyFilters() {
    const statusFilter = document.getElementById('filterStatus').value;
    const typeFilter = document.getElementById('filterType').value;
    const dateFilter = document.getElementById('filterDate').value;

    let filtered = allRecords.filter(record => {
        if (statusFilter && record.status !== statusFilter) return false;
        if (typeFilter && record.maintenance_type !== typeFilter) return false;
        if (dateFilter && record.scheduled_date !== dateFilter) return false;
        return true;
    });

    displayRecords(filtered);
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg text-white ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
        'bg-blue-500'
    } shadow-lg z-50`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Load records on page load
document.addEventListener('DOMContentLoaded', () => {
    initializeEventListeners();
    loadMaintenanceRecords();
});
</script>
