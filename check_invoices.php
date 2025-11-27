<?php
include 'api/db.php';
try {
    $stmt = $conn->query('SELECT COUNT(*) FROM invoices');
    $count = $stmt->fetchColumn();
    echo 'Invoices count: ' . $count . "\n";

    if ($count > 0) {
        $stmt = $conn->query('SELECT * FROM invoices LIMIT 1');
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        echo 'Sample invoice: ' . json_encode($invoice) . "\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>