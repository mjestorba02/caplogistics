// Manage Asset Requests - Admin Page JavaScript

let currentFilter = 'Pending Approval';
let currentRequestId = null;

document.addEventListener('DOMContentLoaded', function() {
    loadRequests();
});

/**
 * Load all asset requests based on filter
 */
function loadRequests() {
    const tableBody = document.getElementById('requestsTableBody');
    tableBody.innerHTML = '<tr><td colspan="8" class="px-4 py-4 text-center text-gray-500">Loading...</td></tr>';

    fetch('../api/asset_requests_admin.php?action=all&status=' + encodeURIComponent(currentFilter))
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                displayRequests(data.requests);
            } else {
                showToast(data.message || 'Failed to load requests', 'error');
                tableBody.innerHTML = '<tr><td colspan="8" class="px-4 py-4 text-center text-red-500">Failed to load requests</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error loading requests', 'error');
            tableBody.innerHTML = '<tr><td colspan="8" class="px-4 py-4 text-center text-red-500">Error loading requests</td></tr>';
        });
}

/**
 * Display requests in table
 */
function displayRequests(requests) {
    const tableBody = document.getElementById('requestsTableBody');
    
    if (!requests || requests.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="8" class="px-4 py-4 text-center text-gray-500">No requests found</td></tr>';
        return;
    }

    tableBody.innerHTML = requests.map(req => `
        <tr class="border-b border-gray-200 hover:bg-gray-50">
            <td class="px-4 py-3 text-sm font-semibold text-blue-600 cursor-pointer" onclick="viewDetails(${req.id})">${req.request_id}</td>
            <td class="px-4 py-3 text-sm text-gray-700">${req.requester_name}</td>
            <td class="px-4 py-3 text-sm text-gray-700">${req.requester_department}</td>
            <td class="px-4 py-3 text-sm text-gray-700">${req.total_items} item(s)</td>
            <td class="px-4 py-3 text-sm">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getPriorityClass(req.priority)}">
                    ${req.priority}
                </span>
            </td>
            <td class="px-4 py-3 text-sm">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getStatusClass(req.status)}">
                    ${req.status}
                </span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-600">${formatDate(req.request_date)}</td>
            <td class="px-4 py-3 text-sm">
                <div class="flex gap-2">
                    <button onclick="viewDetails(${req.id})" class="text-blue-600 hover:text-blue-800" title="View Details">
                        <i class='bx bx-show'></i>
                    </button>
                    ${req.status === 'Pending Approval' ? `
                        <button onclick="openApprovalModal(${req.id})" class="text-green-600 hover:text-green-800" title="Approve">
                            <i class='bx bx-check-circle'></i>
                        </button>
                        <button onclick="openRejectionModal(${req.id})" class="text-red-600 hover:text-red-800" title="Reject">
                            <i class='bx bx-x-circle'></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

/**
 * View request details in modal
 */
function viewDetails(requestId) {
    currentRequestId = requestId;
    const modal = document.getElementById('detailsModal');
    const modalContent = document.getElementById('modalContent');
    const modalActions = document.getElementById('modalActions');
    
    modal.classList.remove('hidden');
    modalContent.innerHTML = '<p class="text-center text-gray-500">Loading...</p>';

    fetch('../api/asset_requests_admin.php?action=details&id=' + requestId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const req = data.request;
                const items = data.items || [];

                let itemsHtml = '<div class="space-y-3">';
                if (items.length > 0) {
                    itemsHtml += items.map((item, idx) => `
                        <div class="border-l-4 border-blue-500 pl-3 pb-3">
                            <p class="font-semibold text-gray-800">${item.asset_description}</p>
                            <p class="text-sm text-gray-600">Quantity: <span class="font-semibold">${item.quantity}</span></p>
                            <p class="text-sm text-gray-600">Urgency: <span class="font-semibold">${item.urgency}</span></p>
                            <p class="text-sm text-gray-600">Est. Cost: <span class="font-semibold">$${parseFloat(item.estimated_cost || 0).toFixed(2)}</span></p>
                            ${item.notes ? `<p class="text-sm text-gray-600 italic">${item.notes}</p>` : ''}
                        </div>
                    `).join('');
                } else {
                    itemsHtml += '<p class="text-gray-500">No items found</p>';
                }
                itemsHtml += '</div>';

                modalContent.innerHTML = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Requester</p>
                                <p class="text-lg font-semibold text-gray-800">${req.requester_name}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Department</p>
                                <p class="text-lg font-semibold text-gray-800">${req.requester_department}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Priority</p>
                                <p class="text-lg font-semibold ${getPriorityClass(req.priority)}">${req.priority}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Status</p>
                                <p class="text-lg font-semibold ${getStatusClass(req.status)}">${req.status}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Date</p>
                                <p class="text-lg font-semibold text-gray-800">${formatDate(req.request_date)}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Total Items</p>
                                <p class="text-lg font-semibold text-gray-800">${req.total_items}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Items Requested</p>
                            ${itemsHtml}
                        </div>
                        ${req.notes ? `
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Notes</p>
                                <p class="text-gray-700">${req.notes}</p>
                            </div>
                        ` : ''}
                    </div>
                `;

                // Set action buttons based on status
                if (req.status === 'Pending Approval') {
                    modalActions.innerHTML = `
                        <button onclick="openApprovalModal(${req.id})" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <i class='bx bx-check mr-1'></i>Approve
                        </button>
                        <button onclick="openRejectionModal(${req.id})" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            <i class='bx bx-x mr-1'></i>Reject
                        </button>
                    `;
                } else {
                    modalActions.innerHTML = '';
                }
            } else {
                showToast(data.message || 'Failed to load details', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalContent.innerHTML = '<p class="text-red-500">Error loading details</p>';
        });
}

/**
 * Open approval modal
 */
function openApprovalModal(requestId) {
    currentRequestId = requestId;
    document.getElementById('approvalNotes').value = '';
    document.getElementById('approvalModal').classList.remove('hidden');
}

/**
 * Confirm approval
 */
document.addEventListener('DOMContentLoaded', function() {
    const confirmApproveBtn = document.getElementById('confirmApproveBtn');
    if (confirmApproveBtn) {
        confirmApproveBtn.onclick = function() {
            approveRequest();
        };
    }
});

function approveRequest() {
    if (!currentRequestId) return;

    const notes = document.getElementById('approvalNotes').value;

    fetch('../api/asset_requests_admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'approve',
            id: currentRequestId,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('Request approved successfully', 'success');
            document.getElementById('approvalModal').classList.add('hidden');
            document.getElementById('detailsModal').classList.add('hidden');
            loadRequests();
        } else {
            showToast(data.message || 'Failed to approve request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error approving request', 'error');
    });
}

/**
 * Open rejection modal
 */
function openRejectionModal(requestId) {
    currentRequestId = requestId;
    document.getElementById('rejectionReason').value = '';
    document.getElementById('rejectionModal').classList.remove('hidden');
}

/**
 * Confirm rejection
 */
document.addEventListener('DOMContentLoaded', function() {
    const confirmRejectBtn = document.getElementById('confirmRejectBtn');
    if (confirmRejectBtn) {
        confirmRejectBtn.onclick = function() {
            rejectRequest();
        };
    }
});

function rejectRequest() {
    if (!currentRequestId) return;

    const reason = document.getElementById('rejectionReason').value;

    if (!reason.trim()) {
        showToast('Rejection reason is required', 'warning');
        return;
    }

    fetch('../api/asset_requests_admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'reject',
            id: currentRequestId,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('Request rejected', 'success');
            document.getElementById('rejectionModal').classList.add('hidden');
            document.getElementById('detailsModal').classList.add('hidden');
            loadRequests();
        } else {
            showToast(data.message || 'Failed to reject request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error rejecting request', 'error');
    });
}

/**
 * Get status badge class
 */
function getStatusClass(status) {
    const classes = {
        'Pending Approval': 'bg-yellow-100 text-yellow-800',
        'Approved': 'bg-green-100 text-green-800',
        'Rejected': 'bg-red-100 text-red-800',
        'In Process': 'bg-blue-100 text-blue-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

/**
 * Get priority badge class
 */
function getPriorityClass(priority) {
    const classes = {
        'Low': 'bg-blue-100 text-blue-800',
        'Medium': 'bg-yellow-100 text-yellow-800',
        'High': 'bg-orange-100 text-orange-800',
        'Urgent': 'bg-red-100 text-red-800'
    };
    return classes[priority] || 'bg-gray-100 text-gray-800';
}

/**
 * Format date
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const colors = {
        success: '#4CAF50',
        error: '#f44336',
        warning: '#ff9800',
        info: '#2196F3'
    };

    Toastify({
        text: message,
        duration: 3000,
        gravity: 'top',
        position: 'right',
        backgroundColor: colors[type] || colors.info,
        close: true
    }).showToast();
}
