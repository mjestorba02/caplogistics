# Vendor Portal - Complete Setup Guide

## Overview
The Vendor Portal is a comprehensive system for managing supplier registration, validation, verification, and requirements tracking. It provides an integrated interface for handling the complete vendor lifecycle management within the e-commerce logistics platform.

## Components Created

### 1. **Database Schema** (`vendor_portal_tables.sql`)
Located at: `/vendor_portal_tables.sql`

**Tables Created:**

#### `vendor_portal_registration`
Core vendor information table with fields:
- **Vendor Information:** vendor_name, company_name, contact_person, email, phone
- **Address Details:** address, city, state_province, country, postal_code
- **Business Details:** business_type, annual_revenue, employees_count, years_in_business
- **Compliance:** tax_id, registration_number, website_url
- **Status Tracking:** status (Draft, Submitted, Under Review, Approved, Rejected, Inactive, Archived)
- **Audit Trail:** created_at, updated_at, submitted_date, reviewed_by, reviewed_date

#### `vendor_validation_checklist`
Tracks validation requirements completion:
- business_license_verified
- tax_compliance_verified
- financial_statements_verified
- references_checked
- insurance_documents_verified
- compliance_documents_verified
- background_check_done
- validation_status (Pending, In Progress, Approved, Failed, Incomplete)
- validation_notes, validation_date, validated_by

#### `vendor_verification`
Tracks different types of vendor verification:
- verification_type (Email, Phone, Address, Business, Financial, Compliance, References)
- verification_status (Pending, In Progress, Verified, Failed, Expired)
- verification_code, verification_date, verified_by
- evidence_document, verification_notes

#### `vendor_requirements`
Manages vendor requirements and compliance items:
- requirement_type (Certification, Insurance, Compliance, Quality Standard, Technical, Financial, Legal)
- requirement_name, requirement_description
- is_mandatory (boolean)
- requirement_status (Not Started, In Progress, Submitted, Approved, Rejected, Expired)
- document_url, submission_date, approval_date, expires_date
- approved_by, requirement_notes

#### `vendor_ratings`
Optional ratings and feedback system:
- overall_rating, quality_rating, reliability_rating, communication_rating, pricing_rating
- comments, rating_date, rating_by

### 2. **Frontend Page** (`pages/vendor_portal.php`)
Main user interface with 4 tabs:

#### **Vendors Tab**
- View all registered vendors
- Search by vendor name or email
- Filter by status (Draft, Submitted, Under Review, Approved, Rejected, Inactive)
- Actions: View details, Edit, Delete
- Register new vendor button

**Registration Form Fields:**
- Basic Info: vendor_name, company_name, contact_person, email, phone
- Business Type: Manufacturer, Distributor, Retailer, Service Provider, Wholesaler
- Address: address, city, state, country, postal_code
- Business Details: tax_id, registration_number, annual_revenue, employees_count, years_in_business
- Contact: website_url

#### **Validation Tab**
- View validation checklist for each vendor
- Track 7 validation items with checkboxes
- Set validation status and notes
- Card-based view with validation progress indicators

#### **Verification Tab**
- Track different verification types
- Filter by vendor and verification type
- Manage verification status and evidence
- Table view with edit and delete actions

#### **Requirements Tab**
- Manage vendor requirements
- Filter by vendor and requirement type
- Track requirement status and expiry dates
- Mark as mandatory or optional
- Upload document links

### 3. **JavaScript Handler** (`scripts/vendor_portal.js`)
Comprehensive client-side logic:

**Features:**
- Tab switching functionality
- Modal management for all CRUD operations
- Form submission with validation
- API communication with backend
- Real-time filtering and search
- Status badge color coding
- Toast notifications for user feedback
- Vendor dropdown auto-loading

**Key Functions:**
- `loadVendors()` - Fetch and display vendors
- `openModal() / closeModal()` - Modal management
- `viewVendorDetails()` - Display full vendor information
- `editVendor()` - Load vendor for editing
- `loadValidations()`, `loadVerifications()`, `loadRequirements()` - Data loading for each tab
- Dynamic status class assignment for visual feedback

### 4. **Backend API** (`api/vendor_portal.php`)
RESTful API endpoint handling all vendor portal operations:

**GET Endpoints:**
- `/api/vendor_portal.php?action=get_vendors` - List all vendors with search/filter
- `/api/vendor_portal.php?action=get_vendor_details&id=X` - Get vendor details
- `/api/vendor_portal.php?action=get_validations` - List validations
- `/api/vendor_portal.php?action=get_validation&id=X` - Get single validation
- `/api/vendor_portal.php?action=get_verifications` - List verifications
- `/api/vendor_portal.php?action=get_verification&id=X` - Get single verification
- `/api/vendor_portal.php?action=get_requirements` - List requirements
- `/api/vendor_portal.php?action=get_requirement&id=X` - Get single requirement

**POST Endpoints:**
- Create/Update Vendor: POST with vendor_name, company_name, etc.
- `?action=save_validation` - Save validation checklist
- `?action=save_verification` - Add new verification
- `?action=save_requirement` - Add new requirement

**PUT Endpoints:**
- `?id=X` - Update vendor information

**DELETE Endpoints:**
- `?id=X` - Delete vendor
- `?action=delete_verification&id=X` - Delete verification
- `?action=delete_requirement&id=X` - Delete requirement

---

## Setup Instructions

### Step 1: Import Database Tables
Execute the SQL file to create all necessary tables:

```bash
mysql -u root -p logcap1 < vendor_portal_tables.sql
```

Or manually run the SQL queries in your database management tool (phpMyAdmin, etc.)

### Step 2: Verify File Structure
Ensure all files are in the correct locations:

```
caplog1/
├── pages/
│   └── vendor_portal.php
├── scripts/
│   └── vendor_portal.js
├── api/
│   └── vendor_portal.php
├── vendor_portal_tables.sql
└── layout/
    └── adminLayout.php (existing)
```

### Step 3: Access the Vendor Portal
Navigate to: `https://log1.imarketph.com/pages/vendor_portal.php`

You must be logged in to access the portal (session-based authentication).

---

## Usage Guide

### Registering a New Vendor

1. Click **"+ Register New Vendor"** button
2. Fill in the registration form:
   - Required fields: Vendor Name, Company Name, Contact Person, Email, Phone, Business Type
   - Optional fields: Address details, Tax ID, Financial information, etc.
3. Click **"Save Vendor"**
4. Vendor is created with **Draft** status
5. Vendor can be submitted, reviewed, and approved through the system

### Managing Validation

1. Go to **Validation** tab
2. Click **"Edit Validation"** on any vendor card
3. Check/uncheck validation items:
   - Business License
   - Tax Compliance
   - Financial Statements
   - References
   - Insurance Documents
   - Compliance Documents
   - Background Check
4. Set validation status (Pending, In Progress, Approved, Failed, Incomplete)
5. Add validation notes if needed
6. Click **"Save Validation"**

### Managing Verification

1. Go to **Verification** tab
2. Select vendor and verification type from filters, OR
3. Click edit button on any verification record
4. Set verification details:
   - Type (Email, Phone, Address, Business, Financial, Compliance, References)
   - Status (Pending, In Progress, Verified, Failed, Expired)
   - Notes and evidence
5. Click **"Save Verification"**

### Managing Requirements

1. Go to **Requirements** tab
2. Click edit button on any requirement, OR
3. Fill requirement form:
   - Type (Certification, Insurance, Compliance, Quality Standard, Technical, Financial, Legal)
   - Name and description
   - Mark as mandatory if required
   - Set status and expiry date
4. Click **"Save Requirement"**

---

## Status Definitions

### Vendor Status
- **Draft:** Vendor registered but not yet submitted for review
- **Submitted:** Vendor profile submitted for review
- **Under Review:** Admin is reviewing the vendor application
- **Approved:** Vendor has been approved and can be used
- **Rejected:** Vendor application was rejected
- **Inactive:** Vendor is temporarily inactive
- **Archived:** Vendor records archived for historical purposes

### Validation Status
- **Pending:** Validation not yet started
- **In Progress:** Validation is being performed
- **Approved:** All validation items completed successfully
- **Failed:** Validation failed, vendor does not meet requirements
- **Incomplete:** Some validation items remain unchecked

### Verification Status
- **Pending:** Verification not yet performed
- **In Progress:** Verification is in progress
- **Verified:** Verification completed successfully
- **Failed:** Verification failed
- **Expired:** Verification has expired and needs renewal

### Requirement Status
- **Not Started:** Requirement not yet addressed
- **In Progress:** Vendor is working on the requirement
- **Submitted:** Vendor has submitted the requirement
- **Approved:** Requirement approved by admin
- **Rejected:** Requirement rejected and needs resubmission
- **Expired:** Requirement has expired

---

## Color Coding

The system uses color-coded badges for quick visual identification:

| Status | Color |
|--------|-------|
| Draft, Inactive, Not Started | Gray |
| Under Review, In Progress, Submitted | Blue/Yellow |
| Approved, Verified, Active | Green |
| Rejected, Failed, Expired | Red |

---

## API Response Format

All API responses follow a standard JSON format:

**Success Response:**
```json
{
  "status": "success",
  "message": "Operation completed",
  "data": { /* response data */ }
}
```

**Error Response:**
```json
{
  "status": "error",
  "message": "Error description"
}
```

---

## Features Implemented

✅ **Vendor Registration**
- Complete vendor profile management
- Business information tracking
- Contact and compliance details

✅ **Validation Management**
- 7-item validation checklist
- Status tracking (Pending → Approved/Failed)
- Notes and audit trail

✅ **Verification Tracking**
- Multiple verification types
- Document/evidence storage links
- Verification date tracking

✅ **Requirements Management**
- Flexible requirement types
- Mandatory/optional flags
- Expiry date tracking
- Document URL storage

✅ **Search & Filter**
- Full-text search by vendor name, email, company
- Status-based filtering
- Tab-specific filtering options

✅ **Responsive Design**
- Mobile-friendly interface
- Tailwind CSS styling
- Tab-based organization

✅ **User Feedback**
- Toast notifications for actions
- Real-time data updates
- Confirmation dialogs for destructive actions

---

## Security Features

✅ **Session Authentication**
- All endpoints require authenticated session
- Unauthorized requests return 401 error

✅ **SQL Injection Prevention**
- Prepared statements for all queries
- Input sanitization with `sanitize_like()` function
- Parameter binding in all SQL operations

✅ **Data Validation**
- Required field validation on frontend and backend
- Email format validation
- Proper status enum validation

---

## Future Enhancements

Consider implementing:
- Email notifications for status changes
- Vendor self-service portal
- Document upload functionality
- Rating and feedback system (foundation already in place)
- Approval workflow automation
- Compliance dashboard and reports
- Vendor scorecard system
- Integration with procurement module

---

## Troubleshooting

### Vendors not loading?
1. Check database connection in `/api/db.php`
2. Verify `vendor_portal_registration` table exists
3. Check browser console for JavaScript errors
4. Verify you're logged in (check `$_SESSION['id']`)

### Form not submitting?
1. Check that required fields are filled
2. Verify API endpoint is accessible
3. Check network tab in browser developer tools
4. Look for validation error messages

### Modal not appearing?
1. Ensure JavaScript file (`vendor_portal.js`) is loaded
2. Check for jQuery conflicts if present
3. Verify modal HTML is in the page

### API errors?
1. Check `/api/vendor_portal.php` file exists
2. Verify database credentials in `db.php`
3. Check PHP error logs
4. Ensure session is active

---

## Support & Maintenance

For issues or questions:
1. Check database integrity with `ANALYZE TABLE vendor_portal_registration;`
2. Review API logs in PHP error log
3. Check browser console for JavaScript errors
4. Verify all files are properly uploaded to server

---

**Created:** January 25, 2026
**Version:** 1.0
**Status:** Ready for Production

