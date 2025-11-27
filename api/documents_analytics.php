<?php
// api/documents_analytics.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['id'])) { http_response_code(401); echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

$action = $_GET['action'] ?? 'summary';

try {
    if ($action === 'summary') {
        $stmt = $conn->query("SELECT status, COUNT(*) as cnt FROM documents GROUP BY status");
        $counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status'=>'success','counts'=>$counts]);
        exit;
    }
    if ($action === 'monthly') {
        $stmt = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as cnt FROM documents GROUP BY month ORDER BY month DESC LIMIT 12");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status'=>'success','monthly'=>$rows]);
        exit;
    }
    echo json_encode(['status'=>'error','message'=>'Invalid action']); exit;
} catch (Throwable $e) { http_response_code(500); echo json_encode(['status'=>'error','message'=>$e->getMessage()]); }