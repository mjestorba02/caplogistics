<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();
include 'db.php';

// Best-effort: ensure invoices table exists and has linkage columns so GET filters won't fail
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS invoices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_number VARCHAR(64) UNIQUE,
        shipment_id INT DEFAULT NULL,
        user_id INT DEFAULT NULL,
        user_name VARCHAR(255),
        delivery_from VARCHAR(255),
        delivery_to VARCHAR(255),
        items JSON,
        date DATE,
        due_date DATE,
        subtotal DECIMAL(12,2) DEFAULT 0,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
} catch (Exception $e) { /* ignore */ }

// ensure columns exist (some installs may have older schema)
try {
    $chk = $conn->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'shipment_id'");
    $chk->execute();
    if (!(int)$chk->fetchColumn()) {
        try { $conn->exec("ALTER TABLE invoices ADD COLUMN shipment_id INT DEFAULT NULL"); } catch (Exception $ee) { /* ignore */ }
    }
    $chk2 = $conn->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'user_id'");
    $chk2->execute();
    if (!(int)$chk2->fetchColumn()) {
        try { $conn->exec("ALTER TABLE invoices ADD COLUMN user_id INT DEFAULT NULL"); } catch (Exception $ee) { /* ignore */ }
    }
    $chk3 = $conn->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'user_name'");
    $chk3->execute();
    if (!(int)$chk3->fetchColumn()) {
        try { $conn->exec("ALTER TABLE invoices ADD COLUMN user_name VARCHAR(255)"); } catch (Exception $ee) { /* ignore */ }
    }
    $chk4 = $conn->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'delivery_from'");
    $chk4->execute();
    if (!(int)$chk4->fetchColumn()) {
        try { $conn->exec("ALTER TABLE invoices ADD COLUMN delivery_from VARCHAR(255)"); } catch (Exception $ee) { /* ignore */ }
    }
    $chk5 = $conn->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'delivery_to'");
    $chk5->execute();
    if (!(int)$chk5->fetchColumn()) {
        try { $conn->exec("ALTER TABLE invoices ADD COLUMN delivery_to VARCHAR(255)"); } catch (Exception $ee) { /* ignore */ }
    }
    // Drop vendor_id if it exists
    $chk6 = $conn->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'vendor_id'");
    $chk6->execute();
    if ((int)$chk6->fetchColumn()) {
        try { $conn->exec("ALTER TABLE invoices DROP COLUMN vendor_id"); } catch (Exception $ee) { /* ignore */ }
    }
    // Add items column if it doesn't exist
    $chk7 = $conn->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'items'");
    $chk7->execute();
    if (!(int)$chk7->fetchColumn()) {
        try { $conn->exec("ALTER TABLE invoices ADD COLUMN items JSON"); } catch (Exception $ee) { /* ignore */ }
    }
    // Drop status column if it exists
    $chk8 = $conn->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoices' AND COLUMN_NAME = 'status'");
    $chk8->execute();
    if ((int)$chk8->fetchColumn()) {
        try { $conn->exec("ALTER TABLE invoices DROP COLUMN status"); } catch (Exception $ee) { /* ignore */ }
    }
} catch (Exception $e) { /* ignore */ }

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare('SELECT * FROM invoices WHERE id = ?');
            $stmt->execute([$_GET['id']]);
            $inv = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($inv) echo json_encode($inv);
            else { http_response_code(404); echo json_encode(['error'=>'Invoice not found']); }
            exit;
        }

    // filters: vendor_id (string), invoice_number, shipment_id, user_id
        $where = [];
        $params = [];
        if (isset($_GET['vendor_id'])) { $where[] = 'vendor_id = ?'; $params[] = $_GET['vendor_id']; }
        if (isset($_GET['invoice_number'])) { $where[] = 'invoice_number = ?'; $params[] = $_GET['invoice_number']; }
    if (isset($_GET['shipment_id'])) { $where[] = 'shipment_id = ?'; $params[] = $_GET['shipment_id']; }
    if (isset($_GET['user_id'])) { $where[] = 'user_id = ?'; $params[] = $_GET['user_id']; }

        $sql = 'SELECT * FROM invoices';
        if (!empty($where)) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY date DESC, id DESC';
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows);
        break;

    case 'POST':
        // create invoices table if not exists (best-effort) and include linkage columns
        try {
            $conn->exec("CREATE TABLE IF NOT EXISTS invoices (
                id INT AUTO_INCREMENT PRIMARY KEY,
                invoice_number VARCHAR(64) UNIQUE,
                shipment_id INT DEFAULT NULL,
                user_id INT DEFAULT NULL,
                user_name VARCHAR(255),
                delivery_from VARCHAR(255),
                delivery_to VARCHAR(255),
                items JSON,
                date DATE,
                due_date DATE,
                subtotal DECIMAL(12,2) DEFAULT 0,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        } catch (Exception $e) { /* ignore */ }

        $stmt = $conn->prepare('INSERT INTO invoices (invoice_number, shipment_id, user_id, user_name, delivery_from, delivery_to, items, date, due_date, subtotal, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $ok = $stmt->execute([
            $data['invoice_number'] ?? null,
            isset($data['shipment_id']) ? $data['shipment_id'] : null,
            isset($data['user_id']) ? $data['user_id'] : null,
            $data['user_name'] ?? null,
            $data['delivery_from'] ?? null,
            $data['delivery_to'] ?? null,
            isset($data['items']) ? json_encode($data['items']) : null,
            $data['date'] ?? null,
            $data['due_date'] ?? null,
            isset($data['subtotal']) ? $data['subtotal'] : 0,
            $data['notes'] ?? ''
        ]);
        if ($ok) {
            http_response_code(201);
            echo json_encode(['message'=>'Invoice created','id'=>$conn->lastInsertId()]);
        } else {
            http_response_code(400);
            echo json_encode(['error'=>'Failed to create invoice']);
        }
        break;

    case 'PUT':
        if (!isset($_GET['id'])) { http_response_code(400); echo json_encode(['error'=>'ID required']); exit; }
        $id = intval($_GET['id']);
        $stmt = $conn->prepare('UPDATE invoices SET invoice_number=?, shipment_id=?, user_id=?, user_name=?, delivery_from=?, delivery_to=?, items=?, date=?, due_date=?, subtotal=?, notes=? WHERE id=?');
        $ok = $stmt->execute([
            $data['invoice_number'] ?? null,
            isset($data['shipment_id']) ? $data['shipment_id'] : null,
            isset($data['user_id']) ? $data['user_id'] : null,
            $data['user_name'] ?? null,
            $data['delivery_from'] ?? null,
            $data['delivery_to'] ?? null,
            isset($data['items']) ? json_encode($data['items']) : null,
            $data['date'] ?? null,
            $data['due_date'] ?? null,
            isset($data['subtotal']) ? $data['subtotal'] : 0,
            $data['notes'] ?? '',
            $id
        ]);
        if ($ok) echo json_encode(['message'=>'Invoice updated']);
        else { http_response_code(400); echo json_encode(['error'=>'Failed to update invoice']); }
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) { http_response_code(400); echo json_encode(['error'=>'ID required']); exit; }
        $id = intval($_GET['id']);
        $stmt = $conn->prepare('DELETE FROM invoices WHERE id=?');
        if ($stmt->execute([$id])) echo json_encode(['message'=>'Invoice deleted']);
        else { http_response_code(400); echo json_encode(['error'=>'Failed to delete invoice']); }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error'=>'Method not allowed']);
        break;
}

?>
