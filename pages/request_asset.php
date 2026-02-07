<?php
session_start();
require_once __DIR__ . '/../layout/adminLayout.php';
require_once __DIR__ . '/../api/department_helpers.php';

$user_id = $_SESSION['id'] ?? null;
$user_name = $_SESSION['name'] ?? 'Unknown User';

if (!$user_id) {
    header('Location: ../login.php');
    exit;
}

// Get user's department from database using helper function
$user_department = getUserDepartment($user_id, 'Unknown Department');

function renderRequestAssetPage() {
    global $user_id, $user_name, $user_department;
?>
<div class="p-6">
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <span>Asset Management</span> &gt; <span>Request Asset</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-0">Request Asset</h1>
    </div>



    <!-- CREATE REQUEST SECTION -->
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
                            <select name="priority" id="priority" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
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
                            <textarea name="notes" id="notes" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" rows="3" placeholder="Any special requirements or notes about this request..."></textarea>
                        </div>

                        <!-- Add Items Section -->
                        <div class="pt-4 border-t">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Request Items</h3>
                            
                            <div id="itemsContainer" class="space-y-4">
                                <!-- First item template -->
                                <div class="item-row border border-gray-200 p-4 rounded-lg bg-white">
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
                                        <input type="text" class="asset-description w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g., Laptop, Office Chair, Software License" required>
                                            </div>

                                    <!-- Quantity & Urgency -->
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

                                    <!-- Estimated Cost -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Estimated Cost (Optional)</label>
                                        <input type="number" class="estimated-cost w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" step="0.01" min="0" placeholder="0.00">
                                    </div>

                                    <!-- Item Notes -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Item Notes (Optional)</label>
                                        <input type="text" class="item-notes w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Specific requirements for this item...">
                                    </div>
                                </div>
                            </div>
                                </div>

                            <!-- Add More Items Button -->
                            <button type="button" id="addMoreItemsBtn" class="mt-4 w-full px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 font-semibold flex items-center justify-center gap-2">
                                <i class='bx bx-plus'></i> Add Another Item
                            </button>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex gap-3 pt-6 border-t">
                            <button type="submit" class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center justify-center gap-2">
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
                <div class="bg-white rounded-lg p-6 shadow sticky top-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Request Information</h3>
                    
                    <div class="space-y-4 text-sm">
                        <div>
                            <p class="text-gray-600 font-semibold">Current User</p>
                            <p class="text-gray-800"><?php echo htmlspecialchars($user_name); ?></p>
                        </div>

                        <div>
                            <p class="text-gray-600 font-semibold">Department</p>
                            <p class="text-gray-800"><?php echo htmlspecialchars($user_department); ?></p>
                        </div>

                        <div class="pt-4 border-t border-gray-200">
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

                        <div class="pt-4 border-t border-gray-200">
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
