-- Procurement Requests Table
-- This SQL creates the procurement_requests table for storing supply requests

CREATE TABLE IF NOT EXISTS procurement_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    description TEXT NULL,
    urgency ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    requester_id INT NOT NULL,
    requester_name VARCHAR(255) NOT NULL,
    date_requested TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    INDEX idx_requester_id (requester_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data (optional)
-- INSERT INTO procurement_requests (item_name, quantity, description, urgency, requester_id, requester_name) VALUES
-- ('Printer Paper', 100, 'A4 size, 80gsm', 'Medium', 1, 'John Doe'),
-- ('Office Chairs', 5, 'Ergonomic chairs', 'High', 2, 'Jane Smith');</content>
<parameter name="filePath">c:\xampp\htdocs\caplog1\sql\procurement_requests.sql