<?php
session_start();

// Only allow authenticated users to see this
if (!isset($_SESSION['id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../api/department_helpers.php';
require_once __DIR__ . '/../layout/adminLayout.php';

$user_id = $_SESSION['id'];
$user_name = $_SESSION['name'] ?? 'Unknown';

// Get current user's department
$current_dept = getUserDepartment($user_id);

// Get all departments
$all_depts = getAllDepartments();

// Get all users
require_once __DIR__ . '/../api/db.php';
$stmt = $conn->prepare("SELECT id, name, email FROM users ORDER BY name");
$stmt->execute();
$all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$children = <<<'HTML'
<div class="p-6">
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <span>Department System Verification</span>
    </div>

    <h1 class="text-3xl font-bold text-gray-800 mb-6">Department System Verification</h1>

    <!-- Current User Info -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Your Department</h2>
        <div class="bg-blue-50 border border-blue-200 rounded p-4">
            <p class="text-lg"><strong>Name:</strong> <?php echo htmlspecialchars($user_name); ?> (ID: <?php echo $user_id; ?>)</p>
            <p class="text-lg"><strong>Department:</strong> 
                <span class="font-bold text-blue-600">
                    <?php echo htmlspecialchars($current_dept); ?>
                </span>
            </p>
            <?php if ($current_dept === 'Unknown Department'): ?>
                <p class="text-red-600 mt-2">⚠️ Department not assigned! Run Setup Departments first.</p>
            <?php else: ?>
                <p class="text-green-600 mt-2">✅ Department correctly assigned!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- All Departments -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">All Departments (<?php echo count($all_depts); ?>)</h2>
        <?php if (count($all_depts) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($all_depts as $dept): ?>
                    <div class="border rounded p-3 bg-gray-50">
                        <p class="font-bold text-indigo-600"><?php echo htmlspecialchars($dept['department_name']); ?></p>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($dept['description'] ?? ''); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-yellow-600">⚠️ No departments found. Run Setup Departments first.</p>
        <?php endif; ?>
    </div>

    <!-- User-Department Assignments -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">User Departments (<?php echo count($all_users); ?> Users)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left">User ID</th>
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Department</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_users as $user): ?>
                        <?php 
                            $dept = getUserDepartment($user['id']);
                            $is_unknown = $dept === 'Unknown Department';
                        ?>
                        <tr class="border-b <?php echo $is_unknown ? 'bg-red-50' : ''; ?>">
                            <td class="px-4 py-2"><?php echo $user['id']; ?></td>
                            <td class="px-4 py-2">
                                <?php echo htmlspecialchars($user['name']); ?>
                                <?php if ($user['id'] === $user_id): ?>
                                    <span class="text-xs bg-blue-200 px-2 py-1 rounded">YOU</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2 text-xs"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="px-4 py-2">
                                <?php if ($is_unknown): ?>
                                    <span class="text-red-600 font-bold">❌ Unknown Department</span>
                                <?php else: ?>
                                    <span class="text-green-600 font-bold">✅ <?php echo htmlspecialchars($dept); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Test Result -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">System Status</h2>
        <?php
            $dept_count = count($all_depts);
            $all_assigned = true;
            foreach ($all_users as $user) {
                if (getUserDepartment($user['id']) === 'Unknown Department') {
                    $all_assigned = false;
                    break;
                }
            }
        ?>
        
        <div class="space-y-3">
            <div class="flex items-center <?php echo $dept_count > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                <span class="text-2xl mr-3"><?php echo $dept_count > 0 ? '✅' : '❌'; ?></span>
                <span><?php echo $dept_count; ?> departments created</span>
            </div>
            
            <div class="flex items-center <?php echo $all_assigned ? 'text-green-600' : 'text-red-600'; ?>">
                <span class="text-2xl mr-3"><?php echo $all_assigned ? '✅' : '❌'; ?></span>
                <span>All users assigned to departments</span>
            </div>
            
            <div class="flex items-center <?php echo $current_dept !== 'Unknown Department' ? 'text-green-600' : 'text-red-600'; ?>">
                <span class="text-2xl mr-3"><?php echo $current_dept !== 'Unknown Department' ? '✅' : '❌'; ?></span>
                <span>Your department: <?php echo htmlspecialchars($current_dept); ?></span>
            </div>
        </div>

        <?php if ($dept_count > 0 && $all_assigned && $current_dept !== 'Unknown Department'): ?>
            <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded">
                <p class="text-green-700 font-bold">🎉 All systems operational!</p>
                <p class="text-green-700 text-sm mt-2">Departments are working correctly. Pages should now display departments instead of "Unknown Department".</p>
            </div>
        <?php else: ?>
            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded">
                <p class="text-yellow-700 font-bold">⚠️ Setup Required</p>
                <p class="text-yellow-700 text-sm mt-2">
                    Go to <strong>Admin Settings</strong> → <strong>Setup Departments</strong> and run the setup.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
HTML;

adminLayout($children);
?>
