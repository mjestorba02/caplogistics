<?php
/**
 * Department Helper Functions
 * Include this file in any page that needs to display user department information
 */

require_once __DIR__ . '/db.php';

/**
 * Get user's department from database
 * @param int $user_id - User ID
 * @param string $default - Default value if not found
 * @return string - Department name or default value
 */
function getUserDepartment($user_id, $default = 'Unknown Department') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT d.department_name 
            FROM departments d
            JOIN user_departments ud ON d.id = ud.department_id
            WHERE ud.user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        $dept = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dept) {
            return $dept['department_name'];
        }
    } catch (Exception $e) {
        error_log("Error getting user department: " . $e->getMessage());
    }
    
    return $default;
}

/**
 * Get all departments
 * @return array - Array of all departments
 */
function getAllDepartments() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT id, department_name, description FROM departments ORDER BY department_name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting departments: " . $e->getMessage());
        return [];
    }
}

/**
 * Get department by ID
 * @param int $dept_id - Department ID
 * @return array|null - Department data or null if not found
 */
function getDepartmentById($dept_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT id, department_name, description FROM departments WHERE id = ?");
        $stmt->execute([$dept_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting department: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all users in a department
 * @param int $dept_id - Department ID
 * @return array - Array of users in the department
 */
function getUsersByDepartment($dept_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT u.id, u.name, u.email, u.account_type
            FROM users u
            JOIN user_departments ud ON u.id = ud.user_id
            WHERE ud.department_id = ?
            ORDER BY u.name ASC
        ");
        $stmt->execute([$dept_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting users by department: " . $e->getMessage());
        return [];
    }
}

/**
 * Assign user to department
 * @param int $user_id - User ID
 * @param int $dept_id - Department ID
 * @return bool - Success status
 */
function assignUserToDepartment($user_id, $dept_id) {
    global $conn;
    
    try {
        // Remove existing assignment
        $delStmt = $conn->prepare("DELETE FROM user_departments WHERE user_id = ?");
        $delStmt->execute([$user_id]);
        
        // Add new assignment
        $insStmt = $conn->prepare("INSERT INTO user_departments (user_id, department_id) VALUES (?, ?)");
        $insStmt->execute([$user_id, $dept_id]);
        
        return true;
    } catch (Exception $e) {
        error_log("Error assigning user to department: " . $e->getMessage());
        return false;
    }
}
