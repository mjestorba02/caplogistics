# REQUEST ASSET SUBMODULE - Implementation Guide

## Overview
The **Request Asset** submodule is a new feature within the **Asset Management** module that allows departments to request new assets. Once approved, these requests automatically flow to the **Procurement Module** → **Request Supplies** submodule for procurement processing.

## Data Flow Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                      ASSET MANAGEMENT MODULE                    │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │          REQUEST ASSET (NEW SUBMODULE)                   │   │
│  │                                                          │   │
│  │  - Create asset requests                               │   │
│  │  - View request status (Pending/Approved/Rejected)      │   │
│  │  - Manage individual asset items in each request        │   │
│  │  - Request approval workflow                            │   │
│  │  - Track urgency levels (Low/Medium/High)               │   │
│  └──────────────────────────────────────────────────────────┘   │
│                            ↓                                     │
│                     [APPROVED REQUESTS]                          │
│                            ↓                                     │
└─────────────────────────────────────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────────┐
│                   PROCUREMENT MODULE                             │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │         REQUEST SUPPLIES (EXISTING SUBMODULE)            │   │
│  │                                                          │   │
│  │  - Receives approved asset requests                     │   │
│  │  - Manages procurement process                          │   │
│  │  - Links to suppliers                                   │   │
│  │  - Tracks procurement status                            │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

## Database Tables

### 1. `asset_requests` (Main Request Table)
Stores all asset requests from the Asset Management module.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| request_id | VARCHAR(50) | Unique request identifier (e.g., AR-001) |
| requester_id | INT | ID of the user requesting |
| requester_name | VARCHAR(100) | Name of requester |
| requester_department | VARCHAR(100) | Department making the request |
| request_date | DATETIME | When the request was created |
| status | ENUM | Pending Approval / Approved / Rejected / In Process / Completed / Archived |
| total_items | INT | Number of items in this request |
| priority | ENUM | Low / Medium / High |
| notes | TEXT | Additional notes |
| approval_date | DATETIME | When the request was approved |
| approved_by | VARCHAR(100) | Name of approver |
| rejection_reason | TEXT | Reason if rejected |
| archived_at | DATETIME | When archived (if applicable) |

**Sample Data:**
```
| request_id | requester_name | department | status | total_items | priority |
|------------|----------------|------------|--------|-------------|----------|
| AR-001     | John Smith     | IT         | Pending Approval | 1 | High |
| AR-002     | Maria Garcia   | HR         | Approved | 1 | Medium |
| AR-003     | Robert Chen    | Finance    | In Process | 1 | Low |
```

---

### 2. `asset_request_items` (Individual Items in Each Request)
Stores each asset item within a request.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| asset_request_id | INT | References asset_requests(id) |
| item_sequence | INT | Order of items in the request |
| asset_description | VARCHAR(255) | What asset is being requested |
| quantity | INT | How many units needed |
| department | VARCHAR(100) | Department receiving the asset |
| urgency | ENUM | Low / Medium / High |
| estimated_cost | DECIMAL | Estimated cost of the item |
| item_status | ENUM | Pending / Approved / Rejected / In Process / Delivered |
| notes | TEXT | Item-specific notes |

**Sample Data:**
```
| asset_request_id | asset_description | quantity | department | urgency | item_status |
|------------------|-------------------|----------|------------|---------|-------------|
| 1                | Laptop            | 5        | IT         | High    | Pending |
| 2                | Office Chairs     | 10       | HR         | Medium  | Approved |
| 3                | Software License  | 1        | Finance    | Low     | In Process |
```

---

### 3. `asset_request_to_procurement` (Bridge/Linking Table)
Links asset requests to procurement requests for tracking purposes.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| asset_request_id | INT | References asset_requests(id) |
| procurement_request_id | INT | References procurement_requests(id) |
| asset_item_id | INT | References asset_request_items(id) |
| sent_to_procurement_date | DATETIME | When sent to Procurement module |
| procurement_status | VARCHAR(100) | Current procurement status |
| notes | TEXT | Additional notes |

---

### 4. `asset_request_audit_log` (Audit Trail)
Maintains a complete history of all changes to asset requests.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| asset_request_id | INT | References asset_requests(id) |
| action | VARCHAR(100) | Type of action (CREATED, APPROVED, REJECTED, SENT_TO_PROCUREMENT, etc.) |
| action_by | VARCHAR(100) | Who performed the action |
| old_value | TEXT | Previous value (if applicable) |
| new_value | TEXT | New value (if applicable) |
| notes | TEXT | Additional details |
| action_date | TIMESTAMP | When the action occurred |

---

## SQL Command Summary

### Create All Tables
Execute the complete SQL file:
```sql
SOURCE C:\xampp\htdocs\newcaplog1\sql\REQUEST_ASSET_MODULE.sql;
```

Or manually run these key commands:

### Create asset_requests Table
```sql
CREATE TABLE IF NOT EXISTS asset_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id VARCHAR(50) NOT NULL UNIQUE,
    requester_id INT NOT NULL,
    requester_name VARCHAR(100) NOT NULL,
    requester_department VARCHAR(100) NOT NULL,
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending Approval', 'Approved', 'Rejected', 'In Process', 'Completed', 'Archived') DEFAULT 'Pending Approval',
    total_items INT NOT NULL DEFAULT 0,
    priority ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    notes TEXT,
    approval_date DATETIME,
    approved_by VARCHAR(100),
    rejection_reason TEXT,
    archived_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_requester_id (requester_id),
    INDEX idx_status (status),
    INDEX idx_request_date (request_date),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### Create asset_request_items Table
```sql
CREATE TABLE IF NOT EXISTS asset_request_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_request_id INT NOT NULL,
    item_sequence INT NOT NULL,
    asset_description VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    department VARCHAR(100) NOT NULL,
    urgency ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    estimated_cost DECIMAL(12,2),
    item_status ENUM('Pending', 'Approved', 'Rejected', 'In Process', 'Delivered') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_request_id) REFERENCES asset_requests(id) ON DELETE CASCADE,
    INDEX idx_asset_request_id (asset_request_id),
    INDEX idx_item_status (item_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### Create asset_request_to_procurement Table (Bridge)
```sql
CREATE TABLE IF NOT EXISTS asset_request_to_procurement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_request_id INT NOT NULL,
    procurement_request_id INT,
    asset_item_id INT NOT NULL,
    sent_to_procurement_date DATETIME,
    procurement_status VARCHAR(100) DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_request_id) REFERENCES asset_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (procurement_request_id) REFERENCES procurement_requests(id) ON DELETE SET NULL,
    FOREIGN KEY (asset_item_id) REFERENCES asset_request_items(id) ON DELETE CASCADE,
    INDEX idx_asset_request_id (asset_request_id),
    INDEX idx_procurement_request_id (procurement_request_id),
    INDEX idx_sent_date (sent_to_procurement_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### Create asset_request_audit_log Table
```sql
CREATE TABLE IF NOT EXISTS asset_request_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_request_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    action_by VARCHAR(100) NOT NULL,
    old_value TEXT,
    new_value TEXT,
    notes TEXT,
    action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_request_id) REFERENCES asset_requests(id) ON DELETE CASCADE,
    INDEX idx_asset_request_id (asset_request_id),
    INDEX idx_action_date (action_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Sample Queries

### View All Pending Approval Requests
```sql
SELECT * FROM asset_requests 
WHERE status = 'Pending Approval'
ORDER BY priority DESC, request_date ASC;
```

### View All Request Items with Request Summary
```sql
SELECT 
    ar.request_id,
    ar.requester_name,
    ari.asset_description,
    ari.quantity,
    ari.department,
    ari.urgency,
    ar.status
FROM asset_requests ar
JOIN asset_request_items ari ON ar.id = ari.asset_request_id
ORDER BY ar.request_date DESC;
```

### Approve a Request
```sql
UPDATE asset_requests 
SET status = 'Approved', 
    approval_date = NOW(), 
    approved_by = 'Admin User'
WHERE id = 1;
```

### Get All Approved Requests Ready for Procurement
```sql
SELECT * FROM asset_requests 
WHERE status = 'Approved'
ORDER BY priority DESC;
```

### Track Request Movement to Procurement
```sql
SELECT 
    ar.request_id,
    ari.asset_description,
    ari.quantity,
    artp.procurement_request_id,
    artp.sent_to_procurement_date,
    artp.procurement_status
FROM asset_requests ar
JOIN asset_request_items ari ON ar.id = ari.asset_request_id
JOIN asset_request_to_procurement artp ON ari.id = artp.asset_item_id
WHERE ar.status = 'In Process';
```

## Available Views

### vw_asset_requests_summary
Shows all requests with item counts and summary information.

```sql
SELECT * FROM vw_asset_requests_summary;
```

### vw_asset_request_items_detail
Shows all items with their request details.

```sql
SELECT * FROM vw_asset_request_items_detail;
```

### vw_asset_requests_for_procurement
Shows approved requests ready to be sent to procurement.

```sql
SELECT * FROM vw_asset_requests_for_procurement;
```

## Available Stored Procedures

### sp_create_asset_request
Creates a new asset request.

```sql
CALL sp_create_asset_request(
    @requester_id := 1,
    @requester_name := 'John Smith',
    @department := 'IT',
    @priority := 'High',
    @notes := 'Urgent laptop replacement',
    @request_id,
    @status
);
```

### sp_add_asset_request_item
Adds an item to an existing request.

```sql
CALL sp_add_asset_request_item(
    @request_id := 1,
    @asset_description := 'Laptop',
    @quantity := 5,
    @department := 'IT',
    @urgency := 'High',
    @estimated_cost := 50000.00,
    @status
);
```

### sp_approve_asset_request
Approves a complete asset request.

```sql
CALL sp_approve_asset_request(
    @request_id := 1,
    @approved_by := 'Manager Name',
    @status
);
```

### sp_reject_asset_request
Rejects a request with a reason.

```sql
CALL sp_reject_asset_request(
    @request_id := 1,
    @rejected_by := 'Manager Name',
    @rejection_reason := 'Budget not available',
    @status
);
```

### sp_send_to_procurement
Sends an approved asset request to the Procurement Module.

```sql
CALL sp_send_to_procurement(
    @asset_request_id := 1,
    @procurement_request_id,
    @status
);
```

## Status Flow Diagram

```
Request Created
    ↓
Pending Approval
    ↓
    ├─→ [APPROVED] → In Process → [COMPLETED]
    │
    └─→ [REJECTED] → (can be deleted or archived)

Alternative Path:
    ├─→ Archived (can archive at any time)
```

## Key Features

1. **Request ID Auto-Generation**: Format AR-001, AR-002, etc.
2. **Multi-Item Requests**: Each request can contain multiple assets
3. **Department Tracking**: Track which department needs each asset
4. **Urgency Levels**: Low, Medium, High priority indicators
5. **Approval Workflow**: Clear approval/rejection with audit trails
6. **Auto-Linking to Procurement**: Approved requests automatically create procurement requests
7. **Complete Audit Trail**: All actions tracked with who and when
8. **Flexible Status**: Supports the entire lifecycle from request to completion
9. **Cost Estimation**: Track estimated costs per item
10. **Performance Optimized**: Proper indexes for common queries

## Integration with Procurement Module

When an asset request is approved and sent to procurement:

1. **Asset Request** (AR-001) is approved
2. **Stored Procedure** `sp_send_to_procurement` is called
3. **Procurement Request** is automatically created for each item
4. **Bridge Table** (`asset_request_to_procurement`) tracks the relationship
5. **Status** is set to "In Process"
6. Procurement team can now work with the request in **Request Supplies** submodule

## Next Steps

1. Execute the SQL file in your database
2. Create PHP backend files for CRUD operations
3. Create frontend UI for:
   - Creating new asset requests
   - Viewing request status
   - Approving/rejecting requests
   - Sending approved requests to procurement
4. Integrate with existing authentication system
5. Add audit logging for all operations

---

**Module Version**: 1.0  
**Created**: February 7, 2026  
**Status**: Ready for Implementation
