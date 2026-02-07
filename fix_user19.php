<?php
require_once 'api/db.php';

// Remove user 19 from Administration (the duplicate from the generic admin query)
$stmt = $conn->prepare('DELETE FROM user_departments WHERE user_id = 19 AND department_id = 3');
$stmt->execute();
echo "Removed user 19 from Administration (department_id 3)\n";

// Verify
$stmt = $conn->prepare('SELECT ud.user_id, ud.department_id, d.department_name FROM user_departments ud JOIN departments d ON ud.department_id = d.id WHERE ud.user_id = 19');
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "User 19 now has departments:\n";
foreach($rows as $row) {
    echo '  - ' . $row['department_name'] . ' (ID: ' . $row['department_id'] . ")\n";
}
?>
