<?php
require_once 'api/db.php';
require_once 'api/department_helpers.php';

echo "=== CHECKING DEPARTMENTS TABLE ===\n";
$result = $conn->query('SELECT * FROM departments ORDER BY id');
while($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo 'ID: ' . $row['id'] . ' | Name: ' . $row['department_name'] . "\n";
}

echo "\n=== CHECKING USER_DEPARTMENTS FOR USER 19 ===\n";
$stmt = $conn->prepare('SELECT * FROM user_departments WHERE user_id = 19');
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($records) > 0) {
    foreach($records as $row) {
        echo 'User ID: ' . $row['user_id'] . ' | Department ID: ' . $row['department_id'] . ' | Assigned At: ' . $row['assigned_at'] . "\n";
    }
} else {
    echo "No records found for user 19\n";
}

echo "\n=== TESTING HELPER FUNCTION ===\n";
$dept = getUserDepartment(19);
echo 'Result: ' . $dept . "\n";

echo "\n=== TESTING DIRECT QUERY ===\n";
$stmt = $conn->prepare("
    SELECT d.department_name 
    FROM departments d
    JOIN user_departments ud ON d.id = ud.department_id
    WHERE ud.user_id = 19
    LIMIT 1
");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    echo 'Direct query result: ' . $row['department_name'] . "\n";
} else {
    echo "Direct query returned no results\n";
}
?>
