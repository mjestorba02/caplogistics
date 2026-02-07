<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    // Create archived_items table if not exists
    $conn->exec("CREATE TABLE IF NOT EXISTS archived_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        archive_type VARCHAR(100) NOT NULL COMMENT 'Type of item archived (contract, request, supplier, etc.)',
        item_id INT NOT NULL,
        original_table VARCHAR(100) NOT NULL COMMENT 'Name of the original table',
        item_data JSON NOT NULL COMMENT 'Full data of archived item as JSON',
        archived_by VARCHAR(100),
        archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        reason TEXT,
        restore_allowed TINYINT(1) DEFAULT 1,
        INDEX idx_archive_type (archive_type),
        INDEX idx_item_id (item_id),
        INDEX idx_archived_at (archived_at)
    )");

    switch ($method) {
        // ============================================================================
        // GET - Retrieve archived items
        // ============================================================================
        case 'GET':
            $action = $_GET['action'] ?? '';
            $archive_type = $_GET['archive_type'] ?? '';

            if ($action === 'list') {
                // Get archived items by type
                $query = "SELECT * FROM archived_items";
                $params = [];

                if ($archive_type) {
                    $query .= " WHERE archive_type = ?";
                    $params[] = $archive_type;
                }

                $query .= " ORDER BY archived_at DESC";
                
                $stmt = $conn->prepare($query);
                $stmt->execute($params);
                $archived = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Archived items retrieved',
                    'items' => $archived,
                    'count' => count($archived)
                ]);
                exit;
            } else {
                // Get a specific archived item
                $id = $_GET['id'] ?? 0;
                $stmt = $conn->prepare("SELECT * FROM archived_items WHERE id = ?");
                $stmt->execute([$id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    echo json_encode(['status' => 'error', 'message' => 'Archived item not found']);
                    exit;
                }

                echo json_encode([
                    'status' => 'success',
                    'item' => $item
                ]);
                exit;
            }
            break;

        // ============================================================================
        // POST - Archive an item (move from original table to archive)
        // ============================================================================
        case 'POST':
            $archive_type = $input['archive_type'] ?? '';
            $item_id = intval($input['item_id'] ?? 0);
            $original_table = $input['original_table'] ?? '';
            $reason = $input['reason'] ?? '';
            $user_name = $_SESSION['name'] ?? 'Unknown';

            if (!$archive_type || !$item_id || !$original_table) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'archive_type, item_id, and original_table are required'
                ]);
                exit;
            }

            // Fetch the item data before archiving
            $stmt = $conn->prepare("SELECT * FROM `{$original_table}` WHERE id = ?");
            $stmt->execute([$item_id]);
            $item_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item_data) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Item not found in original table'
                ]);
                exit;
            }

            // Insert into archive table
            $stmt = $conn->prepare("
                INSERT INTO archived_items 
                (archive_type, item_id, original_table, item_data, archived_by, reason)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $archive_type,
                $item_id,
                $original_table,
                json_encode($item_data),
                $user_name,
                $reason
            ]);

            // Delete from original table
            $stmt = $conn->prepare("DELETE FROM `{$original_table}` WHERE id = ?");
            $stmt->execute([$item_id]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Item archived successfully',
                'archive_id' => $conn->lastInsertId()
            ]);
            exit;
            break;

        // ============================================================================
        // PATCH/PUT - Restore an archived item
        // ============================================================================
        case 'PUT':
            $archive_id = intval($input['archive_id'] ?? 0);

            if (!$archive_id) {
                echo json_encode(['status' => 'error', 'message' => 'archive_id is required']);
                exit;
            }

            // Get archived item
            $stmt = $conn->prepare("SELECT * FROM archived_items WHERE id = ?");
            $stmt->execute([$archive_id]);
            $archived_item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$archived_item || !$archived_item['restore_allowed']) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Archived item cannot be restored'
                ]);
                exit;
            }

            // Restore the item to original table
            $original_table = $archived_item['original_table'];
            $item_data = json_decode($archived_item['item_data'], true);

            // Build the INSERT query dynamically
            $columns = array_keys($item_data);
            $placeholders = array_fill(0, count($columns), '?');
            $values = array_values($item_data);

            $query = "INSERT INTO `{$original_table}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $conn->prepare($query);
            $stmt->execute($values);

            // Remove from archive
            $stmt = $conn->prepare("DELETE FROM archived_items WHERE id = ?");
            $stmt->execute([$archive_id]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Item restored successfully'
            ]);
            exit;
            break;

        // ============================================================================
        // DELETE - Permanently delete an archived item
        // ============================================================================
        case 'DELETE':
            $archive_id = intval($input['archive_id'] ?? 0);

            if (!$archive_id) {
                echo json_encode(['status' => 'error', 'message' => 'archive_id is required']);
                exit;
            }

            $stmt = $conn->prepare("DELETE FROM archived_items WHERE id = ?");
            $stmt->execute([$archive_id]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Archived item permanently deleted'
            ]);
            exit;
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}
?>
