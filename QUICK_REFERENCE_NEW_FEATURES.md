# ⚡ QUICK START GUIDE - NEW FEATURES

## 🚀 5 MINUTE SETUP

### 1. Run Database Migrations (2 minutes)
```bash
# File location:
c:\xampp\htdocs\newcaplog1\DATABASE_MODIFICATIONS.sql

# Steps:
1. Open phpMyAdmin
2. Select database: log1_logisticss1_ecommerce
3. Click "Import"
4. Upload DATABASE_MODIFICATIONS.sql
5. Click "Import"
```

### 2. Verify Files Exist (1 minute)
- ✅ api/auth.php
- ✅ api/auth_helpers.php (NEW)
- ✅ api/archive_management.php (NEW)
- ✅ api/create_contract_reports.php
- ✅ index.php
- ✅ pages/archives.php
- ✅ pages/manage_asset_requests.php
- ✅ scripts/archive_management.js (NEW)
- ✅ scripts/create_contract_reports.js

### 3. Set Admin Users (30 seconds)
In phpMyAdmin SQL tab:
```sql
UPDATE `users` SET `account_type` = 1 WHERE `id` = 3;
UPDATE `users` SET `account_type` = 1 WHERE `id` = 8;
```

### 4. Test Login (2 minutes)
1. Go to http://localhost/newcaplog1
2. Login with: john11@example.com / password
3. OTP modal should appear
4. Enter the 6-digit code shown
5. Should see dashboard

---

## 🎯 FEATURE QUICK ACCESS

### Feature 1: OTP Authentication
**Status**: ✅ Active on all logins  
**Users**: Everyone requires OTP after password  
**Expiry**: 10 minutes  
**Location**: See login page → index.php

### Feature 2: Archive System
**Status**: ✅ Ready to use  
**Access**: http://localhost/newcaplog1/pages/archives.php  
**How to Archive**: Click "Archive" button instead of Delete  
**Restore**: Go to Archives page → Click "Restore"

### Feature 3: Admin Controls
**Status**: ✅ Active  
**Admin Users**: ID 3, 8 (set account_type = 1)  
**Restricted Pages**:
- /pages/manage_asset_requests.php (admins only)
- /api/asset_requests_admin.php (admins only)

### Feature 4: Report Password
**Status**: ✅ Active  
**Location**: Pages → Create Contract and Reports  
**Action**: Click "Generate Report" → Enter password → Download

---

## 🔒 USER ROLES

### Admin User (account_type = 1)
```
✅ Can approve/deny requests
✅ Can archive items
✅ Can restore from archives
✅ Can download reports (with password)
✅ Can manage users
✅ Access to admin dashboard
```

### Regular User (account_type = 0)
```
✅ Can create requests
❌ Cannot approve/deny
✅ Can archive own items
✅ Can download reports (with password)
❌ Cannot access admin pages
```

---

## 📋 API ENDPOINTS

### Authentication
```
POST /api/auth.php
  - action: "login" or "verify_otp"
  - For login: email, password
  - For OTP: action, otp_code
```

### Archives
```
GET  /api/archive_management.php?action=list
GET  /api/archive_management.php?id=1
POST /api/archive_management.php (archive item)
PUT  /api/archive_management.php (restore item)
DELETE /api/archive_management.php (permanent delete)
```

### Reports
```
GET /api/create_contract_reports.php?action=report&password=xxx
  - Requires password verification
```

---

## 🧪 QUICK TESTS

### Test 1: OTP Login
```
1. Open http://localhost/newcaplog1
2. Email: john11@example.com
3. Password: (whatever it is)
4. OTP: (will show in response)
5. Expected: Redirect to dashboard
```

### Test 2: Archive System
```
1. Create a contract in Create Contract page
2. Click "Delete" button → Should say "Archive"
3. Go to Archives Management page
4. Find archived item
5. Click "Restore" to bring back
```

### Test 3: Admin Access
```
1. Login as admin (ID 3 or 8)
2. Go to: Pages → Manage Asset Requests
3. Should work fine
4. Logout and login as regular user
5. Try same page
6. Should see "Access Denied" message
```

### Test 4: Report Password
```
1. Go to Pages → Create Contract and Reports
2. Create test contract
3. Click "Generate Report"
4. Password modal appears
5. Enter correct password
6. CSV should download
7. Try with wrong password
8. Should show error
```

---

## 🎨 VISUAL OVERVIEW

### Login Flow
```
┌─────────────┐
│   Login     │
│  Email/Pass │
└──────┬──────┘
       │ Valid
       ▼
┌─────────────┐
│ OTP Modal   │
│ (6 digits)  │
└──────┬──────┘
       │ Valid
       ▼
┌─────────────┐
│  Dashboard  │
└─────────────┘
```

### Archive Flow
```
┌─────────────┐
│   Item      │
│ (Contract)  │
└──────┬──────┘
       │ Archive
       ▼
┌─────────────────┐
│ archived_items  │
│    (JSON)       │
└──────┬──────────┘
       │
    ┌──┴──┐
    │     │
    ▼     ▼
┌──────┐ ┌──────────┐
│Delete│ │ Restore  │
└──────┘ └──────────┘
```

### Admin Check Flow
```
┌──────────────────┐
│  Check Session   │
│  account_type?   │
└────────┬─────────┘
      ┌──┴──┐
      │     │
      ▼     ▼
    1(✅)  0(❌)
      │     │
      ▼     ▼
   Allow  Deny 403
```

---

## 📞 NEED HELP?

### OTP Issues?
- Check: api/auth.php
- Table: user_otps
- Column: is_otp_enabled

### Archive Issues?
- Check: api/archive_management.php
- Table: archived_items
- Page: pages/archives.php

### Admin Issues?
- Check: api/auth_helpers.php
- Column: users.account_type
- Value: Must be 1 (not 0)

### Report Issues?
- Check: api/create_contract_reports.php
- Line: Password verification section
- Test: Try with correct password

---

## ⚙️ CONFIGURATION

### OTP Settings (in auth.php)
```php
// Expiration time
$expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Max attempts
if ($_SESSION['otp_attempts'] >= 3) {
    // Session timeout
}

// OTP length
$otp_code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
```

### Archive Settings (in archive_management.php)
```php
// Archive types (can extend)
archive_type: 'contract', 'supplier', 'request', 'purchase', 'document'

// Restore allowed
restore_allowed: 1 (default true)
```

---

## 🔄 WORKFLOW EXAMPLES

### Workflow 1: Admin Approves Request
```
1. Regular user creates asset request
2. Request shown as "Pending Approval"
3. Admin logs in → Manage Asset Requests
4. Admin clicks "Approve"
5. Request status changes to "Approved"
6. User notified (can add email here)
```

### Workflow 2: Archive Old Contract
```
1. Admin goes to Create Contract page
2. Finds old contract from 2024
3. Clicks "Archive" button (not Delete)
4. Enters reason: "Contract expired"
5. Item moved to Archives
6. Can still view in Archives page
7. Can restore if needed later
```

### Workflow 3: Download Report Securely
```
1. Admin wants monthly report
2. Clicks "Generate Report"
3. Selects date range (optional)
4. Clicks "Generate Report" button
5. Password modal appears
6. Enters account password
7. Backend verifies password
8. CSV file downloads
9. Log audit entry (can add)
```

---

## ✨ FEATURES AT A GLANCE

| Feature | Status | Location | Users |
|---------|--------|----------|-------|
| OTP Login | ✅ Active | index.php | All |
| Archive Items | ✅ Active | pages/archives.php | All |
| Admin Controls | ✅ Active | api/auth_helpers.php | Admins |
| Report Password | ✅ Active | api/create_contract_reports.php | All |
| User Types | ✅ Active | users.account_type | All |

---

**Last Updated**: February 7, 2026  
**Status**: Ready for Production ✅
