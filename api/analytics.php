<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

session_start();
include 'db.php';

// Require admin (pages/warehouse_analytics.php already checks session 'id')
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // Total stock
    $tStmt = $conn->query("SELECT COALESCE(SUM(stock_level),0) AS total FROM inventory_items");
    $total = (int) $tStmt->fetchColumn();

    // Low stock alerts
    $lStmt = $conn->query("SELECT COUNT(*) FROM inventory_items WHERE stock_level <= reorder_level");
    $low = (int) $lStmt->fetchColumn();

    // Incoming / Outgoing shipments counts (simple categorization)
    $inStmt = $conn->prepare("SELECT COUNT(*) FROM shipments WHERE status IN ('Pending','In Transit')");
    $inStmt->execute();
    $incoming = (int) $inStmt->fetchColumn();

    $outStmt = $conn->prepare("SELECT COUNT(*) FROM shipments WHERE status IN ('Delivered','Cancelled')");
    $outStmt->execute();
    $outgoing = (int) $outStmt->fetchColumn();

    // Timeseries for last 8 months from shipments.created_at (grouped by year-month)
    $seriesStmt = $conn->prepare(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym,
            SUM(CASE WHEN status IN ('Pending','In Transit') THEN 1 ELSE 0 END) AS incoming,
            SUM(CASE WHEN status IN ('Delivered','Cancelled') THEN 1 ELSE 0 END) AS outgoing
         FROM shipments
         GROUP BY ym
         ORDER BY ym DESC
         LIMIT 8"
    );
    $seriesStmt->execute();
    $rows = $seriesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Build labels for the last 8 months (most recent first in SQL) and map values
    $labels = [];
    $inc = [];
    $out = [];

    // We want chronological order (oldest -> newest)
    $rows = array_reverse($rows);
    foreach ($rows as $r) {
        // format label to human friendly (YYYY-MM -> Mon YYYY)
        $dt = DateTime::createFromFormat('Y-m', $r['ym']);
        $labels[] = $dt ? $dt->format('M') : $r['ym'];
        $inc[] = (int)$r['incoming'];
        $out[] = (int)$r['outgoing'];
    }

    echo json_encode([
        'total_stock_items' => $total,
        'low_stock_alerts' => $low,
        'incoming_shipments' => $incoming,
        'outgoing_shipments' => $outgoing,
        'timeseries' => [ 'labels' => $labels, 'incoming' => $inc, 'outgoing' => $out ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'detail' => $e->getMessage()]);
}

?>
