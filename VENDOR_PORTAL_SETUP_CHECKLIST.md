# Vendor Portal - Quick Setup Checklist

## Pre-Setup Requirements
- [ ] XAMPP/Apache running
- [ ] MySQL database running
- [ ] User logged into the system
- [ ] Access to phpMyAdmin or MySQL client

## Setup Steps

### 1. Database Setup (Required First)
- [ ] Open MySQL command line or phpMyAdmin
- [ ] Select database: `logcap1`
- [ ] Execute SQL file: `vendor_portal_tables.sql`
  - Location: `c:\xampp\htdocs\caplog1\vendor_portal_tables.sql`
  - Or copy-paste the SQL commands manually

**Verify tables created:**
```
SHOW TABLES LIKE 'vendor_%';
```

Should show:
- vendor_portal_registration
- vendor_validation_checklist
- vendor_verification
- vendor_requirements
- vendor_ratings

### 2. File Placement Verification
Check all files are in correct locations:

```
✓ /pages/vendor_portal.php
✓ /scripts/vendor_portal.js
✓ /api/vendor_portal.php
✓ /vendor_portal_tables.sql
✓ /VENDOR_PORTAL_GUIDE.md
```

### 3. Database Connection Verification
- [ ] Check `/api/db.php` has correct credentials
  - Host: localhost
  - Database: logcap1
  - Username: root
  - Password: (empty or your password)

### 4. Access & Test

**Method 1: Direct URL**
```
http://localhost/caplog1/pages/vendor_portal.php
```

**Method 2: Via Dashboard Navigation**
- Add link to sidebar menu (optional)
- Location: `/layout/adminLayout.php`

### 5. Basic Functionality Test

- [ ] Login to system
- [ ] Navigate to Vendor Portal page
- [ ] **Vendors Tab:**
  - [ ] Click "Register New Vendor"
  - [ ] Fill in vendor form
  - [ ] Save vendor
  - [ ] Verify vendor appears in table
  - [ ] Click View/Edit/Delete buttons

- [ ] **Validation Tab:**
  - [ ] Select vendor from dropdown
  - [ ] Click "Edit Validation"
  - [ ] Check some validation items
  - [ ] Save validation
  - [ ] Verify data saved

- [ ] **Verification Tab:**
  - [ ] Select vendor from dropdown
  - [ ] Verify types show in dropdown
  - [ ] Click Edit on any verification
  - [ ] Modify and save
  - [ ] Verify data persists

- [ ] **Requirements Tab:**
  - [ ] Select vendor from dropdown
  - [ ] Click Edit on any requirement
  - [ ] Modify and save
  - [ ] Verify expiry dates display

### 6. Search & Filter Test

- [ ] **Vendors Tab:**
  - [ ] Enter search text in vendor search
  - [ ] Change status filter
  - [ ] Click Apply
  - [ ] Verify results filtered correctly
  - [ ] Click Clear

- [ ] **Other Tabs:**
  - [ ] Test vendor dropdown filters
  - [ ] Test type filters
  - [ ] Verify data updates correctly

### 7. Error Handling Test

- [ ] Try registering vendor with missing fields
  - Should show: "All fields are required"
  
- [ ] Try registering with duplicate email
  - Should show: "Email already exists"

- [ ] Try deleting a vendor
  - Should show confirmation dialog
  - Should show success message

### 8. Browser Console Check

- [ ] Open browser DevTools (F12)
- [ ] Go to Console tab
- [ ] Perform vendor operations
- [ ] Verify no JavaScript errors appear

### 9. Network Tab Verification

- [ ] Open browser DevTools (F12)
- [ ] Go to Network tab
- [ ] Perform any CRUD operation
- [ ] Verify API calls show 200/201 status
- [ ] Check response contains valid JSON

---

## Common Issues & Solutions

### Issue: "Unauthorized" error
**Solution:** 
- Ensure you're logged in
- Check session is not expired
- Login again

### Issue: Tables not found
**Solution:**
- Run SQL import again
- Check database name is correct
- Verify MySQL user has privileges

### Issue: Forms not submitting
**Solution:**
- Check console for errors
- Verify all required fields filled
- Clear browser cache and reload

### Issue: Data not saving
**Solution:**
- Check API endpoint `/api/vendor_portal.php` exists
- Verify database connection
- Check MySQL user privileges
- Review error messages in console

### Issue: Dropdowns empty
**Solution:**
- Create at least one vendor first
- Refresh the page
- Check browser console for errors

---

## Performance Optimization Tips

1. **Database Indexing** - Already included in SQL
2. **Pagination** - Consider adding if vendor count exceeds 500
3. **Caching** - Can be added for vendor dropdown lists
4. **Search Optimization** - Full-text search can be enabled

---

## Customization Options

### Add New Vendor Status
Edit `vendor_portal.php`:
```php
<option value="YourStatus">YourStatus</option>
```

Update database:
```sql
ALTER TABLE vendor_portal_registration 
MODIFY status ENUM('Draft', 'Submitted', 'Under Review', 'Approved', 'Rejected', 'Inactive', 'YourStatus', 'Archived');
```

### Add New Verification Type
Add option to `verification_type_select` in modals and update database:
```sql
ALTER TABLE vendor_verification 
MODIFY verification_type ENUM('Email', 'Phone', 'Address', 'Business', 'Financial', 'Compliance', 'References', 'YourType');
```

### Change Color Scheme
Edit `vendor_portal.js` functions:
- `getStatusBadgeClass()`
- `getValidationStatusClass()`
- `getVerificationStatusClass()`
- `getRequirementStatusClass()`

---

## Integration with Other Modules

### Connect with Procurement Module
The Vendor Portal can be integrated with:
- Procurement Planning
- Purchase Management
- Supplier Evaluation
- Supplier Relationship

### Add Vendor Portal Link to Dashboard
Edit `/pages/dashboard.php` to add:
```html
<a href="vendor_portal.php" class="...">Vendor Portal</a>
```

### Link from Supplier Pages
Reference vendor ID from supplier_identification table when available.

---

## Backup & Maintenance

### Regular Backup
```sql
BACKUP TABLE vendor_portal_registration, vendor_validation_checklist, vendor_verification, vendor_requirements;
```

### Clean Up Archived Records (Optional)
```sql
DELETE FROM vendor_portal_registration WHERE status = 'Archived' AND DATE_SUB(NOW(), INTERVAL 1 YEAR) > updated_at;
```

### Check Database Integrity
```sql
CHECK TABLE vendor_portal_registration;
REPAIR TABLE vendor_portal_registration;
```

---

## Testing Checklist

### Unit Tests
- [ ] Vendor CRUD operations
- [ ] Validation checklist updates
- [ ] Verification tracking
- [ ] Requirements management

### Integration Tests
- [ ] Form submission to database
- [ ] Search and filter functionality
- [ ] Modal operations
- [ ] Error handling

### UI/UX Tests
- [ ] Responsive layout on mobile
- [ ] Accessibility of forms
- [ ] Toast notifications visibility
- [ ] Button clicks working

### Security Tests
- [ ] SQL injection prevention (try: `'; DROP TABLE--`)
- [ ] Session authentication
- [ ] Unauthorized access prevention
- [ ] CSRF protection (if implemented)

---

## Support Resources

- **Documentation:** `/VENDOR_PORTAL_GUIDE.md`
- **Database Schema:** `/vendor_portal_tables.sql`
- **Frontend:** `/pages/vendor_portal.php`
- **API Endpoint:** `/api/vendor_portal.php`
- **JavaScript Handler:** `/scripts/vendor_portal.js`

---

## Completion Status

- [x] Database schema created
- [x] Frontend page created
- [x] JavaScript functionality implemented
- [x] API backend created
- [x] Documentation written
- [ ] Testing completed (your turn!)
- [ ] Deployed to production (your turn!)

---

**Ready to begin?** Start with **Step 1: Database Setup** above!

Date Created: January 25, 2026
Last Updated: January 25, 2026
