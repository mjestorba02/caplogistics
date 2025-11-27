# Procurement & Sourcing Management (PSM) - Quick User Guide

## Module Overview

The PSM module is organized into 7 easy-to-use submodules, each with auto-generated numbers and smart dropdown selectors to make data entry smooth and error-free.

---

## 🎯 The 7 PSM Submodules

### 1️⃣ **Supplier Identification & Pre-Qualification**
**What it does:** Register and qualify new suppliers

**Key Features:**
- Auto-generates supplier ID
- Multi-select certifications (ISO 9001, ISO 14001, etc.)
- Risk level assessment (Low, Medium, High)
- Optional phone and notes fields
- Filter by certification

**How to Use:**
1. Click "Add Supplier" button
2. Enter supplier name and email (required)
3. Select certifications (hold Ctrl/Cmd for multiple)
4. Choose risk level
5. Add phone/notes if needed
6. Click "Save Supplier"

---

### 2️⃣ **Supplier Evaluation & Selection**
**What it does:** Manage RFQ (Request For Quote) process

**Key Features:**
- Create RFQs with item details
- Track supplier responses
- Filter by status (Pending, Submitted, Selected)
- Budget management

**How to Use:**
1. Click "Create RFQ"
2. Enter item description and quantity
3. Set budget and select suppliers to quote
4. Set status (Pending → Submitted → Selected)
5. Track responses in the table

---

### 3️⃣ **Procurement Planning & Requisition**
**What it does:** Create purchase requisitions

**Key Features:**
- **Auto-generated requisition # (REQ-0001, REQ-0002, etc.)**
- Department selector (IT, HR, Finance, etc.)
- Total amount tracking
- Status workflow (Draft → Submitted → Approved)

**How to Use:**
1. Click "New Requisition"
2. Requisition # appears automatically
3. Select department from dropdown
4. Describe what you need
5. Enter total amount
6. Click "Save Requisition"

**Note:** The REQ # is auto-generated - you don't need to enter it!

---

### 4️⃣ **Purchase Order (PO) Management**
**What it does:** Create and manage purchase orders

**Key Features:**
- **Auto-generated PO # (PO-0001, PO-0002, etc.)**
- Supplier dropdown (existing suppliers)
- Description and amount tracking
- Due date setting
- Status tracking

**How to Use:**
1. Click "Create PO"
2. PO # appears automatically
3. Select supplier from dropdown
4. Add description and amount
5. Set due date
6. Click "Save PO"

**Note:** The PO # is auto-generated - just fill in the details!

---

### 5️⃣ **Receiving & Quality Inspection**
**What it does:** Track goods receipt and quality checks

**Key Features:**
- **Auto-generated Receipt # (RCP-0001, RCP-0002, etc.)**
- Link to existing PO (dropdown)
- Track quantity received vs. inspected
- Condition status (Good, Damaged, Defective)

**How to Use:**
1. Click "Add Receipt"
2. Receipt # appears automatically
3. Select PO from dropdown
4. Enter quantities received and inspected
5. Set condition status
6. Click "Save Receipt"

**Tip:** Use this to track what was actually delivered vs. what was checked

---

### 6️⃣ **Supplier Relationship Management**
**What it does:** Monitor supplier performance

**Key Features:**
- Track supplier metrics
- On-time delivery percentage
- Quality score percentage
- Performance history

**How to Use:**
1. View all suppliers and their performance scores
2. Add/update performance records
3. Monitor trends over time
4. Filter by performance level

---

### 7️⃣ **Payment & Compliance Management**
**What it does:** Manage invoices and payments

**Key Features:**
- **Auto-generated Invoice # (INV-0001, INV-0002, etc.)**
- Link to PO and supplier (dropdowns)
- Amount and due date tracking
- Compliance notes for audit trail
- Payment status tracking

**How to Use:**
1. Click "Create Invoice"
2. Invoice # appears automatically
3. Select PO from dropdown
4. Select supplier from dropdown
5. Enter amount and due date
6. Add compliance notes if needed
7. Click "Save Invoice"

**Tip:** Compliance notes help track why invoice was created or any special conditions

---

## 🎓 Key User Tips

### Auto-Generated Numbers
✨ **All modules use auto-generated numbers:**
- REQ-XXXX (Requisitions)
- PO-XXXX (Purchase Orders)
- RCP-XXXX (Receipts)
- INV-XXXX (Invoices)

**You don't need to enter these - they appear automatically!**

### Dropdowns vs. Text Fields
- **Dropdown Fields:** Click to select from existing options
- **Text Fields:** Type your own value
- **Read-Only Fields:** Show auto-generated numbers (can't edit)
- **Optional Fields:** Marked with "(optional)" - you can skip them

### Required vs. Optional
- **Required fields:** Marked with `*` asterisk
- **Optional fields:** Marked with "(optional)" label
- **If you forget required fields:** You'll see an error message

### Multi-Select Certifications
In Supplier Identification, to select multiple certifications:
1. Click the dropdown
2. Hold **Ctrl** (Windows) or **Cmd** (Mac)
3. Click each certification you want
4. Release and scroll to see selected items

### Filtering Data
All modules support filtering:
1. Use the filter dropdown or search box
2. Click "Apply Filters"
3. Click "Clear" to see all records again

---

## 🔄 Typical Workflow

### Complete Procurement Process

```
1. SUPPLIER IDENTIFICATION
   └─→ Register supplier with certifications

2. SUPPLIER EVALUATION  
   └─→ Send RFQ to suppliers

3. PROCUREMENT PLANNING
   └─→ Create requisition (REQ auto-generated)

4. PO MANAGEMENT
   └─→ Create PO from approved requisition (PO auto-generated)

5. RECEIVING & QUALITY
   └─→ Receive goods and inspect (RCP auto-generated)

6. PAYMENT & COMPLIANCE
   └─→ Create invoice for payment (INV auto-generated)

7. SUPPLIER RELATIONSHIP
   └─→ Track supplier performance for future reference
```

---

## ❌ Common Issues & Solutions

### Issue: Form won't submit
**Solution:** Check that all required fields (marked with *) are filled

### Issue: Dropdown is empty
**Solution:** Add records to the referenced module first (e.g., add suppliers before selecting in PO form)

### Issue: Can't edit auto-generated number
**This is correct!** These numbers are read-only to maintain consistency. The system generates them automatically.

### Issue: Where do I find my saved data?
**Solution:** It appears in the main table below the form. Use filters to find specific records.

---

## 📋 Checklists

### Before Creating a PO
- [ ] Supplier exists in Supplier Identification
- [ ] Supplier has been evaluated (RFQ completed)
- [ ] Requisition is approved
- [ ] Budget is confirmed

### Before Creating an Invoice
- [ ] PO exists and is referenced
- [ ] Goods have been received and inspected
- [ ] Amount matches PO
- [ ] Supplier details are correct

### Before Payment
- [ ] Invoice is recorded
- [ ] Receipt matches invoice amount
- [ ] Quality inspection is complete
- [ ] All compliance notes are filled

---

## 🎯 Best Practices

1. **Use Dropdowns:** Always select from dropdowns instead of typing to avoid typos
2. **Fill Optional Fields:** Notes and phone numbers help future reference
3. **Set Due Dates:** Always include due dates for POs and Invoices
4. **Regular Updates:** Update supplier performance regularly
5. **Use Compliance Notes:** Document any special conditions or notes
6. **Filter by Status:** Use status filters to find pending items
7. **Delete Carefully:** Deleted records can't be recovered - confirm before deleting

---

## 🆘 Need Help?

- **Form validation errors:** Make sure all required fields (*) are filled
- **Missing dropdowns:** Create records in the source module first
- **Want to export data:** Contact your system administrator
- **System issues:** Check your internet connection and try again

---

## 📊 Module Status Colors

**Status Badges Explained:**

- 🟢 **Green:** Approved, Good, Success
- 🟡 **Yellow:** Medium risk, In Progress
- 🔴 **Red:** High risk, Rejected, Error
- 🔵 **Blue:** Information, Default, Pending

---

## ✅ Quick Success Checklist

- [ ] I understand the 7 PSM modules
- [ ] I know auto-generated numbers don't need manual entry
- [ ] I can use dropdowns to select from existing data
- [ ] I understand required (*) vs. optional fields
- [ ] I can filter records to find what I need
- [ ] I can create a complete procurement workflow

**If all checked: You're ready to use PSM! 🎉**

---

**Last Updated:** 2024
**Version:** 1.0 - Initial Release
