<?php
include 'api/db.php';

try {
    // Add cart items for user_id 2
    $stmt = $conn->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?');
    $stmt->execute([2, 10, 2, 2]); // user 2, product 10 (Gaming Headset), quantity 2
    $stmt->execute([2, 12, 1, 1]); // user 2, product 12 (Gaming Keyboard), quantity 1
    
    echo 'Added cart items for user 2\n';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>