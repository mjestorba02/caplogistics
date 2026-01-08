-- Document Requests Table
-- This SQL creates the document_requests table for storing document requests

CREATE TABLE IF NOT EXISTS document_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_id INT NOT NULL,
    requester_name VARCHAR(255) NOT NULL,
    document_type VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('Pending', 'Approved', 'Rejected', 'Completed') DEFAULT 'Pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_date TIMESTAMP NULL,
    INDEX idx_requester_id (requester_id),
    INDEX idx_status (status),
    INDEX idx_request_date (request_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Document Uploads Table
-- This SQL creates the document_uploads table for storing uploaded documents

CREATE TABLE IF NOT EXISTS document_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NULL,
    uploader_id INT NOT NULL,
    uploader_name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    status ENUM('Uploaded', 'Verified', 'Rejected') DEFAULT 'Uploaded',
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_request_id (request_id),
    INDEX idx_uploader_id (uploader_id),
    INDEX idx_status (status),
    INDEX idx_upload_date (upload_date),
    FOREIGN KEY (request_id) REFERENCES document_requests(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;