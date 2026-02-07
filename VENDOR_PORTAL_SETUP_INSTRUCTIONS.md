# VENDOR PORTAL DATABASE SETUP INSTRUCTIONS

## Step 1: Open phpMyAdmin
1. Go to http://localhost/phpmyadmin
2. Login with your XAMPP credentials (usually root, no password)

## Step 2: Create Database (if not exists)
1. Click on "New" on the left sidebar
2. Type database name: `logcap1`
3. Click "Create"

## Step 3: Import SQL File
1. Click on the `logcap1` database
2. Click on "Import" tab
3. Click "Choose File" and select: **VENDOR_PORTAL_COMPLETE_DATABASE.sql**
4. Click "Import" button

## Step 4: Verify Tables Created
You should see these 5 tables in the logcap1 database:
- vendor_portal_registration
- vendor_validation_checklist
- vendor_verification
- vendor_requirements
- vendor_ratings

## Step 5: Test the Application
1. Open http://localhost/caplog1/pages/vendor_portal.php
2. Click "Add Vendor" button
3. Fill in the form fields:
   - **Vendor Name** (required)
   - **Vendor Type** (required) - Choose: Supplier, Contractor, Service Provider, or Distributor
   - **Company Name** (required)
   - **Contact Person** (required)
   - **Email** (required)
   - **Phone** (required)
   - Address (optional)
   - Status (defaults to Pending)
4. Click "Save Vendor"
5. Test other tabs: Validation, Verification, Requirements

## Database Fields Explanation

### Vendor Portal Registration Table
- **vendor_name**: Name of the vendor
- **vendor_type**: Type of vendor (Supplier, Contractor, Service Provider, Distributor)
- **company_name**: Official company name
- **contact_person**: Primary contact person
- **email**: Email address (unique)
- **phone**: Phone number
- **address**: Business address
- **status**: Current status (Draft, Submitted, Under Review, Approved, Rejected, Inactive, Archived)

### Sample Data
The SQL file includes sample vendor data. You can delete it if you prefer a clean start by removing the INSERT statements.

## Troubleshooting

### Error: "Table doesn't exist"
- Make sure you imported the SQL file correctly
- Verify you're using the correct database name: `logcap1`
- Check that all tables exist in phpMyAdmin

### Error: "Duplicate entry for email"
- Each vendor must have a unique email
- Try using a different email address

### Form Won't Submit
- Make sure all required fields are filled (marked with *)
- Check browser console (F12) for JavaScript errors
- Verify the API file exists: /api/vendor_portal.php

## File Locations
- SQL File: `/VENDOR_PORTAL_COMPLETE_DATABASE.sql`
- HTML Form: `/pages/vendor_portal.php`
- JavaScript: `/scripts/vendor_portal.js`
- API Backend: `/api/vendor_portal.php`
