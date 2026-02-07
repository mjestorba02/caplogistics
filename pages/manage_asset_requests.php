<?php
session_start();
require_once __DIR__ . '/../layout/adminLayout.php';
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../api/auth_helpers.php';

$user_id = $_SESSION['id'] ?? null;
$user_name = $_SESSION['name'] ?? 'Unknown User';
$account_type = $_SESSION['account_type'] ?? 0;

if (!$user_id) {
    header('Location: ../index.php');
    exit;
}

// Check if user has admin privileges
if ($account_type != 1) {
    echo "<div class='p-6 bg-red-100 text-red-800 rounded-lg m-6'>";
    echo "<h2 class='text-xl font-bold mb-2'>Access Denied</h2>";
    echo "<p>Only administrators can access this page. You are logged in as a regular user.</p>";
    echo "<p class='mt-2'><a href='dashboard.php' class='text-red-600 hover:underline'>Return to Dashboard</a></p>";
    echo "</div>";
    exit;
}

// Check if user has admin privileges (can be extended based on your role system)
// For now, all logged-in users can view
$filter_status = $_GET['status'] ?? 'Pending Approval';

function renderManageAssetRequestsPage() {
    global $user_id, $user_name, $filter_status;
?>
<div class="ml-0 md:ml-72 flex-1 flex flex-col">
    <div class="bg-white shadow p-4 md:p-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Manage Asset Requests</h1>
            <p class="text-gray-600 mt-1">Review and approve/reject pending asset requests</p>
        </div>
    </div>

    <div class="p-4 md:p-6 flex-1 overflow-auto">
        <!-- Status Filter -->
        <div class="mb-6 flex gap-4 flex-wrap">
            <a href="?status=Pending Approval" class="px-4 py-2 rounded-lg font-semibold <?php echo $filter_status === 'Pending Approval' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                <i class='bx bx-time-five mr-1'></i>Pending Approval
            </a>
            <a href="?status=Approved" class="px-4 py-2 rounded-lg font-semibold <?php echo $filter_status === 'Approved' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                <i class='bx bx-check-circle mr-1'></i>Approved
            </a>
            <a href="?status=Rejected" class="px-4 py-2 rounded-lg font-semibold <?php echo $filter_status === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                <i class='bx bx-x-circle mr-1'></i>Rejected
            </a>
            <a href="?status=In Process" class="px-4 py-2 rounded-lg font-semibold <?php echo $filter_status === 'In Process' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                <i class='bx bx-cog mr-1'></i>In Process
            </a>
        </div>

        <!-- Requests Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div id="requestsContainer" class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Request ID</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Requester</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Department</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Items</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Priority</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="requestsTableBody">
                        <tr><td colspan="8" class="px-4 py-4 text-center text-gray-500">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Request Details -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="p-6 border-b border-gray-200 sticky top-0 bg-white">
            <h2 class="text-xl font-bold text-gray-800">Request Details</h2>
            <p class="text-sm text-gray-500" id="modalRequestId"></p>
        </div>
        <div id="modalContent" class="p-6">
            <!-- Content loaded via JS -->
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end gap-3 sticky bottom-0 bg-gray-50">
            <button onclick="document.getElementById('detailsModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Close
            </button>
            <div id="modalActions" class="flex gap-3"></div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Approve Request</h2>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Approval Notes (Optional)</label>
                <textarea id="approvalNotes" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" rows="3" placeholder="Add approval comments..."></textarea>
            </div>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
            <button onclick="document.getElementById('approvalModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
            </button>
            <button id="confirmApproveBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class='bx bx-check mr-1'></i>Approve
            </button>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Reject Request</h2>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Rejection Reason (Required)</label>
                <textarea id="rejectionReason" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" rows="3" placeholder="Explain why this request is being rejected..." required></textarea>
            </div>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
            <button onclick="document.getElementById('rejectionModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
            </button>
            <button id="confirmRejectBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class='bx bx-x mr-1'></i>Reject
            </button>
        </div>
    </div>
</div>

<script src="../scripts/toastify.js"></script>
<script src="../scripts/manage_asset_requests.js"></script>
<?php
}

// Capture the page HTML and pass it to the layout wrapper
ob_start();
renderManageAssetRequestsPage();
$content = ob_get_clean();

adminLayout($content);
?>
