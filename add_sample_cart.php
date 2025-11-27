<?php
include 'api/db.php';

try {
    // Add some sample cart items for testing
    $cartItems = [
        [1, 10, 1], // user_id 1 (doesn't exist), product_id 10, quantity 1
        [2, 10, 2], // user_id 2, product_id 10, quantity 2  
        [3, 12, 1], // user_id 3, product_id 12, quantity 1
    ];

    foreach ($cartItems as $item) {
        try {
            $stmt = $conn->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?');
            $stmt->execute([$item[0], $item[1], $item[2], $item[2]]);
        } catch (Exception $e) {
            echo 'Error adding cart item: ' . $e->getMessage() . "\n";
        }
    }
    echo 'Sample cart items added\n';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>