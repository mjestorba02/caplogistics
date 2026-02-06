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
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
            <option value="Cancelled">Cancelled</option>
        </select>

        <select id="filterType" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
            <option value="">All Types</option>
            <option value="Preventive">Preventive</option>
            <option value="Corrective">Corrective</option>
            <option value="Emergency">Emergency</option>
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
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Item Number</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Type</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Scheduled Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Technician</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Cost</th>
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
                    <input type="number" id="assetId" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Item Number</label>
                    <input type="text" id="itemNumber" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Maintenance Type *</label>
                    <select id="maintenanceType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600" required>
                        <option value="Preventive">Preventive</option>
                        <option value="Corrective">Corrective</option>
                        <option value="Emergency">Emergency</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Scheduled Date</label>
                    <input type="date" id="scheduledDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Technician Name</label>
                    <input type="text" id="technicianName" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Cost</label>
                    <input type="number" id="cost" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Description *</label>
                <textarea id="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600" required></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Notes</label>
                <textarea id="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"></textarea>
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
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Completed Date</label>
                <input type="date" id="completedDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Technician Name</label>
                <input type="text" id="updateTechnicianName" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Cost</label>
                <input type="number" id="updateCost" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600">
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
        tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">No maintenance records found.</td></tr>';
        return;
    }

    tbody.innerHTML = records.map(record => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm text-gray-900">${record.asset_id}</td>
            <td class="px-6 py-4 text-sm text-gray-900">${record.item_number || '-'}</td>
            <td class="px-6 py-4 text-sm">
                <span class="px-2 py-1 rounded-full text-xs font-semibold ${
                    record.maintenance_type === 'Emergency' ? 'bg-red-100 text-red-800' :
                    record.maintenance_type === 'Corrective' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-blue-100 text-blue-800'
                }">
                    ${record.maintenance_type}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">${record.scheduled_date || '-'}</td>
            <td class="px-6 py-4 text-sm">
                <span class="px-2 py-1 rounded-full text-xs font-semibold ${
                    record.status === 'Completed' ? 'bg-green-100 text-green-800' :
                    record.status === 'In Progress' ? 'bg-blue-100 text-blue-800' :
                    record.status === 'Cancelled' ? 'bg-gray-100 text-gray-800' :
                    'bg-yellow-100 text-yellow-800'
                }">
                    ${record.status}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">${record.technician_name || '-'}</td>
            <td class="px-6 py-4 text-sm text-gray-900">${record.cost ? '$' + parseFloat(record.cost).toFixed(2) : '-'}</td>
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
document.getElementById('scheduleMaintenanceBtn').addEventListener('click', () => {
    document.getElementById('scheduleMaintenanceModal').classList.remove('hidden');
});

// Close modals
document.querySelectorAll('.closeModal').forEach(btn => {
    btn.addEventListener('click', function() {
        this.closest('.fixed').classList.add('hidden');
    });
});

// Submit maintenance
document.getElementById('submitMaintenanceBtn').addEventListener('click', () => {
    const data = {
        asset_id: parseInt(document.getElementById('assetId').value),
        item_number: document.getElementById('itemNumber').value,
        maintenance_type: document.getElementById('maintenanceType').value,
        description: document.getElementById('description').value,
        scheduled_date: document.getElementById('scheduledDate').value || null,
        technician_name: document.getElementById('technicianName').value,
        cost: parseFloat(document.getElementById('cost').value) || 0,
        notes: document.getElementById('notes').value
    };

    if (!data.asset_id || !data.description) {
        alert('Asset ID and Description are required');
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
            document.getElementById('itemNumber').value = '';
            document.getElementById('description').value = '';
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

// Open edit modal
function openEditModal(id) {
    const record = allRecords.find(r => r.id === id);
    if (!record) return;

    currentEditingId = id;
    document.getElementById('updateStatus').value = record.status;
    document.getElementById('completedDate').value = record.completed_date || '';
    document.getElementById('updateTechnicianName').value = record.technician_name || '';
    document.getElementById('updateCost').value = record.cost || '';
    document.getElementById('updateNotes').value = record.notes || '';

    document.getElementById('updateStatusModal').classList.remove('hidden');
}

// Update status
document.getElementById('updateStatusBtn').addEventListener('click', () => {
    const data = {
        id: currentEditingId,
        status: document.getElementById('updateStatus').value,
        completed_date: document.getElementById('completedDate').value || null,
        technician_name: document.getElementById('updateTechnicianName').value,
        cost: parseFloat(document.getElementById('updateCost').value) || null,
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

document.getElementById('filterStatus').addEventListener('change', applyFilters);
document.getElementById('filterType').addEventListener('change', applyFilters);
document.getElementById('filterDate').addEventListener('change', applyFilters);

document.getElementById('clearFiltersBtn').addEventListener('click', () => {
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterType').value = '';
    document.getElementById('filterDate').value = '';
    displayRecords(allRecords);
});

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
loadMaintenanceRecords();
</script>
