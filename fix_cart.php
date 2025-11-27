<?php
include 'api/db.php';

try {
    // Clear existing cart and add correct items
    $conn->exec('DELETE FROM cart');
    
    // Add cart items with correct product_ids
    $cartItems = [
        [2, 1, 2], // user_id 2, product_id 1 (Gaming Headset), quantity 2
        [2, 2, 1], // user_id 2, product_id 2 (Gaming Keyboard), quantity 1
        [3, 3, 1], // user_id 3, product_id 3 (CPU Cooler), quantity 1
    ];

    foreach ($cartItems as $item) {
        $stmt = $conn->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)');
        $stmt->execute($item);
    }
    
    echo 'Cart updated with correct product IDs\n';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>