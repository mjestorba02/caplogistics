-- Migration: Ensure Project Performance Monitoring & Closure table exists
CREATE TABLE IF NOT EXISTS project_performance_monitoring_closure (
    id INT AUTO_INCREMENT PRIMARY KEY,
    performance_id VARCHAR(50) UNIQUE NOT NULL,
    project_id VARCHAR(50) NOT NULL,
    reporting_period_start DATE,
    reporting_period_end DATE,
    kpi_cost_actual DECIMAL(15,2),
    kpi_cost_planned DECIMAL(15,2),
    kpi_cost_variance DECIMAL(15,2),
    kpi_cost_variance_percent DECIMAL(5,2),
    kpi_delivery_time_actual INT,
    kpi_delivery_time_planned INT,
    kpi_delivery_time_variance INT,
    kpi_milestone_adherence_percent INT,
    kpi_quality_score INT,
    kpi_customer_satisfaction INT,
    performance_status ENUM('On Track','At Risk','Off Track','Critical') DEFAULT 'On Track',
    variance_explanation TEXT,
    corrective_actions_taken TEXT,
    improvement_suggestions TEXT,
    project_closure_status ENUM('Active','In Review','Closed','Archived') DEFAULT 'Active',
    closure_date DATETIME,
    final_report_generated TINYINT DEFAULT 0,
    final_report_document_id VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add compatibility columns if needed (MySQL 8+ supports IF NOT EXISTS on ADD COLUMN)
ALTER TABLE project_performance_monitoring_closure 
    ADD COLUMN IF NOT EXISTS monitoring_status VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS on_time_delivery_rate INT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS cost_performance_index DECIMAL(10,2) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS remarks TEXT DEFAULT NULL;

-- End of migration
