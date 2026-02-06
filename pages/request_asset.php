<?php
session_start();
require_once __DIR__ . '/../layout/adminLayout.php';

$user_id = $_SESSION['id'] ?? null;
$user_name = $_SESSION['name'] ?? 'Unknown User';
$user_department = $_SESSION['department'] ?? 'Unknown Department';

if (!$user_id) {
    header('Location: ../login.php');
    exit;
}

function renderRequestAssetPage() {
    global $user_id, $user_name, $user_department;
?>
<div class="ml-0 md:ml-72 flex-1 flex flex-col">
    <div class="bg-white shadow p-4 md:p-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Request Asset</h1>
        <p class="text-gray-600 mt-1">Create and manage asset requests for your department</p>
    </div>

    <div class="p-4 md:p-6 flex-1 overflow-auto">
        <!-- Tab Navigation -->
        <div class="flex gap-4 mb-6 border-b border-gray-200">
            <button class="tab-button active px-4 py-2 border-b-2 border-blue-600 text-blue-600 font-semibold" data-tab="create-tab">
                <i class='bx bx-plus-circle mr-2'></i>Create Request
            </button>
            <button class="tab-button px-4 py-2 border-b-2 border-transparent text-gray-600 hover:text-gray-800" data-tab="view-tab">
                <i class='bx bx-list-ul mr-2'></i>My Requests
            </button>
            <button class="tab-button px-4 py-2 border-b-2 border-transparent text-gray-600 hover:text-gray-800" data-tab="status-tab">
                <i class='bx bx-search-alt-2 mr-2'></i>Track Status
            </button>
        </div>

        <!-- CREATE REQUEST TAB -->
        <div id="create-tab" class="tab-content">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Form Section -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-6">New Asset Request</h2>
                        
                        <form id="requestAssetForm" class="space-y-4">
                            <!-- Request Priority -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Priority Level</label>
                                <select name="priority" id="priority" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">-- Select Priority --</option>
                                    <option value="Low">Low</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="High">High</option>
                                </select>
                            </div>

                            <!-- Department -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Department</label>
                                <input type="text" value="<?php echo htmlspecialchars($user_department); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly>
                                <input type="hidden" name="department" value="<?php echo htmlspecialchars($user_department); ?>">
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Request Notes (Optional)</label>
                                <textarea name="notes" id="notes" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Any special requirements or notes about this request..."></textarea>
                            </div>

                            <!-- Add Items Section -->
                            <div class="pt-4 border-t">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Request Items</h3>
                                
                                <div id="itemsContainer" class="space-y-4">
                                    <!-- First item template -->
                                    <div class="item-row border border-gray-200 p-4 rounded-lg bg-gray-50">
                                        <div class="flex justify-between items-center mb-4">
                                            <span class="text-sm font-semibold text-gray-700">Item #1</span>
                                            <button type="button" class="remove-item text-red-500 hover:text-red-700 hidden">
                                                <i class='bx bx-trash text-lg'></i>
                                            </button>
                                        </div>

                                        <div class="space-y-3">
                                            <!-- Asset Description -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1">Asset Description *</label>
                                                <input type="text" class="asset-description w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Laptop, Office Chair, Software License" required>
                                            </div>

                                            <!-- Quantity & Urgency -->
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Quantity *</label>
                                                    <input type="number" class="quantity w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" min="1" value="1" required>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Urgency *</label>
                                                    <select class="urgency w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                                        <option value="Low">Low</option>
                                                        <option value="Medium" selected>Medium</option>
                                                        <option value="High">High</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Estimated Cost -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1">Estimated Cost (Optional)</label>
                                                <input type="number" class="estimated-cost w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" step="0.01" min="0" placeholder="0.00">
                                            </div>

                                            <!-- Item Notes -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1">Item Notes (Optional)</label>
                                                <input type="text" class="item-notes w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Specific requirements for this item...">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Add More Items Button -->
                                <button type="button" id="addMoreItemsBtn" class="mt-4 w-full px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 font-semibold flex items-center justify-center gap-2">
                                    <i class='bx bx-plus'></i> Add Another Item
                                </button>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex gap-3 pt-6 border-t">
                                <button type="submit" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold flex items-center justify-center gap-2">
                                    <i class='bx bx-send'></i> Submit Request
                                </button>
                                <button type="reset" class="flex-1 px-4 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 font-semibold">
                                    Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Info Section -->
                <div class="lg:col-span-1">
                    <div class="bg-blue-50 rounded-lg p-6 sticky top-4">
                        <h3 class="text-lg font-bold text-blue-900 mb-4">Request Information</h3>
                        
                        <div class="space-y-4 text-sm">
                            <div>
                                <p class="text-gray-600 font-semibold">Current User</p>
                                <p class="text-gray-800"><?php echo htmlspecialchars($user_name); ?></p>
                            </div>

                            <div>
                                <p class="text-gray-600 font-semibold">Department</p>
                                <p class="text-gray-800"><?php echo htmlspecialchars($user_department); ?></p>
                            </div>

                            <div class="pt-4 border-t border-blue-200">
                                <p class="text-gray-600 font-semibold mb-2">Status Workflow</p>
                                <div class="space-y-2 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">Pending</span>
                                        <span class="text-gray-600">Awaiting approval</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Approved</span>
                                        <span class="text-gray-600">Ready for procurement</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">In Process</span>
                                        <span class="text-gray-600">Sent to procurement</span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-blue-200">
                                <p class="text-gray-600 font-semibold mb-2">Tips</p>
                                <ul class="list-disc list-inside space-y-1 text-gray-700">
                                    <li>Be specific with asset descriptions</li>
                                    <li>Set realistic urgency levels</li>
                                    <li>Include cost estimates when possible</li>
                                    <li>Add special requirements in notes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW REQUESTS TAB -->
        <div id="view-tab" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">My Asset Requests</h2>
                </div>

                <!-- Filters -->
                <div class="p-6 border-b border-gray-200 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                            <input type="text" id="searchFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search by request ID, asset name...">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                            <select id="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- All Statuses --</option>
                                <option value="Pending Approval">Pending Approval</option>
                                <option value="Approved">Approved</option>
                                <option value="In Process">In Process</option>
                                <option value="Rejected">Rejected</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Priority</label>
                            <select id="priorityFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- All Priorities --</option>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Request ID</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Items</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Priority</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Created</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="requestsTableBody">
                            <tr class="border-b border-gray-200">
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading requests...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TRACK STATUS TAB -->
        <div id="status-tab" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Track Request Status</h2>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Status Summary -->
                    <div class="lg:col-span-1">
                        <div class="space-y-4">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <p class="text-sm text-yellow-700 font-semibold mb-2">Pending Approval</p>
                                <p class="text-2xl font-bold text-yellow-800" id="countPending">0</p>
                            </div>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <p class="text-sm text-green-700 font-semibold mb-2">Approved</p>
                                <p class="text-2xl font-bold text-green-800" id="countApproved">0</p>
                            </div>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <p class="text-sm text-blue-700 font-semibold mb-2">In Process</p>
                                <p class="text-2xl font-bold text-blue-800" id="countInProcess">0</p>
                            </div>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <p class="text-sm text-red-700 font-semibold mb-2">Rejected</p>
                                <p class="text-2xl font-bold text-red-800" id="countRejected">0</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Timeline -->
                    <div class="lg:col-span-2">
                        <div id="statusTimeline" class="space-y-4">
                            <p class="text-gray-500">Loading status information...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include JavaScript -->
<script src="../scripts/request_asset.js"></script>

<?php
}

// Capture the page HTML and pass it to the layout wrapper
ob_start();
renderRequestAssetPage();
$content = ob_get_clean();

adminLayout($content);
?>
