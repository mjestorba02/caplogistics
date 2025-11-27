# PSM Module - Developer Reference Card

## Quick Reference for Developers/Maintainers

---

## 📁 File Locations & Purposes

### Frontend Pages (`pages/`)
```
supplier_identification.php    → Supplier registration & pre-qualification
supplier_evaluation.php        → RFQ management
procurement_planning.php       → Requisition creation (AUTO: REQ#)
po_management.php              → Purchase order creation (AUTO: PO#)
receiving_quality.php          → Goods receipt & QC (AUTO: RCP#)
supplier_relationship.php      → Supplier performance tracking
payment_compliance.php         → Invoice & payment management (AUTO: INV#)
```

### API Endpoints (`api/`)
```
supplier_identification.php    → CRUD for suppliers
supplier_evaluation.php        → CRUD for RFQs
procurement_planning.php       → CRUD for requisitions + AUTO REQ#
po_management.php              → CRUD for POs + AUTO PO#
receiving_quality.php          → CRUD for receipts + AUTO RCP#
supplier_relationship.php      → CRUD for supplier metrics
payment_compliance.php         → CRUD for invoices + AUTO INV#
```

### JavaScript Files (`scripts/`)
```
supplier_identification.js     → Modal, fetch, edit, delete
supplier_evaluation.js         → Modal, fetch, filter
procurement_planning.js        → Modal, auto REQ# handling
po_management.js               → Modal, auto PO# handling
receiving_quality.js           → Modal, auto RCP# handling
supplier_relationship.js       → Modal, fetch, edit
payment_compliance.js          → Modal, auto INV# handling
```

---

## 🔧 Common Development Tasks

### Add a New Dropdown Option

**Scenario:** Add "R&D" department to procurement_planning

1. **Edit the page:** `pages/procurement_planning.php`
2. **Find the select element:** Look for `id="department"`
3. **Add option:**
```html
<option value="R&D">R&D</option>
```

### Change Auto-Number Format

**Scenario:** Change PO format from "PO-0001" to "PO-2024-001"

1. **Edit API:** `api/po_management.php`
2. **Find generation code:**
```php
$po_number = 'PO-' . str_pad($next_num, 4, '0', STR_PAD_LEFT);
```
3. **Modify to:**
```php
$current_year = date('Y');
$po_number = 'PO-' . $current_year . '-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);
```

### Add Email Notification on Invoice Created

**Scenario:** Send email when INV is created

1. **Edit API:** `api/payment_compliance.php`
2. **After successful insert, add:**
```php
// Send email notification
$supplier_email = $input['supplier_email'] ?? '';
if($supplier_email) {
    mail($supplier_email, "Invoice Created: $invoice_number", 
         "Invoice $invoice_number has been created for your PO.");
}
```

### Add New Field to Form

**Scenario:** Add "Tax ID" field to supplier_identification form

1. **Edit page:** `pages/supplier_identification.php`
2. **Add to form:**
```html
<div>
    <label class="block text-gray-700 font-medium">Tax ID</label>
    <input id="tax_id" type="text" class="w-full border rounded px-3 py-2" placeholder="(optional)" />
</div>
```
3. **Edit JavaScript:** `scripts/supplier_identification.js`
4. **Add to data object:**
```javascript
const data = {
    supplier_name: document.getElementById('supplier_name').value,
    // ... other fields ...
    tax_id: document.getElementById('tax_id').value  // ADD THIS
};
```
5. **Edit API:** `api/supplier_identification.php`
6. **Add to POST case:**
```php
$tax_id = $input['tax_id'] ?? '';
// Use in INSERT/UPDATE query
```

---

## 🐛 Debugging Tips

### Modal Not Opening
**Check:**
- Modal element exists: `document.getElementById('modal')`
- Classes are correct: `hidden` and `flex`
- Event listener is attached: `.addEventListener('click', openModal)`

**Fix:**
```javascript
function openModal() {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
```

### Data Not Loading
**Check:**
- Network tab in browser (F12) - see if API returns data
- Console for JavaScript errors
- Session is active (check `$_SESSION['id']`)

**Debug:**
```php
// In API file, add logging
error_log("API called: " . $_SERVER['REQUEST_METHOD']);
error_log("Input: " . json_encode($input));
```

### Auto-Number Not Generating
**Check:**
- Database query works: `SELECT MAX(CAST(SUBSTRING(...`
- Table exists and has records
- API returns number in response

**Debug:**
```php
$result = $conn->query("SELECT MAX(CAST(SUBSTRING(po_number, 4) AS UNSIGNED)) as max_num FROM purchase_orders WHERE po_number LIKE 'PO-%'");
$row = $result->fetch(PDO::FETCH_ASSOC);
error_log("Max number: " . json_encode($row));
```

### Dropdown Options Not Showing
**Check:**
- Select element has correct `id`
- Options have `value` attribute
- HTML syntax is correct
- JavaScript properly references the select

---

## 🚀 Performance Optimization Tips

### Reduce Database Queries
**Before:**
```javascript
// Called for every edit
fetch('../api/supplier_identification.php').then(...);
```

**After:**
```javascript
// Load once on page load
let suppliersCache = [];
document.addEventListener('DOMContentLoaded', () => {
    fetchSuppliers();
    // Reuse cache for edits
});
```

### Cache API Responses
```javascript
const cache = {};
async function fetchData(url) {
    if(cache[url]) return cache[url];
    const response = await fetch(url);
    const data = await response.json();
    cache[url] = data;
    return data;
}
```

### Lazy Load Large Tables
```javascript
// Load only first 20 rows, then load more on scroll
const rowsPerPage = 20;
let currentPage = 1;

function loadMore() {
    currentPage++;
    fetch(`../api/endpoint.php?page=${currentPage}`).then(...);
}
```

---

## 📊 Database Schema Quick Reference

### Key Tables
```
supplier_evaluations
├─ id (PK)
├─ item_description
├─ quantity
├─ budget
├─ suppliers (text)
├─ status
└─ created_at

procurement_requisitions
├─ id (PK)
├─ requisition_number (UNIQUE)
├─ department
├─ description
├─ total_amount
├─ status
└─ created_at

purchase_orders
├─ id (PK)
├─ po_number (UNIQUE)
├─ supplier
├─ description
├─ total_value
├─ due_date
├─ status
└─ created_at

goods_receipts
├─ id (PK)
├─ receipt_number (UNIQUE)
├─ po_number (FK)
├─ quantity_received
├─ quantity_inspected
├─ condition
├─ status
└─ created_at

payment_invoices
├─ id (PK)
├─ invoice_number (UNIQUE)
├─ po_number (FK)
├─ supplier
├─ amount
├─ due_date
├─ status
├─ compliance_notes
└─ created_at
```

---

## 🔍 Testing Checklist

### Before Deploying Changes
- [ ] Page loads without 404 errors
- [ ] Session authentication works
- [ ] Modal opens and closes
- [ ] Form validation works
- [ ] API endpoint returns proper JSON
- [ ] CRUD operations work (Create, Read, Update, Delete)
- [ ] Dropdown options populate correctly
- [ ] Auto-numbers generate in correct format
- [ ] Filter functionality works
- [ ] Edit loads data into form
- [ ] Delete shows confirmation
- [ ] Success notifications appear
- [ ] Error messages are helpful
- [ ] Responsive design works on mobile
- [ ] No console errors (F12 → Console tab)
- [ ] No PHP errors in error log

### Test Data
Use these test records when developing:
```
Supplier: "Test Corp Inc"
Email: "test@testcorp.com"
Department: "IT"
Amount: "5000.00"
Date: "2024-12-31"
```

---

## 📝 Code Snippets

### Reusable Modal Pattern
```php
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative max-h-96 overflow-y-auto">
        <h2 id="modalTitle" class="text-2xl font-bold mb-4">Title</h2>
        <form id="myForm" class="space-y-4">
            <!-- Form fields here -->
        </form>
        <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500">&times;</button>
    </div>
</div>
```

### Reusable Table Pattern
```html
<div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="w-full text-left text-sm">
        <thead class="bg-gray-200 border-b">
            <tr>
                <th class="px-6 py-3">Column1</th>
                <th class="px-6 py-3">Column2</th>
                <th class="px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody id="dataTable"></tbody>
    </table>
    <div id="emptyState" class="hidden text-center py-8 text-gray-600">No data found</div>
</div>
```

### Reusable Fetch Pattern
```javascript
async function fetchData(url, method = 'GET', data = null) {
    try {
        const options = {
            method: method,
            headers: { 'Content-Type': 'application/json' }
        };
        if(data) options.body = JSON.stringify(data);
        
        const response = await fetch(url, options);
        const result = await response.json();
        
        if(result.status === 'success') {
            return result;
        } else {
            throw new Error(result.message || 'Unknown error');
        }
    } catch(error) {
        console.error('Fetch error:', error);
        Toastify({ text: error.message, backgroundColor: '#ef4444' }).showToast();
    }
}
```

---

## ⚡ Quick Fixes

### Fix: CORS Error on API Call
**Cause:** Browser blocking cross-origin request
**Fix:** Make sure API path is correct (should be relative: `../api/...`)

### Fix: 404 on API Call
**Cause:** Wrong API file path
**Fix:** Check that file exists at: `c:\xampp\htdocs\caplog1\api\filename.php`

### Fix: JSON Parse Error
**Cause:** API returning invalid JSON (HTML error page, etc.)
**Fix:** Check API file for syntax errors, use PHP error_log()

### Fix: Modal Stuck Open
**Cause:** Form submission failed
**Fix:** Check browser console for errors, verify data is valid

### Fix: Dropdown Empty
**Cause:** No data in source table or API not returning options
**Fix:** Insert sample data, check API GET endpoint

---

## 🎯 Version Control Notes

### Important Files to Track
- All `/pages/*.php` files
- All `/api/*.php` files
- All `/scripts/*.js` files
- `/layout/adminLayout.php`
- `/psm_tables.sql` (database schema)

### Never Commit to Version Control
- Database credentials
- User session data
- Temporary test files
- Large binary files

---

## 🔗 File Dependencies

### supplier_identification.php depends on:
- `layout/adminLayout.php` (template)
- `api/supplier_identification.php` (data)
- `scripts/supplier_identification.js` (interactivity)

### procurement_planning.php depends on:
- `layout/adminLayout.php` (template)
- `api/procurement_planning.php` (auto REQ#)
- `scripts/procurement_planning.js` (interactivity)

### Similarly for all other modules...

---

## 🎓 Learning Resources

### Key Technologies Used
- PHP 7.2+ with PDO
- MySQL/MariaDB
- JavaScript ES6+
- HTML5 Semantic
- Tailwind CSS
- Toastify.js (notifications)

### Development Environment
- XAMPP/WAMP (local server)
- VS Code or IDE
- MySQL Workbench (optional)
- Postman (for API testing)

---

## 📞 Support & Escalation

### Common Issues → Solutions
| Issue | Solution | File to Check |
|-------|----------|---------------|
| Page won't load | Check session | page.php |
| API 401 error | Session expired | api/file.php |
| Auto-number not generating | DB query issue | api/file.php line 20+ |
| Dropdown empty | No sample data | db.php or insert sample |
| Modal won't close | Event listener issue | scripts/file.js |
| Form won't submit | Validation failing | scripts/file.js |

---

**Last Updated:** 2024
**For:** PSM Module v1.0
**Audience:** Developers/Maintainers
