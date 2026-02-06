# REQUEST ASSET SUBMODULE - QUICK REFERENCE CARD

## 📊 Data Model Overview

```
Asset Management Module (NEW)
├── Request Asset Submodule
│   ├── asset_requests (Main table)
│   ├── asset_request_items (Line items)
│   ├── asset_request_to_procurement (Bridge to Procurement)
│   └── asset_request_audit_log (Audit trail)
│
↓ Flow to Procurement Module
│
Procurement Module (EXISTING)
└── Request Supplies Submodule
    └── procurement_requests table
```

## 🚀 Quick Setup (Copy & Paste)

### Step 1: Create All Tables at Once
```sql
-- Execute this entire block in your MySQL/PhpMyAdmin
SOURCE C:/xampp/htdocs/newcaplog1/sql/REQUEST_ASSET_MODULE.sql;
```

### Step 2: Verify Tables Created
```sql
SHOW TABLES LIKE 'asset_%';
```

Expected output:
```
asset_request_audit_log
asset_request_items
asset_request_to_procurement
asset_requests
```

## 📋 Sample Data Queries

### Insert a New Asset Request
```sql
INSERT INTO asset_requests 
(request_id, requester_id, requester_name, requester_department, status, priority)
VALUES 
('AR-001', 1, 'John Smith', 'IT', 'Pending Approval', 'High');
```

### Add Items to the Request
```sql
INSERT INTO asset_request_items 
(asset_request_id, item_sequence, asset_description, quantity, department, urgency)
VALUES 
(1, 1, 'Laptop', 5, 'IT', 'High');
```

### Approve a Request
```sql
UPDATE asset_requests 
SET status = 'Approved', approval_date = NOW(), approved_by = 'Admin User'
WHERE request_id = 'AR-001';
```

### Get All Pending Requests
```sql
SELECT request_id, requester_name, total_items, priority, status
FROM asset_requests
WHERE status = 'Pending Approval'
ORDER BY priority DESC, request_date ASC;
```

## 📌 Table Structure Summary

### asset_requests
- **Primary Key**: `id`
- **Unique**: `request_id` (AR-001, AR-002, etc.)
- **Status Values**: Pending Approval, Approved, Rejected, In Process, Completed, Archived
- **Indexed**: requester_id, status, request_date, priority

### asset_request_items
- **Primary Key**: `id`
- **Foreign Key**: `asset_request_id` → asset_requests(id)
- **Status Values**: Pending, Approved, Rejected, In Process, Delivered
- **Indexed**: asset_request_id, item_status

### asset_request_to_procurement
- **Links**: asset_requests ↔ procurement_requests
- **Purpose**: Track which assets are being procured
- **Indexed**: asset_request_id, procurement_request_id

### asset_request_audit_log
- **Foreign Key**: `asset_request_id` → asset_requests(id)
- **Tracks**: All changes to requests with who/when/what

## 🔄 Status Flow

```
┌──────────────────────────────────┐
│   Request Created                │
│   status = 'Pending Approval'    │
└─────────────┬──────────────────┘
              │
    ┌─────────┴─────────┐
    ↓                   ↓
┌──────────┐      ┌──────────┐
│ APPROVED │      │ REJECTED │
│ status = │      │ status = │
│ 'Approved'      │ 'Rejected'
└─────┬────┘      └──────────┘
      │
      ↓ (Send to Procurement)
┌──────────────────────────────────┐
│   In Process                     │
│   status = 'In Process'          │
│   → Creates procurement_requests │
└─────────────┬──────────────────┘
              │
              ↓
┌──────────────────────────────────┐
│   Completed                      │
│   status = 'Completed'           │
└──────────────────────────────────┘
```

## 🔑 Key Fields

| Table | Key Field | Purpose |
|-------|-----------|---------|
| asset_requests | request_id | Unique identifier (AR-001) |
| asset_requests | status | Workflow tracking |
| asset_requests | priority | Urgency indicator |
| asset_request_items | asset_description | What is being requested |
| asset_request_items | urgency | Item-level priority |
| asset_request_to_procurement | procurement_request_id | Links to Procurement module |

## 📊 Example Data Structure

### Request AR-001 (From your sample)
```
Request: AR-001
├── Requester: John Smith (IT Department)
├── Status: Pending Approval
├── Priority: High
└── Items:
    └── Item 1: Laptop
        ├── Quantity: 5
        ├── Department: IT
        ├── Urgency: High
        ├── Est. Cost: $50,000
        └── Status: Pending
```

### Request AR-002 (From your sample)
```
Request: AR-002
├── Requester: Maria Garcia (HR Department)
├── Status: Approved (Approval Date: TODAY)
├── Priority: Medium
└── Items:
    └── Item 1: Office Chairs
        ├── Quantity: 10
        ├── Department: HR
        ├── Urgency: Medium
        ├── Est. Cost: $25,000
        └── Status: Approved
```

### Request AR-003 (From your sample)
```
Request: AR-003
├── Requester: Robert Chen (Finance Department)
├── Status: In Process (Sent to Procurement)
├── Priority: Low
└── Items:
    └── Item 1: Software License
        ├── Quantity: 1
        ├── Department: Finance
        ├── Urgency: Low
        ├── Est. Cost: $15,000
        └── Status: In Process
```

## 🛠️ Useful SQL Patterns

### Pattern 1: Get All Requests with Item Details
```sql
SELECT ar.request_id, ar.requester_name, ar.status, 
       ari.asset_description, ari.quantity, ari.urgency
FROM asset_requests ar
LEFT JOIN asset_request_items ari ON ar.id = ari.asset_request_id
ORDER BY ar.request_date DESC;
```

### Pattern 2: Get Requests Ready for Procurement
```sql
SELECT ar.request_id, ar.requester_name, COUNT(ari.id) as item_count
FROM asset_requests ar
JOIN asset_request_items ari ON ar.id = ari.asset_request_id
WHERE ar.status = 'Approved'
GROUP BY ar.id, ar.request_id, ar.requester_name;
```

### Pattern 3: Track Item Status
```sql
SELECT ar.request_id, ari.asset_description, ari.item_status, ari.urgency
FROM asset_request_items ari
JOIN asset_requests ar ON ari.asset_request_id = ar.id
WHERE ari.item_status IN ('In Process', 'Pending')
ORDER BY ari.urgency DESC;
```

### Pattern 4: Get Audit Trail for Specific Request
```sql
SELECT action, action_by, action_date, new_value
FROM asset_request_audit_log
WHERE asset_request_id = 1
ORDER BY action_date DESC;
```

## 💾 Backup Important Commands

### Backup Tables
```sql
-- Export asset_requests
SELECT * INTO OUTFILE '/tmp/asset_requests_backup.csv'
FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n'
FROM asset_requests;
```

### Restore from Backup
```sql
LOAD DATA INFILE '/tmp/asset_requests_backup.csv'
INTO TABLE asset_requests
FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\n';
```

## 🔗 Integration Points

### With Procurement Module:
1. **Table**: `procurement_requests` (existing)
2. **Bridge**: `asset_request_to_procurement`
3. **Trigger**: When status = 'Approved', can send to procurement
4. **Auto Create**: `sp_send_to_procurement` procedure

### With Audit System:
1. **Table**: `asset_request_audit_log`
2. **Tracks**: All CRUD operations
3. **Who**: Stores user who made change
4. **When**: Stores timestamp of change

### With Users/Authentication:
1. **Links to**: User ID (requester_id)
2. **Links to**: User name (requester_name, approved_by, action_by)

## ✅ Implementation Checklist

- [ ] Run SQL script to create tables
- [ ] Verify tables exist: `SHOW TABLES LIKE 'asset_%'`
- [ ] Insert sample data from SQL script
- [ ] Test views: `SELECT * FROM vw_asset_requests_summary`
- [ ] Create PHP backend for CRUD operations
- [ ] Create frontend UI forms
- [ ] Test approval workflow
- [ ] Test sending to procurement
- [ ] Configure email notifications (optional)
- [ ] Set up backup strategy
- [ ] Document in system

## 📞 Support Reference

**SQL File Location**: `C:\xampp\htdocs\newcaplog1\sql\REQUEST_ASSET_MODULE.sql`

**Documentation**: `C:\xampp\htdocs\newcaplog1\REQUEST_ASSET_IMPLEMENTATION.md`

**Tables Created**: 4 main + 1 bridge = 5 tables

**Views Created**: 3 views for common queries

**Stored Procedures**: 5 procedures for operations

---

**Version**: 1.0 | **Date**: February 7, 2026 | **Status**: Ready
