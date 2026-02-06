// Request Asset Module JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize
    loadMyRequests();
    loadStatusCounts();
    attachEventListeners();
});

// ============================================================================
// EVENT LISTENERS
// ============================================================================

function attachEventListeners() {
    // Tab navigation
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            switchTab(tabName);
        });
    });

    // Form submission
    document.getElementById('requestAssetForm').addEventListener('submit', submitRequest);

    // Add more items button
    document.getElementById('addMoreItemsBtn').addEventListener('click', addItemRow);

    // Remove item buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            e.preventDefault();
            const itemRow = e.target.closest('.item-row');
            itemRow.remove();
            updateItemNumbers();
        }
    });

    // Filter listeners
    document.getElementById('searchFilter').addEventListener('input', filterRequests);
    document.getElementById('statusFilter').addEventListener('change', filterRequests);
    document.getElementById('priorityFilter').addEventListener('change', filterRequests);
}

// ============================================================================
// TAB SWITCHING
// ============================================================================

function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });

    // Remove active state from all buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
        btn.classList.remove('border-indigo-600', 'text-indigo-600');
        btn.classList.add('border-transparent', 'text-gray-600');
    });

    // Show selected tab
    document.getElementById(tabName).classList.remove('hidden');

    // Mark button as active
    const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
    activeButton.classList.add('active');
    activeButton.classList.remove('border-transparent', 'text-gray-600');
    activeButton.classList.add('border-indigo-600', 'text-indigo-600');

    // Reload data based on tab
    if (tabName === 'view-tab') {
        loadMyRequests();
    } else if (tabName === 'status-tab') {
        loadStatusCounts();
    }
}

// ============================================================================
// FORM MANAGEMENT
// ============================================================================

function addItemRow() {
    const itemsContainer = document.getElementById('itemsContainer');
    const itemCount = itemsContainer.querySelectorAll('.item-row').length + 1;

    const newItemRow = document.createElement('div');
    newItemRow.className = 'item-row border border-gray-200 p-4 rounded-lg bg-white';
    newItemRow.innerHTML = `
        <div class="flex justify-between items-center mb-4">
            <span class="text-sm font-semibold text-gray-700">Item #${itemCount}</span>
            <button type="button" class="remove-item text-red-500 hover:text-red-700">
                <i class='bx bx-trash text-lg'></i>
            </button>
        </div>

        <div class="space-y-3">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Asset Description *</label>
                <input type="text" class="asset-description w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g., Laptop, Office Chair, Software License" required>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Quantity *</label>
                    <input type="number" class="quantity w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" min="1" value="1" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Urgency *</label>
                    <select class="urgency w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Estimated Cost (Optional)</label>
                <input type="number" class="estimated-cost w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" step="0.01" min="0" placeholder="0.00">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Item Notes (Optional)</label>
                <input type="text" class="item-notes w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Specific requirements for this item...">
            </div>
        </div>
    `;

    itemsContainer.appendChild(newItemRow);
    updateItemNumbers();
}

function updateItemNumbers() {
    const itemRows = document.querySelectorAll('.item-row');
    itemRows.forEach((row, index) => {
        const label = row.querySelector('span:first-of-type');
        if (label) {
            label.textContent = `Item #${index + 1}`;
        }
        // Hide remove button if only one item
        const removeBtn = row.querySelector('.remove-item');
        if (removeBtn) {
            removeBtn.style.display = itemRows.length > 1 ? 'block' : 'none';
        }
    });
}

function submitRequest(e) {
    e.preventDefault();

    const form = e.target;
    const priority = form.querySelector('#priority').value;
    const department = form.querySelector('input[name="department"]').value;
    const notes = form.querySelector('#notes').value;

    // Collect items
    const items = [];
    document.querySelectorAll('.item-row').forEach((row, index) => {
        items.push({
            asset_description: row.querySelector('.asset-description').value,
            quantity: parseInt(row.querySelector('.quantity').value),
            urgency: row.querySelector('.urgency').value,
            estimated_cost: parseFloat(row.querySelector('.estimated-cost').value) || 0,
            notes: row.querySelector('.item-notes').value
        });
    });

    if (items.length === 0) {
        showToast('Please add at least one item', 'error');
        return;
    }

    // Send to API
    const payload = {
        priority,
        department,
        notes,
        items
    };

    fetch('../api/asset_requests.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('Request submitted successfully! Request ID: ' + data.request_id, 'success');
            form.reset();
            document.getElementById('itemsContainer').innerHTML = `
                <div class="item-row border border-gray-200 p-4 rounded-lg bg-white">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm font-semibold text-gray-700">Item #1</span>
                        <button type="button" class="remove-item text-red-500 hover:text-red-700 hidden">
                            <i class='bx bx-trash text-lg'></i>
                        </button>
                    </div>
                    <!-- item content here -->
                </div>
            `;
            updateItemNumbers();
            // Load requests in view tab
            setTimeout(() => {
                switchTab('view-tab');
                loadMyRequests();
            }, 1000);
        } else {
            showToast(data.message || 'Error submitting request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error submitting request', 'error');
    });
}

// ============================================================================
// LOAD AND DISPLAY REQUESTS
// ============================================================================

function loadMyRequests() {
    fetch('../api/asset_requests.php?action=my_requests')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayMyRequests(data.requests);
            } else {
                showToast('Error loading requests', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error loading requests', 'error');
        });
}

function displayMyRequests(requests) {
    const tbody = document.getElementById('requestsTableBody');
    
    if (requests.length === 0) {
        tbody.innerHTML = `
            <tr class="border-b border-gray-200">
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <i class='bx bx-inbox text-4xl mb-2'></i>
                    <p>No requests found</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = requests.map(req => `
        <tr class="border-b border-gray-200 hover:bg-gray-50">
            <td class="px-6 py-4 font-semibold text-blue-600">${req.request_id}</td>
            <td class="px-6 py-4 text-sm">${req.total_items} item(s)</td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getStatusBadgeClass(req.status)}">
                    ${req.status}
                </span>
            </td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getPriorityBadgeClass(req.priority)}">
                    ${req.priority}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">${formatDate(req.request_date)}</td>
            <td class="px-6 py-4">
                <button onclick="viewRequestDetails(${req.id})" class="text-blue-600 hover:text-blue-800 font-semibold mr-2">
                    <i class='bx bx-show'></i>
                </button>
                ${req.status === 'Pending Approval' ? `
                    <button onclick="editRequest(${req.id})" class="text-green-600 hover:text-green-800 mr-2">
                        <i class='bx bx-edit'></i>
                    </button>
                    <button onclick="deleteRequest(${req.id})" class="text-red-600 hover:text-red-800">
                        <i class='bx bx-trash'></i>
                    </button>
                ` : ''}
            </td>
        </tr>
    `).join('');
}

function filterRequests() {
    const searchText = document.getElementById('searchFilter').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const priorityFilter = document.getElementById('priorityFilter').value;

    const rows = document.querySelectorAll('#requestsTableBody tr');
    rows.forEach(row => {
        if (row.cells.length < 6) return; // Skip empty rows

        const requestId = row.cells[0].textContent.toLowerCase();
        const status = row.cells[2].textContent.trim();
        const priority = row.cells[3].textContent.trim();

        const matchesSearch = requestId.includes(searchText);
        const matchesStatus = !statusFilter || status.includes(statusFilter);
        const matchesPriority = !priorityFilter || priority.includes(priorityFilter);

        row.style.display = matchesSearch && matchesStatus && matchesPriority ? '' : 'none';
    });
}

// ============================================================================
// STATUS TRACKING
// ============================================================================

function loadStatusCounts() {
    fetch('../api/asset_requests.php?action=status_summary')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayStatusCounts(data);
            }
        })
        .catch(error => console.error('Error:', error));
}

function displayStatusCounts(data) {
    document.getElementById('countPending').textContent = data.pending_count || 0;
    document.getElementById('countApproved').textContent = data.approved_count || 0;
    document.getElementById('countInProcess').textContent = data.in_process_count || 0;
    document.getElementById('countRejected').textContent = data.rejected_count || 0;

    // Build timeline
    const timeline = document.getElementById('statusTimeline');
    if (data.recent_requests && data.recent_requests.length > 0) {
        timeline.innerHTML = data.recent_requests.map(req => `
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="font-semibold text-gray-800">${req.request_id}</h4>
                        <p class="text-sm text-gray-600 mt-1">${req.total_items} asset(s) requested</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold ${getStatusBadgeClass(req.status)}">
                        ${req.status}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-3">Requested on ${formatDate(req.request_date)}</p>
            </div>
        `).join('');
    } else {
        timeline.innerHTML = '<p class="text-gray-500">No requests yet</p>';
    }
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

function getStatusBadgeClass(status) {
    switch(status) {
        case 'Pending Approval': return 'bg-yellow-100 text-yellow-800';
        case 'Approved': return 'bg-green-100 text-green-800';
        case 'In Process': return 'bg-blue-100 text-blue-800';
        case 'Rejected': return 'bg-red-100 text-red-800';
        case 'Completed': return 'bg-purple-100 text-purple-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getPriorityBadgeClass(priority) {
    switch(priority) {
        case 'High': return 'bg-red-100 text-red-800';
        case 'Medium': return 'bg-yellow-100 text-yellow-800';
        case 'Low': return 'bg-green-100 text-green-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function showToast(message, type = 'info') {
    // Using Toastify if available
    if (typeof Toastify !== 'undefined') {
        Toastify({
            text: message,
            duration: 3000,
            gravity: 'top',
            position: 'right',
            backgroundColor: type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6',
            stopOnFocus: true
        }).showToast();
    } else {
        alert(message);
    }
}

function viewRequestDetails(requestId) {
    // Open detailed view modal
    alert('View details for request ID: ' + requestId);
    // TODO: Implement modal with request details
}

function editRequest(requestId) {
    alert('Edit request ID: ' + requestId);
    // TODO: Implement edit functionality
}

function deleteRequest(requestId) {
    if (confirm('Are you sure you want to delete this request?')) {
        fetch('../api/asset_requests.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: requestId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToast('Request deleted successfully', 'success');
                loadMyRequests();
            } else {
                showToast('Error deleting request', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error deleting request', 'error');
        });
    }
}
