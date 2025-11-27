<?php
include 'api/db.php';

try {
    $conn->exec('CREATE TABLE IF NOT EXISTS cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_product (user_id, product_id)
    )');
    echo 'Cart table created successfully\n';
} catch (Exception $e) {
    echo 'Error creating cart table: ' . $e->getMessage() . '\n';
}

try {
    $conn->exec('CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');
    echo 'Products table created successfully\n';
} catch (Exception $e) {
    echo 'Error creating products table: ' . $e->getMessage() . '\n';
}

// Populate products table with data from inventory_items
try {
    $stmt = $conn->query('SELECT COUNT(*) FROM products');
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("INSERT INTO products (name, price, description, image) 
                     SELECT item_name, price, notes, product_photo 
                     FROM inventory_items");
        echo 'Products table populated from inventory_items\n';
    }
} catch (Exception $e) {
    echo 'Error populating products table: ' . $e->getMessage() . '\n';
}

echo 'Done\n';
?>