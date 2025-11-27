-- ═════════════════════════════════════════════════════════════════════════════════
-- PROJECT LOGISTICS TRACKER (PLT) - DATABASE SCHEMA
-- Database: logcap1
-- ═════════════════════════════════════════════════════════════════════════════════

-- ─────────────────────────────────────────────────────────────────────────────────
-- MODULE 1: PROJECT REQUIREMENT PLANNING
-- ─────────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS project_requirement_planning (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id VARCHAR(50) UNIQUE NOT NULL,
    project_name VARCHAR(255) NOT NULL,
    project_description TEXT,
    project_scope TEXT,
    start_date DATE,
    end_date DATE,
    total_budget DECIMAL(15,2),
    logistics_scope ENUM('Procurement', 'Transport', 'Delivery', 'Installation', 'Multi-Phase') DEFAULT 'Multi-Phase',
    project_status ENUM('Planning', 'Active', 'On Hold', 'Completed', 'Cancelled') DEFAULT 'Planning',
    assigned_vehicles INT,
    assigned_warehouses INT,
    assigned_personnel INT,
    project_manager_id INT,
    project_manager_name VARCHAR(255),
    logistics_coordinator_id INT,
    logistics_coordinator_name VARCHAR(255),
    risk_level ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    milestone_count INT DEFAULT 0,
    total_milestones_completed INT DEFAULT 0,
    budget_consumed DECIMAL(15,2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_project_id (project_id),
    INDEX idx_project_status (project_status),
    INDEX idx_start_date (start_date),
    INDEX idx_logistics_scope (logistics_scope),
    INDEX idx_risk_level (risk_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────────
-- MODULE 2: PROCUREMENT & SUPPLIER COORDINATION
-- ─────────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS procurement_supplier_coordination (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coordination_id VARCHAR(50) UNIQUE NOT NULL,
    project_id VARCHAR(50) NOT NULL,
    supplier_id INT,
    supplier_name VARCHAR(255) NOT NULL,
    po_number VARCHAR(50),
    contract_id VARCHAR(50),
    milestone_id INT,
    milestone_name VARCHAR(255),
    supplier_contact_person VARCHAR(255),
    supplier_phone VARCHAR(20),
    supplier_email VARCHAR(255),
    delivery_date DATE,
    expected_delivery_date DATE,
    status ENUM('Assigned', 'In Progress', 'Ready for Pickup', 'Pickup Complete', 'Delayed', 'Completed') DEFAULT 'Assigned',
    quality_certification ENUM('Pending', 'Certified', 'Failed', 'Conditional') DEFAULT 'Pending',
    readiness_score INT,
    production_progress INT DEFAULT 0,
    estimated_value DECIMAL(15,2),
    inspection_status ENUM('Not Started', 'In Progress', 'Passed', 'Failed') DEFAULT 'Not Started',
    risk_flags TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_coordination_id (coordination_id),
    INDEX idx_project_id (project_id),
    INDEX idx_supplier_name (supplier_name),
    INDEX idx_status (status),
    INDEX idx_delivery_date (delivery_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────────
-- MODULE 3: SHIPMENT SCHEDULING & ROUTE PLANNING
-- ─────────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS shipment_scheduling_route_planning (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id VARCHAR(50) UNIQUE NOT NULL,
    project_id VARCHAR(50) NOT NULL,
    origin_location VARCHAR(255) NOT NULL,
    destination_location VARCHAR(255) NOT NULL,
    transport_mode ENUM('Sea', 'Air', 'Land', 'Rail', 'Multi-Modal') DEFAULT 'Land',
    scheduled_departure DATE,
    scheduled_arrival DATE,
    carrier_name VARCHAR(255),
    carrier_type ENUM('Ocean Freight', 'Air Freight', 'Trucking', 'Rail') DEFAULT 'Trucking',
    carrier_reliability_score INT,
    route_distance_km INT,
    estimated_transit_time_days INT,
    total_cost DECIMAL(15,2),
    carrier_assignment_status ENUM('Available', 'Assigned', 'Confirmed', 'In Transit') DEFAULT 'Available',
    risk_assessment TEXT,
    weather_risk ENUM('Low', 'Medium', 'High') DEFAULT 'Low',
    customs_risk ENUM('Low', 'Medium', 'High') DEFAULT 'Low',
    permit_risk ENUM('Low', 'Medium', 'High') DEFAULT 'Low',
    route_optimization_score INT,
    shipment_status ENUM('Scheduled', 'Confirmed', 'In Transit', 'Delayed', 'Delivered', 'Cancelled') DEFAULT 'Scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_shipment_id (shipment_id),
    INDEX idx_project_id (project_id),
    INDEX idx_transport_mode (transport_mode),
    INDEX idx_carrier_name (carrier_name),
    INDEX idx_shipment_status (shipment_status),
    INDEX idx_scheduled_departure (scheduled_departure)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────────
-- MODULE 4: EXECUTION & REAL-TIME TRACKING
-- ─────────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS execution_realtime_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id VARCHAR(50) UNIQUE NOT NULL,
    shipment_id VARCHAR(50) NOT NULL,
    project_id VARCHAR(50) NOT NULL,
    dispatch_date DATETIME,
    dispatch_location VARCHAR(255),
    dispatch_officer_id INT,
    dispatch_officer_name VARCHAR(255),
    load_compliance_status ENUM('Compliant', 'Non-Compliant', 'Pending Verification') DEFAULT 'Pending Verification',
    safety_documentation_complete TINYINT DEFAULT 0,
    gps_device_id VARCHAR(50),
    current_location VARCHAR(255),
    last_location_update DATETIME,
    current_latitude DECIMAL(10,8),
    current_longitude DECIMAL(11,8),
    speed_kmh DECIMAL(5,2),
    vehicle_condition ENUM('Good', 'Fair', 'Poor', 'Emergency') DEFAULT 'Good',
    exception_alerts TEXT,
    delay_reason VARCHAR(500),
    damage_reported TINYINT DEFAULT 0,
    damage_description TEXT,
    route_deviation_detected TINYINT DEFAULT 0,
    deviation_details TEXT,
    container_status ENUM('Sealed', 'Open', 'Compromised') DEFAULT 'Sealed',
    iot_sensor_reading DECIMAL(10,2),
    temperature_reading DECIMAL(5,2),
    humidity_reading DECIMAL(5,2),
    tracking_status ENUM('In Transit', 'Stopped', 'Delayed', 'Alert', 'Delivered') DEFAULT 'In Transit',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_tracking_id (tracking_id),
    INDEX idx_shipment_id (shipment_id),
    INDEX idx_project_id (project_id),
    INDEX idx_tracking_status (tracking_status),
    INDEX idx_last_location_update (last_location_update)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────────
-- MODULE 5: CUSTOMS & REGULATORY COMPLIANCE
-- ─────────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS customs_regulatory_compliance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compliance_id VARCHAR(50) UNIQUE NOT NULL,
    shipment_id VARCHAR(50) NOT NULL,
    project_id VARCHAR(50) NOT NULL,
    customs_declaration_id VARCHAR(100),
    hs_code VARCHAR(20),
    commodity_description TEXT,
    declared_value DECIMAL(15,2),
    country_of_origin VARCHAR(100),
    country_of_destination VARCHAR(100),
    customs_authority VARCHAR(255),
    declaration_status ENUM('Draft', 'Submitted', 'Approved', 'Rejected', 'Pending') DEFAULT 'Draft',
    customs_clearance_status ENUM('Pending', 'In Review', 'Cleared', 'On Hold', 'Failed') DEFAULT 'Pending',
    clearance_date DATETIME,
    permits_required TEXT,
    permits_obtained TINYINT DEFAULT 0,
    permit_expiry_date DATE,
    compliance_documentation VARCHAR(500),
    regulatory_issues TEXT,
    risk_flags TEXT,
    escalation_required TINYINT DEFAULT 0,
    escalation_reason VARCHAR(500),
    compliance_officer_id INT,
    compliance_officer_name VARCHAR(255),
    compliance_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_compliance_id (compliance_id),
    INDEX idx_shipment_id (shipment_id),
    INDEX idx_project_id (project_id),
    INDEX idx_declaration_status (declaration_status),
    INDEX idx_customs_clearance_status (customs_clearance_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────────
-- MODULE 6: DELIVERY & SITE COORDINATION
-- ─────────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS delivery_site_coordination (
    id INT AUTO_INCREMENT PRIMARY KEY,
    delivery_id VARCHAR(50) UNIQUE NOT NULL,
    shipment_id VARCHAR(50) NOT NULL,
    project_id VARCHAR(50) NOT NULL,
    site_location VARCHAR(255) NOT NULL,
    site_contact_person VARCHAR(255),
    site_contact_phone VARCHAR(20),
    planned_delivery_date DATE,
    actual_delivery_date DATE,
    site_readiness_status ENUM('Not Ready', 'Partially Ready', 'Ready', 'Confirmed') DEFAULT 'Not Ready',
    site_preparation_checklist TEXT,
    required_equipment_available TINYINT DEFAULT 0,
    available_manpower INT,
    site_access_cleared TINYINT DEFAULT 0,
    delivery_confirmation_status ENUM('Pending', 'In Progress', 'Confirmed', 'Completed') DEFAULT 'Pending',
    pod_document_id VARCHAR(100),
    pod_date DATETIME,
    pod_signature_collected TINYINT DEFAULT 0,
    recipient_name VARCHAR(255),
    recipient_title VARCHAR(100),
    items_received_count INT,
    items_damaged INT DEFAULT 0,
    items_missing INT DEFAULT 0,
    condition_assessment TEXT,
    installation_required TINYINT DEFAULT 0,
    installation_status ENUM('Not Started', 'In Progress', 'Completed', 'Failed') DEFAULT 'Not Started',
    installation_completion_date DATETIME,
    handover_completed TINYINT DEFAULT 0,
    handover_date DATETIME,
    delivery_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_delivery_id (delivery_id),
    INDEX idx_shipment_id (shipment_id),
    INDEX idx_project_id (project_id),
    INDEX idx_planned_delivery_date (planned_delivery_date),
    INDEX idx_site_readiness_status (site_readiness_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────────
-- MODULE 7: PROJECT PERFORMANCE MONITORING & CLOSURE
-- ─────────────────────────────────────────────────────────────────────────────────

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
    performance_status ENUM('On Track', 'At Risk', 'Off Track', 'Critical') DEFAULT 'On Track',
    variance_explanation TEXT,
    corrective_actions_taken TEXT,
    improvement_suggestions TEXT,
    project_closure_status ENUM('Active', 'In Review', 'Closed', 'Archived') DEFAULT 'Active',
    closure_date DATETIME,
    final_report_generated TINYINT DEFAULT 0,
    final_report_document_id VARCHAR(100),
    lessons_learned TEXT,
    compliance_documentation_archived TINYINT DEFAULT 0,
    archive_location VARCHAR(255),
    project_approved_for_closure TINYINT DEFAULT 0,
    approved_by_id INT,
    approved_by_name VARCHAR(255),
    approval_date DATETIME,
    total_project_cost DECIMAL(15,2),
    actual_vs_planned_analysis TEXT,
    recommendations_for_future TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_performance_id (performance_id),
    INDEX idx_project_id (project_id),
    INDEX idx_performance_status (performance_status),
    INDEX idx_project_closure_status (project_closure_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═════════════════════════════════════════════════════════════════════════════════
-- SAMPLE DATA
-- ═════════════════════════════════════════════════════════════════════════════════

-- Sample Project Requirement Planning
INSERT INTO project_requirement_planning (project_id, project_name, project_description, project_scope, start_date, end_date, total_budget, logistics_scope, project_status, assigned_vehicles, assigned_warehouses, assigned_personnel, project_manager_id, project_manager_name, logistics_coordinator_id, logistics_coordinator_name, risk_level, milestone_count, notes)
VALUES
('PROJ-2024-001', 'Manufacturing Plant Setup', 'Complete logistics for new manufacturing facility', 'Procurement, transport, installation', '2024-01-15', '2024-06-30', 500000.00, 'Multi-Phase', 'Active', 15, 3, 25, 1, 'Robert Smith', 2, 'Lisa Chen', 'Medium', 5, 'On-track for Q2 completion'),
('PROJ-2024-002', 'Infrastructure Development', 'Highway infrastructure delivery project', 'Transport, delivery, installation', '2024-02-01', '2024-12-31', 1200000.00, 'Multi-Phase', 'Planning', 20, 5, 40, 3, 'James Wilson', 4, 'Sarah Johnson', 'High', 8, 'Critical path optimization needed'),
('PROJ-2024-003', 'Distribution Network Expansion', 'Expansion of distribution centers', 'Procurement, warehouse setup', '2024-03-10', '2024-09-15', 350000.00, 'Multi-Phase', 'Active', 10, 2, 15, 1, 'Robert Smith', 2, 'Lisa Chen', 'Low', 4, 'Smooth progress');

-- Sample Procurement & Supplier Coordination
INSERT INTO procurement_supplier_coordination (coordination_id, project_id, supplier_id, supplier_name, po_number, contract_id, milestone_id, milestone_name, supplier_contact_person, supplier_phone, supplier_email, delivery_date, expected_delivery_date, status, quality_certification, readiness_score, production_progress, estimated_value, inspection_status, notes)
VALUES
('COORD-001', 'PROJ-2024-001', 1, 'ABC Manufacturing', 'PO-2024-001', 'C-2024-001', 1, 'Equipment Procurement', 'John Lee', '+1-555-0101', 'john@abc.com', '2024-02-15', '2024-02-20', 'In Progress', 'Certified', 85, 75, 150000.00, 'In Progress', 'On schedule'),
('COORD-002', 'PROJ-2024-001', 2, 'Global Components Ltd', 'PO-2024-002', 'C-2024-002', 2, 'Parts Supply', 'Maria Garcia', '+1-555-0102', 'maria@globalcomp.com', '2024-03-01', '2024-03-05', 'Ready for Pickup', 'Certified', 90, 100, 120000.00, 'Passed', 'Quality verified'),
('COORD-003', 'PROJ-2024-002', 3, 'Infrastructure Supplies Co', 'PO-2024-003', 'C-2024-003', 3, 'Heavy Equipment', 'David Kumar', '+1-555-0103', 'david@infrasup.com', NULL, '2024-04-10', 'Assigned', 'Pending', 70, 45, 200000.00, 'Not Started', 'Early stage');

-- Sample Shipment Scheduling & Route Planning
INSERT INTO shipment_scheduling_route_planning (shipment_id, project_id, origin_location, destination_location, transport_mode, scheduled_departure, scheduled_arrival, carrier_name, carrier_type, carrier_reliability_score, route_distance_km, estimated_transit_time_days, total_cost, carrier_assignment_status, risk_assessment, weather_risk, customs_risk, permit_risk, route_optimization_score, shipment_status, notes)
VALUES
('SHIP-001', 'PROJ-2024-001', 'Beijing, China', 'Los Angeles, USA', 'Sea', '2024-02-10', '2024-03-15', 'Pacific Shipping Lines', 'Ocean Freight', 92, 12000, 35, 45000.00, 'Confirmed', 'Low risk route', 'Low', 'Low', 'Low', 88, 'Confirmed', 'Container ship scheduled'),
('SHIP-002', 'PROJ-2024-001', 'Los Angeles, USA', 'Chicago, USA', 'Land', '2024-03-20', '2024-03-27', 'TransAmerica Logistics', 'Trucking', 85, 2200, 3, 5500.00, 'Assigned', 'Standard route', 'Medium', 'Low', 'Low', 90, 'Scheduled', 'Optimized trucking route'),
('SHIP-003', 'PROJ-2024-002', 'Rotterdam, Netherlands', 'Hamburg, Germany', 'Land', '2024-04-01', '2024-04-02', 'Euro Transport GmbH', 'Trucking', 88, 450, 1, 2000.00, 'Available', 'Short-haul route', 'Low', 'Low', 'Low', 95, 'Scheduled', 'Direct route');

-- Sample Execution & Real-Time Tracking
INSERT INTO execution_realtime_tracking (tracking_id, shipment_id, project_id, dispatch_date, dispatch_location, dispatch_officer_id, dispatch_officer_name, load_compliance_status, safety_documentation_complete, gps_device_id, current_location, last_location_update, current_latitude, current_longitude, speed_kmh, vehicle_condition, tracking_status, notes)
VALUES
('TRACK-001', 'SHIP-001', 'PROJ-2024-001', '2024-02-10 08:00:00', 'Beijing Port', 1, 'Wei Zhang', 'Compliant', 1, 'GPS-001', 'Pacific Ocean', '2024-02-25 14:30:00', 15.2837, -120.5456, 18.5, 'Good', 'In Transit', 'Container ship enroute'),
('TRACK-002', 'SHIP-002', 'PROJ-2024-001', '2024-03-20 06:00:00', 'Los Angeles Port', 2, 'Mike Johnson', 'Compliant', 1, 'GPS-002', 'Nevada Desert', '2024-03-24 10:15:00', 36.1699, -115.1398, 95.0, 'Good', 'In Transit', 'Truck on schedule'),
('TRACK-003', 'SHIP-003', 'PROJ-2024-002', '2024-04-01 07:00:00', 'Rotterdam Terminal', 3, 'Klaus Mueller', 'Compliant', 1, 'GPS-003', 'Hamburg', '2024-04-02 16:45:00', 53.5511, 10.0119, 0.0, 'Good', 'Delivered', 'Completed delivery');

-- Sample Customs & Regulatory Compliance
INSERT INTO customs_regulatory_compliance (compliance_id, shipment_id, project_id, customs_declaration_id, hs_code, commodity_description, declared_value, country_of_origin, country_of_destination, customs_authority, declaration_status, customs_clearance_status, permits_required, permits_obtained, compliance_officer_id, compliance_officer_name, compliance_notes)
VALUES
('COMPL-001', 'SHIP-001', 'PROJ-2024-001', 'CD-2024-0001', '8403.0090', 'Industrial Machinery', 150000.00, 'China', 'USA', 'US Customs & Border Protection', 'Submitted', 'Cleared', 'Import License', 1, 1, 'Thomas Brown', 'Fast-track clearance approved'),
('COMPL-002', 'SHIP-002', 'PROJ-2024-001', 'CD-2024-0002', '7325.9090', 'Metal Components', 120000.00, 'USA', 'USA', 'State DOT', 'Draft', 'Pending', 'State Permits', 0, 2, 'Amanda Lee', 'Domestic shipment, awaiting state permit'),
('COMPL-003', 'SHIP-003', 'PROJ-2024-002', 'CD-2024-0003', '7308.9000', 'Steel Structures', 200000.00, 'Netherlands', 'Germany', 'German Customs', 'Submitted', 'Approved', 'EU Certificates', 1, 3, 'Franz Hoffman', 'EU internal transfer, smoothly cleared');

-- Sample Delivery & Site Coordination
INSERT INTO delivery_site_coordination (delivery_id, shipment_id, project_id, site_location, site_contact_person, site_contact_phone, planned_delivery_date, actual_delivery_date, site_readiness_status, required_equipment_available, available_manpower, site_access_cleared, delivery_confirmation_status, pod_date, pod_signature_collected, recipient_name, items_received_count, items_damaged, items_missing, installation_required, installation_status, notes)
VALUES
('DEL-001', 'SHIP-001', 'PROJ-2024-001', 'Manufacturing Facility, Chicago', 'Sarah Wilson', '+1-312-555-0001', '2024-03-28', NULL, 'Ready', 1, 15, 1, 'Pending', NULL, 0, NULL, 0, 0, 0, 1, 'Not Started', 'Site ready for delivery'),
('DEL-002', 'SHIP-002', 'PROJ-2024-001', 'Manufacturing Facility, Chicago', 'Sarah Wilson', '+1-312-555-0001', '2024-03-30', '2024-03-30', 'Confirmed', 1, 15, 1, 'Completed', '2024-03-30 14:30:00', 1, 'Sarah Wilson', 25, 0, 0, 1, 'In Progress', 'Delivery completed, installation starting'),
('DEL-003', 'SHIP-003', 'PROJ-2024-002', 'Hamburg Port Terminal', 'Klaus Mueller', '+49-40-555-0001', '2024-04-02', '2024-04-02', 'Confirmed', 1, 8, 1, 'Completed', '2024-04-02 17:00:00', 1, 'Klaus Mueller', 50, 0, 0, 0, 'Completed', 'Successfully delivered and completed');

-- Sample Project Performance Monitoring & Closure
INSERT INTO project_performance_monitoring_closure (performance_id, project_id, reporting_period_start, reporting_period_end, kpi_cost_actual, kpi_cost_planned, kpi_cost_variance, kpi_cost_variance_percent, kpi_delivery_time_actual, kpi_delivery_time_planned, kpi_delivery_time_variance, kpi_milestone_adherence_percent, kpi_quality_score, kpi_customer_satisfaction, performance_status, variance_explanation, corrective_actions_taken, improvement_suggestions, project_closure_status, notes)
VALUES
('PERF-001', 'PROJ-2024-001', '2024-01-15', '2024-03-31', 245000.00, 250000.00, -5000.00, -2.0, 75, 80, -5, 90, 95, 92, 'On Track', 'Slight cost savings due to early supplier discounts', 'Optimized transport routes', 'Consider supplier consolidation for future', 'Active', 'Q1 Performance: Excellent progress'),
('PERF-002', 'PROJ-2024-002', '2024-02-01', '2024-03-31', 450000.00, 400000.00, 50000.00, 12.5, 60, 50, 10, 75, 88, 85, 'At Risk', 'Heavy equipment sourcing took longer', 'Added backup suppliers', 'Pre-source long-lead items earlier', 'Active', 'Q1 Performance: Cost overrun, schedule slip'),
('PERF-003', 'PROJ-2024-003', '2024-03-10', '2024-03-31', 85000.00, 90000.00, -5000.00, -5.6, 22, 25, -3, 95, 96, 94, 'On Track', 'Efficient warehouse setup', 'Streamlined site prep process', 'Document best practices for reuse', 'Active', 'Early stage: Strong performance');

-- ═════════════════════════════════════════════════════════════════════════════════
-- END OF SCHEMA
-- ═════════════════════════════════════════════════════════════════════════════════
