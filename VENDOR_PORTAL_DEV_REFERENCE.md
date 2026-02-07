# Vendor Portal - Developer Quick Reference

## File Structure
```
caplog1/
├── pages/
│   └── vendor_portal.php (Main UI)
├── scripts/
│   └── vendor_portal.js (Client Logic)
├── api/
│   └── vendor_portal.php (REST API)
├── vendor_portal_tables.sql (Database Schema)
├── VENDOR_PORTAL_GUIDE.md (Complete Guide)
├── VENDOR_PORTAL_SETUP_CHECKLIST.md (Setup Steps)
└── VENDOR_PORTAL_DEV_REFERENCE.md (This File)
```

---

## Database Tables Reference

### vendor_portal_registration
**Primary vendor data**
```
id (PK) | vendor_name | company_name | contact_person | email (UQ) | phone
address | city | state_province | country | postal_code | tax_id | registration_number
business_type | annual_revenue | employees_count | website_url | years_in_business
status | registration_date | submitted_date | reviewed_by | reviewed_date | rejection_reason
created_at | updated_at
```

### vendor_validation_checklist (1-to-1 with vendor_portal_registration)
**Validation tracking**
```
id (PK) | vendor_id (FK) | business_license_verified (bool)
tax_compliance_verified | financial_statements_verified | references_checked
insurance_documents_verified | compliance_documents_verified | background_check_done
validation_status | validation_date | validated_by | validation_notes
created_at | updated_at
```

### vendor_verification (1-to-many with vendor_portal_registration)
**Multiple verification records per vendor**
```
id (PK) | vendor_id (FK) | verification_type
verification_status | verification_code | verification_date | verified_by
evidence_document | verification_notes | created_at | updated_at
```

### vendor_requirements (1-to-many with vendor_portal_registration)
**Multiple requirements per vendor**
```
id (PK) | vendor_id (FK) | requirement_type | requirement_name | requirement_description
is_mandatory | requirement_status | document_url | submission_date | approval_date
expires_date | approved_by | requirement_notes | created_at | updated_at
```

### vendor_ratings (Optional)
**Vendor feedback and ratings**
```
id (PK) | vendor_id (FK) | rating_by | rating_date
overall_rating | quality_rating | reliability_rating | communication_rating | pricing_rating
comments | created_at | updated_at
```

---

## API Endpoints Cheat Sheet

### Vendor Endpoints

| Method | Endpoint | Params | Purpose |
|--------|----------|--------|---------|
| GET | `?action=get_vendors` | search, status, limit | List vendors |
| GET | `?action=get_vendor_details&id=X` | id | Get single vendor |
| POST | (default) | vendor_name, company_name, ... | Create vendor |
| PUT | `?id=X` | Same as POST | Update vendor |
| DELETE | `?id=X` | id | Delete vendor |

### Validation Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `?action=get_validations` | List all validations |
| GET | `?action=get_validation&id=X` | Get single validation |
| POST | `?action=save_validation` | Save/Update validation |

### Verification Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `?action=get_verifications` | List verifications |
| GET | `?action=get_verification&id=X` | Get single verification |
| POST | `?action=save_verification` | Create verification |
| DELETE | `?action=delete_verification&id=X` | Delete verification |

### Requirements Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `?action=get_requirements` | List requirements |
| GET | `?action=get_requirement&id=X` | Get single requirement |
| POST | `?action=save_requirement` | Create requirement |
| DELETE | `?action=delete_requirement&id=X` | Delete requirement |

---

## JavaScript Key Functions

### Modal Management
```javascript
openModal(modal)              // Show modal
closeModal(modal)             // Hide modal
```

### Data Loading
```javascript
loadVendors(search, status)   // Load vendors with filters
loadValidations(vendorId, status)
loadVerifications(vendorId, type)
loadRequirements(vendorId, type)
loadVendorDropdowns()         // Populate dropdowns
```

### CRUD Operations
```javascript
window.viewVendorDetails(vendorId)
window.editVendor(vendorId)
window.deleteVendor(vendorId)
window.editValidation(validationId)
window.editVerification(verificationId)
window.deleteVerification(verificationId)
window.editRequirement(requirementId)
window.deleteRequirement(requirementId)
```

### Utilities
```javascript
showToast(message, type)      // Show notification
getStatusBadgeClass(status)   // Get CSS class for status
getValidationStatusClass()
getVerificationStatusClass()
getRequirementStatusClass()
```

---

## Form Field Mappings

### Registration Form (vendor_portal_registration)
| HTML ID | Database Column | Type | Required |
|---------|-----------------|------|----------|
| vendor_name | vendor_name | VARCHAR(150) | Yes |
| company_name | company_name | VARCHAR(150) | Yes |
| contact_person | contact_person | VARCHAR(100) | Yes |
| email | email | VARCHAR(150) | Yes |
| phone | phone | VARCHAR(20) | Yes |
| address | address | VARCHAR(255) | No |
| city | city | VARCHAR(100) | No |
| state_province | state_province | VARCHAR(100) | No |
| country | country | VARCHAR(100) | No |
| postal_code | postal_code | VARCHAR(20) | No |
| tax_id | tax_id | VARCHAR(50) | No |
| registration_number | registration_number | VARCHAR(100) | No |
| business_type | business_type | ENUM | Yes |
| annual_revenue | annual_revenue | DECIMAL(15,2) | No |
| employees_count | employees_count | INT | No |
| website_url | website_url | VARCHAR(255) | No |
| years_in_business | years_in_business | INT | No |

---

## Enums & Constants

### Business Types
```
'Manufacturer'
'Distributor'
'Retailer'
'Service Provider'
'Wholesaler'
```

### Vendor Status
```
'Draft'
'Submitted'
'Under Review'
'Approved'
'Rejected'
'Inactive'
'Archived'
```

### Verification Types
```
'Email'
'Phone'
'Address'
'Business'
'Financial'
'Compliance'
'References'
```

### Verification Status
```
'Pending'
'In Progress'
'Verified'
'Failed'
'Expired'
```

### Requirement Types
```
'Certification'
'Insurance'
'Compliance'
'Quality Standard'
'Technical'
'Financial'
'Legal'
```

### Requirement Status
```
'Not Started'
'In Progress'
'Submitted'
'Approved'
'Rejected'
'Expired'
```

### Validation Status
```
'Pending'
'In Progress'
'Approved'
'Failed'
'Incomplete'
```

---

## Common Queries

### Get Approved Vendors
```sql
SELECT * FROM vendor_portal_registration WHERE status = 'Approved';
```

### Get Pending Validations
```sql
SELECT * FROM vendor_validation_checklist WHERE validation_status = 'Pending';
```

### Get Vendors with Missing Validations
```sql
SELECT v.* FROM vendor_portal_registration v
LEFT JOIN vendor_validation_checklist vc ON v.id = vc.vendor_id
WHERE vc.id IS NULL;
```

### Get Expiring Requirements
```sql
SELECT * FROM vendor_requirements 
WHERE DATE(expires_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY);
```

### Get Vendor Summary with Counts
```sql
SELECT 
    vp.id,
    vp.vendor_name,
    COUNT(DISTINCT vv.id) as verification_count,
    COUNT(DISTINCT vr.id) as requirement_count,
    vvc.validation_status
FROM vendor_portal_registration vp
LEFT JOIN vendor_verification vv ON vp.id = vv.vendor_id
LEFT JOIN vendor_requirements vr ON vp.id = vr.vendor_id
LEFT JOIN vendor_validation_checklist vvc ON vp.id = vvc.vendor_id
GROUP BY vp.id;
```

---

## Security Notes

### SQL Injection Prevention
- All queries use prepared statements
- `sanitize_like()` function escapes wildcards
- Parameter binding in all operations

### Session Protection
- Check `$_SESSION['id']` on all endpoints
- Return 401 for unauthenticated requests
- No direct SQL from user input

### Data Validation
- Frontend: HTML5 validation + JavaScript
- Backend: Type checking + field validation
- Enum validation for all status fields

---

## Performance Considerations

### Indexes Created
- vendor_name
- email
- status
- vendor_id (in related tables)
- verification_type
- requirement_type
- created_at

### Query Optimization
- Use LIMIT for large result sets
- Filter before joining (in WHERE clause)
- Avoid SELECT * when not needed

### Caching Opportunities
- Vendor dropdown list (changes infrequently)
- Status enums (never change)
- Verified vendors list (good for reporting)

---

## Common Modifications

### Add New Vendor Status
1. Add option in HTML dropdown
2. Update ENUM in database
3. Add CSS class in `getStatusBadgeClass()`

### Add New Verification Type
1. Add option in HTML dropdown
2. Update ENUM in database
3. Restart data loading

### Add New Requirement Type
1. Add option in HTML dropdown
2. Update ENUM in database
3. Update filters

### Change Color Scheme
Edit CSS classes in:
- `vendor_portal.php` (Tailwind classes)
- `vendor_portal.js` (getStatusBadgeClass functions)

---

## Debugging Tips

### Enable SQL Logging
Edit `/api/vendor_portal.php`:
```php
error_log(json_encode(['query' => $sql, 'params' => $params]));
```

### Check API Responses
Browser DevTools → Network tab → API call → Response

### Validate Database Connection
```php
var_dump($conn);
```

### Monitor JavaScript Errors
Browser DevTools → Console tab

### Test API Directly
```bash
curl -X GET "https://log1.imarketph.com/api/vendor_portal.php?action=get_vendors"
```

---

## Testing Scenarios

### New Vendor Workflow
1. Register vendor (Draft)
2. Validate vendor details
3. Run validation checklist
4. Add verification records
5. Add requirements
6. Submit for review
7. Admin approves
8. Status = Approved

### Filter & Search Tests
- Search for partial vendor name
- Search for email domain
- Filter by multiple status values
- Combine filters with search

### Edge Cases
- Long vendor names (truncation)
- Special characters in notes
- Very large revenue amounts
- Past dates for expiry
- Empty search results

---

## Related Modules Integration

### With Procurement
- Link vendor to PO creation
- Pull vendor data into procurement forms

### With Supplier Evaluation
- Use vendor data as baseline
- Reference vendor status

### With Assets
- Link vendor to asset source
- Track vendor performance on assets

---

## Version Info
- **Created:** January 25, 2026
- **Version:** 1.0
- **Status:** Production Ready
- **Framework:** Vanilla PHP, JavaScript
- **Database:** MySQL
- **UI Framework:** Tailwind CSS

---

## Quick Command Reference

### MySQL
```sql
-- Create tables
SOURCE vendor_portal_tables.sql;

-- View table structure
DESC vendor_portal_registration;

-- Count records
SELECT COUNT(*) FROM vendor_portal_registration;

-- See indexes
SHOW INDEX FROM vendor_portal_registration;
```

### API Testing
```bash
# Test endpoint exists
curl https://log1.imarketph.com/api/vendor_portal.php

# Get vendors
curl "https://log1.imarketph.com/api/vendor_portal.php?action=get_vendors"

# Get with filter
curl "https://log1.imarketph.com/api/vendor_portal.php?action=get_vendors&status=Approved"
```

---

**For detailed information, refer to `/VENDOR_PORTAL_GUIDE.md`**
