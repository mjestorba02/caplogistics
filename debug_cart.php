<?php
include 'api/db.php';

try {
    // Check cart items
    $stmt = $conn->query('SELECT * FROM cart');
    $carts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo 'Cart items: ' . json_encode($carts) . "\n";

    // Check invoice
    $stmt = $conn->query('SELECT * FROM invoices WHERE id = 3');
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
    echo 'Invoice: ' . json_encode($invoice) . "\n";

    // Check products
    $stmt = $conn->query('SELECT * FROM products LIMIT 5');
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo 'Products: ' . json_encode($products) . "\n";

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>