-- Create departments table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_department_name (department_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create user_departments table
CREATE TABLE IF NOT EXISTS user_departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    department_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_dept (user_id, department_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_department_id (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample departments
INSERT INTO departments (department_name, description) VALUES
('Warehouse & Logistics', 'Handles inventory, storage, and logistics operations'),
('Procurement', 'Manages purchase orders and supplier relationships'),
('Administration', 'Administrative and management functions'),
('Sales & Customer Service', 'Sales operations and customer support'),
('Finance', 'Financial operations and accounting'),
('Operations', 'General operations and coordination'),
('Quality Control', 'Quality assurance and inspection'),
('Shipping & Delivery', 'Shipping and delivery coordination');

-- Assign departments to users
-- Admins (account_type = 1): Administration
INSERT INTO user_departments (user_id, department_id) 
SELECT u.id, d.id FROM users u, departments d 
WHERE u.account_type = 1 AND d.department_name = 'Administration'
ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP;

-- Regular users - assign based on name/role inference
-- John users (Admin)
INSERT INTO user_departments (user_id, department_id) SELECT 3, id FROM departments WHERE department_name = 'Administration' ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP;
INSERT INTO user_departments (user_id, department_id) SELECT 8, id FROM departments WHERE department_name = 'Administration' ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP;

-- Lance - Warehouse/Logistics
INSERT INTO user_departments (user_id, department_id) SELECT 11, id FROM departments WHERE department_name = 'Warehouse & Logistics' ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP;

-- Manang - Operations
INSERT INTO user_departments (user_id, department_id) SELECT 12, id FROM departments WHERE department_name = 'Operations' ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP;

-- Ariel Mendoza - Quality Control
INSERT INTO user_departments (user_id, department_id) SELECT 13, id FROM departments WHERE department_name = 'Quality Control' ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP;

-- Andrei - Procurement
INSERT INTO user_departments (user_id, department_id) SELECT 14, id FROM departments WHERE department_name = 'Procurement' ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP;

-- Daniel Zabat - Shipping & Delivery
INSERT INTO user_departments (user_id, department_id) SELECT 15, id FROM departments WHERE department_name = 'Shipping & Delivery' ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP;

-- Randy Alvarez - Warehouse & Logistics
INSERT INTO user_departments (user_id, department_id) SELECT 16, id FROM departments WHERE department_name = 'Warehouse & Logistics' ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP;

-- Harley - Sales & Customer Service
INSERT INTO user_departments (user_id, department_id) SELECT 17, id FROM departments WHERE department_name = 'Sales & Customer Service' ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP;

-- Julian Castañares - Procurement
INSERT INTO user_departments (user_id, department_id) SELECT 18, id FROM departments WHERE department_name = 'Procurement' ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP;

-- Mark John Estorba - Finance
INSERT INTO user_departments (user_id, department_id) SELECT 19, id FROM departments WHERE department_name = 'Finance' ON DUPLICATE KEY UPDATE assigned_at = CURRENT_TIMESTAMP;
