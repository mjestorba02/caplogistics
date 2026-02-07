<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
    <!-- Breadcrumb -->
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <a href="asset_management.php" class="text-indigo-600 hover:underline">Asset Management</a> &gt;
        <span>Maintenance</span>
    </div>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Asset Maintenance</h1>
        <button id="scheduleMaintenanceBtn" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition flex items-center gap-2">
            <i class='bx bx-plus-circle'></i> Schedule Maintenance
        </button>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Total Maintenance</div>
            <div class="text-3xl font-bold text-gray-800" id="totalMaintenance">0</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Scheduled</div>
            <div class="text-3xl font-bold text-yellow-600" id="scheduledCount">0</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Completed</div>
            <div class="text-3xl font-bold text-green-600" id="completedCount">0</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Cancelled</div>
            <div class="text-3xl font-bold text-red-600" id="cancelledCount">0</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="filterStatus" class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">All Status</option>
                    <option value="Scheduled">Scheduled</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select id="filterType" class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">All Types</option>
                    <option value="Preventive">Preventive</option>
                    <option value="Corrective">Corrective</option>
                    <option value="Predictive">Predictive</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input type="date" id="filterDate" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div class="flex items-end">
                <button id="clearFiltersBtn" class="w-full bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition text-sm">
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Maintenance Records Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">Asset ID</th>
                    <th class="px-6 py-3">Asset Name</th>
                    <th class="px-6 py-3">Type</th>
                    <th class="px-6 py-3">Maintenance Date</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Notes</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody id="maintenanceTableBody" class="divide-y">
                <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
            </tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">No maintenance records found</div>
    </div>
</div>

<!-- Schedule Maintenance Modal -->
<div id="scheduleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 my-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Schedule Maintenance</h2>
        <form id="scheduleForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Asset *</label>
                    <select id="scheduleAssetId" class="w-full border rounded px-3 py-2" required>
                        <option value="">Select an asset...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Asset Name</label>
                    <input id="scheduleAssetName" type="text" class="w-full border rounded px-3 py-2 bg-gray-100" readonly />
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Maintenance Type *</label>
                    <select id="scheduleType" class="w-full border rounded px-3 py-2" required>
                        <option value="Preventive">Preventive</option>
                        <option value="Corrective">Corrective</option>
                        <option value="Predictive">Predictive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Maintenance Date</label>
                    <input id="scheduleDate" type="date" class="w-full border rounded px-3 py-2" />
                </div>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">Notes</label>
                <textarea id="scheduleNotes" rows="3" class="w-full border rounded px-3 py-2" placeholder="Add any notes about the maintenance..."></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeScheduleModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Schedule</button>
            </div>
        </form>
    </div>
</div>

<!-- Update Status Modal -->
<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Update Maintenance Status</h2>
        <form id="updateForm" class="space-y-4">
            <input type="hidden" id="updateId" />
            <div>
                <label class="block text-gray-700 font-medium mb-1">Status *</label>
                <select id="updateStatus" class="w-full border rounded px-3 py-2" required>
                    <option value="Scheduled">Scheduled</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-1">Notes</label>
                <textarea id="updateNotes" rows="3" class="w-full border rounded px-3 py-2"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeUpdateModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Update</button>
            </div>
        </form>
    </div>
</div>

HTML;

adminLayout($children);
?>

<script>
let allRecords = [];

document.addEventListener('DOMContentLoaded', () => {
    // Load assets for dropdown
    loadAssets();
    
    // Event listeners
    document.getElementById('scheduleMaintenanceBtn').addEventListener('click', () => {
        document.getElementById('scheduleModal').classList.remove('hidden');
    });

    document.getElementById('closeScheduleModal').addEventListener('click', () => {
        document.getElementById('scheduleModal').classList.add('hidden');
    });

    document.getElementById('closeUpdateModal').addEventListener('click', () => {
        document.getElementById('updateModal').classList.add('hidden');
    });

    // Asset selection handler
    document.getElementById('scheduleAssetId').addEventListener('change', (e) => {
        const assetId = e.target.value;
        const selectedOption = e.target.options[e.target.selectedIndex];
        const assetName = selectedOption.dataset.assetName || '';
        document.getElementById('scheduleAssetName').value = assetName;
    });

    document.getElementById('scheduleForm').addEventListener('submit', handleScheduleSubmit);
    document.getElementById('updateForm').addEventListener('submit', handleUpdateSubmit);

    document.getElementById('filterStatus').addEventListener('change', applyFilters);
    document.getElementById('filterType').addEventListener('change', applyFilters);
    document.getElementById('filterDate').addEventListener('change', applyFilters);

    document.getElementById('clearFiltersBtn').addEventListener('click', () => {
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterType').value = '';
        document.getElementById('filterDate').value = '';
        displayRecords(allRecords);
    });

    // Load initial data
    loadMaintenanceRecords();
});

function loadMaintenanceRecords() {
    fetch('../api/asset_maintenance.php?action=all')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                allRecords = data.records || [];
                displayRecords(allRecords);
                updateStatistics();
            }
        })
        .catch(error => {
            console.error('Error loading maintenance records:', error);
            showToast('Error loading maintenance records', 'error');
        });
}

function loadAssets() {
    fetch('../api/asset_management.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.assets) {
                const select = document.getElementById('scheduleAssetId');
                select.innerHTML = '<option value="">Select an asset...</option>';
                
                data.assets.forEach(asset => {
                    const option = document.createElement('option');
                    option.value = asset.id;
                    option.textContent = `${asset.item_number} - ${asset.item_name}`;
                    option.dataset.assetName = asset.item_name;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading assets:', error);
        });
}

function displayRecords(records) {
    const tbody = document.getElementById('maintenanceTableBody');
    const emptyState = document.getElementById('emptyState');

    if (records.length === 0) {
        tbody.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }

    emptyState.classList.add('hidden');
    tbody.innerHTML = records.map(record => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-3 font-semibold">${record.asset_id}</td>
            <td class="px-6 py-3">${record.asset_name || '-'}</td>
            <td class="px-6 py-3">
                <span class="px-2 py-1 rounded text-xs font-semibold ${
                    record.type === 'Corrective' ? 'bg-yellow-100 text-yellow-800' :
                    record.type === 'Predictive' ? 'bg-purple-100 text-purple-800' :
                    'bg-blue-100 text-blue-800'
                }">
                    ${record.type}
                </span>
            </td>
            <td class="px-6 py-3">${record.maintenance_date || '-'}</td>
            <td class="px-6 py-3">
                <span class="px-2 py-1 rounded text-xs font-semibold ${
                    record.status === 'Completed' ? 'bg-green-100 text-green-800' :
                    record.status === 'Cancelled' ? 'bg-red-100 text-red-800' :
                    'bg-yellow-100 text-yellow-800'
                }">
                    ${record.status}
                </span>
            </td>
            <td class="px-6 py-3 text-sm text-gray-600">${record.notes ? record.notes.substring(0, 40) + '...' : '-'}</td>
            <td class="px-6 py-3 flex gap-2">
                <button onclick="openUpdateModal(${record.id}, '${record.status}', \`${(record.notes || '').replace(/`/g, '\\`')}\`)" class="text-indigo-600 hover:text-indigo-900 font-semibold text-sm">Edit</button>
                <button onclick="deleteRecord(${record.id})" class="text-red-600 hover:text-red-900 font-semibold text-sm">Delete</button>
            </td>
        </tr>
    `).join('');
}

function updateStatistics() {
    const total = allRecords.length;
    const scheduled = allRecords.filter(r => r.status === 'Scheduled').length;
    const completed = allRecords.filter(r => r.status === 'Completed').length;
    const cancelled = allRecords.filter(r => r.status === 'Cancelled').length;

    document.getElementById('totalMaintenance').textContent = total;
    document.getElementById('scheduledCount').textContent = scheduled;
    document.getElementById('completedCount').textContent = completed;
    document.getElementById('cancelledCount').textContent = cancelled;
}

function applyFilters() {
    const statusFilter = document.getElementById('filterStatus').value;
    const typeFilter = document.getElementById('filterType').value;
    const dateFilter = document.getElementById('filterDate').value;

    let filtered = allRecords.filter(record => {
        if (statusFilter && record.status !== statusFilter) return false;
        if (typeFilter && record.type !== typeFilter) return false;
        if (dateFilter && record.maintenance_date !== dateFilter) return false;
        return true;
    });

    displayRecords(filtered);
}

function handleScheduleSubmit(e) {
    e.preventDefault();

    const data = {
        asset_id: document.getElementById('scheduleAssetId').value,
        asset_name: document.getElementById('scheduleAssetName').value,
        maintenance_type: document.getElementById('scheduleType').value,
        maintenance_date: document.getElementById('scheduleDate').value || new Date().toISOString().split('T')[0],
        notes: document.getElementById('scheduleNotes').value
    };

    fetch('../api/asset_maintenance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('Maintenance scheduled successfully', 'success');
            document.getElementById('scheduleModal').classList.add('hidden');
            document.getElementById('scheduleForm').reset();
            loadMaintenanceRecords();
        } else {
            showToast(data.message || 'Error scheduling maintenance', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error scheduling maintenance', 'error');
    });
}

function openUpdateModal(id, status, notes) {
    document.getElementById('updateId').value = id;
    document.getElementById('updateStatus').value = status;
    document.getElementById('updateNotes').value = notes;
    document.getElementById('updateModal').classList.remove('hidden');
}

function handleUpdateSubmit(e) {
    e.preventDefault();

    const data = {
        id: document.getElementById('updateId').value,
        status: document.getElementById('updateStatus').value,
        notes: document.getElementById('updateNotes').value
    };

    fetch('../api/asset_maintenance.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('Maintenance record updated', 'success');
            document.getElementById('updateModal').classList.add('hidden');
            loadMaintenanceRecords();
        } else {
            showToast(data.message || 'Error updating record', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating record', 'error');
    });
}

function deleteRecord(id) {
    if (!confirm('Delete this maintenance record?')) return;

    const data = { id: id };
    fetch('../api/asset_maintenance.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
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

window.openUpdateModal = openUpdateModal;
window.deleteRecord = deleteRecord;
</script>
