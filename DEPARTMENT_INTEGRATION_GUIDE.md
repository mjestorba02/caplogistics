# Department Integration Guide

## Quick Setup for Any Page

To display a user's department in any page, follow these steps:

### Step 1: Include the Helper
Add this line at the top of your PHP file (after other requires):

```php
require_once __DIR__ . '/../api/department_helpers.php';
```

### Step 2: Get User Department
Get the department for any user:

```php
// Get current user's department
$user_department = getUserDepartment($_SESSION['id']);

// Get a specific user's department
$john_department = getUserDepartment(3);

// Get with custom default value
$dept = getUserDepartment($user_id, 'No Department Assigned');
```

### Step 3: Display It
Use it anywhere in your HTML:

```php
<p>Department: <?php echo htmlspecialchars($user_department); ?></p>
```

---

## Available Helper Functions

### `getUserDepartment($user_id, $default = 'Unknown Department')`
Gets a single user's department name.

```php
$dept = getUserDepartment(11);  // Returns: "Warehouse & Logistics"
```

### `getAllDepartments()`
Gets all departments as an array.

```php
$depts = getAllDepartments();
// Returns array like:
// [
//     ['id' => 1, 'department_name' => 'Warehouse & Logistics', 'description' => '...'],
//     ['id' => 2, 'department_name' => 'Procurement', 'description' => '...'],
//     ...
// ]
```

### `getDepartmentById($dept_id)`
Gets a single department by ID.

```php
$dept = getDepartmentById(1);
// Returns: ['id' => 1, 'department_name' => 'Warehouse & Logistics', ...]
```

### `getUsersByDepartment($dept_id)`
Gets all users in a specific department.

```php
$logistics_users = getUsersByDepartment(1);
// Returns: [
//     ['id' => 11, 'name' => 'Lance', 'email' => '...', 'account_type' => 0],
//     ['id' => 16, 'name' => 'Randy', 'email' => '...', 'account_type' => 0],
// ]
```

### `assignUserToDepartment($user_id, $dept_id)`
Assigns (or reassigns) a user to a department.

```php
$success = assignUserToDepartment(11, 2);  // Move Lance to Procurement
if ($success) {
    echo "User assigned successfully";
}
```

---

## Usage Examples

### Example 1: Display Department in a Form
```php
<?php
require_once __DIR__ . '/../api/department_helpers.php';

$user_id = $_SESSION['id'];
$department = getUserDepartment($user_id);
?>

<div class="form-group">
    <label>Department</label>
    <input type="text" value="<?php echo htmlspecialchars($department); ?>" readonly>
    <input type="hidden" name="department" value="<?php echo htmlspecialchars($department); ?>">
</div>
```

### Example 2: Create Department Filter Dropdown
```php
<?php
$departments = getAllDepartments();
?>

<select name="department_filter">
    <option value="">All Departments</option>
    <?php foreach($departments as $dept): ?>
        <option value="<?php echo $dept['id']; ?>">
            <?php echo htmlspecialchars($dept['department_name']); ?>
        </option>
    <?php endforeach; ?>
</select>
```

### Example 3: List All Users in Current User's Department
```php
<?php
require_once __DIR__ . '/../api/department_helpers.php';

$user_id = $_SESSION['id'];
$user_dept = getUserDepartment($user_id);

// Get department ID from name
$depts = getAllDepartments();
$dept_id = null;
foreach($depts as $d) {
    if($d['department_name'] == $user_dept) {
        $dept_id = $d['id'];
        break;
    }
}

// Get all users in this department
if($dept_id) {
    $team_members = getUsersByDepartment($dept_id);
    foreach($team_members as $member) {
        echo $member['name'] . " (" . $member['email'] . ")<br>";
    }
}
?>
```

### Example 4: Show Department in User Info Card
```php
<?php
require_once __DIR__ . '/../api/department_helpers.php';

$user_department = getUserDepartment($_SESSION['id']);
?>

<div class="user-info-card">
    <h3><?php echo htmlspecialchars($_SESSION['name']); ?></h3>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
    <p><strong>Department:</strong> <?php echo htmlspecialchars($user_department); ?></p>
</div>
```

---

## Pages Already Updated

The following pages have been updated to show departments:
- ✅ `/pages/request_asset.php` - Shows user's department in asset request form

---

## Adding Department to More Pages

To add departments to any page:

1. Add at the top after other requires:
   ```php
   require_once __DIR__ . '/../api/department_helpers.php';
   ```

2. Get the department:
   ```php
   $user_department = getUserDepartment($_SESSION['id']);
   ```

3. Display it in your HTML:
   ```php
   <p><?php echo htmlspecialchars($user_department); ?></p>
   ```

That's it! The page will now show the actual department instead of "Unknown Department".

---

## Troubleshooting

**Q: Still showing "Unknown Department"?**
A: Make sure you:
1. Ran the Setup Departments page
2. Check that `user_departments` table has the user assigned
3. Check that the page includes the helper file
4. Clear your browser cache

**Q: Getting database error?**
A: The helper returns the default value if there's an error. Check server logs for details.

**Q: How to add a new department?**
A: Use the API or go to Setup Departments page and re-run to add a new department entry.
