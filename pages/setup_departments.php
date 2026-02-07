<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: ../index.php');
    exit();
}

require_once __DIR__ . '/../api/db.php';

$setup = isset($_POST['setup']);

if ($setup) {
    try {
        // Create departments table
        $conn->exec("CREATE TABLE IF NOT EXISTS departments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            department_name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_department_name (department_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // Create user_departments table
        $conn->exec("CREATE TABLE IF NOT EXISTS user_departments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            department_id INT NOT NULL,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_dept (user_id, department_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_department_id (department_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // Insert sample departments
        $departments = [
            'Warehouse & Logistics',
            'Procurement',
            'Administration',
            'Sales & Customer Service',
            'Finance',
            'Operations',
            'Quality Control',
            'Shipping & Delivery'
        ];

        $descriptions = [
            'Warehouse & Logistics' => 'Handles inventory, storage, and logistics operations',
            'Procurement' => 'Manages purchase orders and supplier relationships',
            'Administration' => 'Administrative and management functions',
            'Sales & Customer Service' => 'Sales operations and customer support',
            'Finance' => 'Financial operations and accounting',
            'Operations' => 'General operations and coordination',
            'Quality Control' => 'Quality assurance and inspection',
            'Shipping & Delivery' => 'Shipping and delivery coordination'
        ];

        foreach ($departments as $dept) {
            try {
                $stmt = $conn->prepare("INSERT INTO departments (department_name, description) VALUES (?, ?)");
                $stmt->execute([$dept, $descriptions[$dept]]);
            } catch (Exception $e) {
                // Ignore duplicate entries
            }
        }

        // Assign departments to users
        $userAssignments = [
            3 => 'Administration',      // John
            8 => 'Administration',      // john (admin)
            11 => 'Warehouse & Logistics', // lance
            12 => 'Operations',         // manang
            13 => 'Quality Control',    // Ariel mendoza
            14 => 'Procurement',        // andrei
            15 => 'Shipping & Delivery', // Daniel Zabat
            16 => 'Warehouse & Logistics', // randy alvarez
            17 => 'Sales & Customer Service', // Harley
            18 => 'Procurement',        // Julian Castañares
            19 => 'Finance'             // Mark John Estorba
        ];

        foreach ($userAssignments as $user_id => $dept_name) {
            $stmt = $conn->prepare("SELECT id FROM departments WHERE department_name = ?");
            $stmt->execute([$dept_name]);
            $dept = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dept) {
                try {
                    // Delete existing assignment
                    $delStmt = $conn->prepare("DELETE FROM user_departments WHERE user_id = ?");
                    $delStmt->execute([$user_id]);

                    // Add new assignment
                    $insStmt = $conn->prepare("INSERT INTO user_departments (user_id, department_id) VALUES (?, ?)");
                    $insStmt->execute([$user_id, $dept['id']]);
                } catch (Exception $e) {
                    // Ignore duplicates
                }
            }
        }

        $success = true;
        $message = 'Departments table created and users assigned successfully!';
    } catch (Exception $e) {
        $success = false;
        $message = 'Error: ' . $e->getMessage();
    }
}

include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6 max-w-2xl">
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <span>Setup Departments</span>
    </div>

    <div class="bg-white p-8 rounded-lg shadow">
        <h1 class="text-3xl font-bold mb-6">Setup Departments System</h1>

        <?php if (isset($success)): ?>
            <div class="mb-6 p-4 rounded <?= $success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' ?>">
                <p class="<?= $success ? 'text-green-700' : 'text-red-700' ?>"><?= htmlspecialchars($message) ?></p>
            </div>
        <?php endif; ?>

        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded">
            <h2 class="font-semibold text-blue-900 mb-2">What this does:</h2>
            <ul class="list-disc list-inside text-blue-900 space-y-1">
                <li>Creates a <code>departments</code> table with 8 sample departments</li>
                <li>Creates a <code>user_departments</code> table to link users to departments</li>
                <li>Assigns all 19 users to appropriate departments</li>
                <li>Replaces "Unknown Department" with actual department names</li>
            </ul>
        </div>

        <form method="POST" class="space-y-4">
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
                <p class="text-yellow-900 font-semibold mb-2">⚠️ Warning</p>
                <p class="text-yellow-900 mb-4">This will modify your database. Make sure you have a backup!</p>
            </div>

            <button type="submit" name="setup" value="1" class="w-full bg-indigo-600 text-white px-6 py-3 rounded hover:bg-indigo-700 transition font-semibold">
                Create Departments & Assign Users
            </button>
        </form>

        <?php if (!isset($success)): ?>
        <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded">
            <h3 class="font-semibold mb-3">Sample Departments:</h3>
            <ul class="space-y-2 text-sm">
                <li class="flex justify-between"><span>Warehouse & Logistics</span> <span class="text-gray-500">Lance, Randy Alvarez</span></li>
                <li class="flex justify-between"><span>Procurement</span> <span class="text-gray-500">Andrei, Julian Castañares</span></li>
                <li class="flex justify-between"><span>Administration</span> <span class="text-gray-500">John (admin)</span></li>
                <li class="flex justify-between"><span>Sales & Customer Service</span> <span class="text-gray-500">Harley</span></li>
                <li class="flex justify-between"><span>Finance</span> <span class="text-gray-500">Mark John Estorba</span></li>
                <li class="flex justify-between"><span>Operations</span> <span class="text-gray-500">Manang</span></li>
                <li class="flex justify-between"><span>Quality Control</span> <span class="text-gray-500">Ariel Mendoza</span></li>
                <li class="flex justify-between"><span>Shipping & Delivery</span> <span class="text-gray-500">Daniel Zabat</span></li>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>
HTML;

adminLayout($children);
?>
