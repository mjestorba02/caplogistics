// Request Asset Module JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize
    attachEventListeners();
});

// ============================================================================
// EVENT LISTENERS
// ============================================================================

function attachEventListeners() {
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
            
            // Also submit to request_supplies for procurement workflow
            const supplyPayload = {
                items: items,
                priority: priority,
                requester_id: data.requester_id,
                requester_name: data.requester_name,
                asset_request_id: data.request_id,
                notes: notes
            };
            
            fetch('../api/request_supplies.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(supplyPayload)
            })
            .then(response => response.json())
            .then(supplyData => {
                if (supplyData.status === 'success') {
                    showToast('Request forwarded to Procurement', 'success');
                }
            })
            .catch(error => console.error('Supply submission error:', error));
            
            // Reset form
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
// UTILITY FUNCTIONS
// ============================================================================

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
