# ALM Module Quick Reference

## Module Workflow

```
START
  ↓
1. RECEIVING & LOGISTICS INTAKE (asset_receiving_logistics.php)
   - Create Purchase Order reception records
   - Track: PO Number, Received Date, Qty Received, Qty Expected
   
   ↓ (Provides FK link)
   
2. ONBOARDING & REGISTRATION (asset_onboarding_registration.php)
   - Register assets from receiving records
   - Track: Asset Tag, Asset Name, Serial Number, Registration Date
   - REQUIRES: Select Receiving Record from dropdown
   
   ↓ (Provides FK link)
   
3. DEPLOYMENT & OPERATIONAL LIFE (asset_deployment_lifecycle.php)
   - Assign assets to locations/custodians
   - Track: Assigned To, Location, Assignment Date, Status
   - REQUIRES: Select Asset from dropdown
   
   ↓ (Optional, parallel operations possible)
   
4. MAINTENANCE & SERVICING (asset_maintenance_servicing.php)
   - Schedule and track maintenance
   - Track: Work Order #, Scheduled Date, Technician, Status
   - REQUIRES: Select Asset from dropdown
   
   ↓ (Optional, parallel operations possible)
   
5. END-OF-LIFE & DISPOSAL (asset_end_of_life_disposal.php)
   - Request and track asset disposal
   - Track: Disposal Request Date, Method, Approval, Proceeds
   - REQUIRES: Select Asset from dropdown
   
END
```

## Key Features

### ✅ Dynamic Dropdowns
- All modules auto-populate dropdowns from parent tables
- Shows meaningful labels (e.g., "TAG-001 - Laptop (In Inventory)")
- No manual ID entry needed

### ✅ Foreign Key Validation
- Backend checks references exist before INSERT/UPDATE
- Returns friendly error messages with instructions
- Prevents database constraint violations

### ✅ Toast Notifications
- Replaced all alert() dialogs with Toastify
- Green success toasts
- Red error toasts with actionable messages
- 3-5 second auto-dismiss

### ✅ Client-Side Validation
- Required field checks
- Dropdown selection validation
- Pre-submission validation before API call

### ✅ Better Error Messages
- "Asset ID does not exist. Please onboard the asset first..."
- "Receiving Record ID does not exist. Please create a Receiving Record first..."
- "Please select a Receiving Record"
- "Asset is required"

## Testing Tips

### Test Case 1: Complete Happy Path
1. Receiving & Logistics: Create record "PO-001"
2. Onboarding: Create asset "TAG-001", select PO-001
3. Deployment: Create deployment for TAG-001
4. Maintenance: Create maintenance for TAG-001
5. Disposal: Create disposal for TAG-001
✅ All should succeed with green toasts

### Test Case 2: Invalid Asset FK
1. Try to create Deployment with non-existent asset
2. Submit form
❌ Red toast: "Error: Asset ID does not exist..."

### Test Case 3: Invalid Receiving FK
1. Try to create Onboarding with non-existent receiving record
2. Submit form
❌ Red toast: "Error: Receiving Record ID does not exist..."

### Test Case 4: Missing Required Field
1. Open any module, leave required dropdown empty
2. Try to submit
❌ Red toast: "Asset is required" or "Receiving Record is required"

## File Locations

### Backend APIs
- `/api/asset_receiving_logistics.php`
- `/api/asset_onboarding_registration.php`
- `/api/asset_deployment_lifecycle.php`
- `/api/asset_maintenance_servicing.php`
- `/api/asset_end_of_life_disposal.php`

### Frontend Pages
- `/pages/asset_receiving_logistics.php`
- `/pages/asset_onboarding_registration.php`
- `/pages/asset_deployment_lifecycle.php`
- `/pages/asset_maintenance_servicing.php`
- `/pages/asset_end_of_life_disposal.php`

### JavaScript Controllers
- `/scripts/asset_receiving_logistics.js`
- `/scripts/asset_onboarding_registration.js`
- `/scripts/asset_deployment_lifecycle.js`
- `/scripts/asset_maintenance_servicing.js`
- `/scripts/asset_end_of_life_disposal.js`

## Database Schema

All tables have proper foreign keys:
- `asset_onboarding_registration.receiving_id` → `asset_receiving_logistics.id`
- `asset_deployment_lifecycle.asset_id` → `asset_onboarding_registration.id`
- `asset_maintenance_servicing.asset_id` → `asset_onboarding_registration.id`
- `asset_end_of_life_disposal.asset_id` → `asset_onboarding_registration.id`

All with `ON DELETE CASCADE` for data integrity.

## Support

If you encounter any errors:

1. **"Asset ID does not exist"** → Create the asset in Onboarding first
2. **"Receiving Record ID does not exist"** → Create a Receiving record first
3. **"[Field] is required"** → Fill in all required dropdown/text fields
4. **"Error saving"** → Check browser console (F12) for detailed error
5. **Empty dropdown** → Refresh the page to reload parent records

## Database Dump

Check `ALM_DATABASE_SCHEMA.sql` for full schema and sample data insert statements.
