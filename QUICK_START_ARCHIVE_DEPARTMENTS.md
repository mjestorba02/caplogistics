# Quick Start: Archive & Departments System

## 🚀 Get Started in 3 Steps

### Step 1: Setup Departments
1. **Login** to your application
2. **Click**: Admin Settings → Setup Departments
3. **Click**: "Create Departments & Assign Users"
4. **Done!** Your 19 users now have departments assigned

### Step 2: Test Archiving
1. Go to **Asset Management**
2. Click the orange **"Archive"** button on any asset
3. Confirm the action
4. ✅ The asset disappears from the list
5. Go to **Archived Items** to see it there

### Step 3: View Archived Items
1. Click **"Archived Items"** in the sidebar
2. See statistics at the top
3. Browse archived items by type
4. Click "View" to see full details
5. Click "Restore" to bring back an item

---

## 📊 What Was Added

| Component | Location | Purpose |
|-----------|----------|---------|
| Archive Dashboard | Sidebar → Archived Items | View all archived items |
| Setup Wizard | Admin Settings → Setup Departments | One-click setup |
| Department API | `/api/departments.php` | Backend for department management |
| Archive Fix | Asset Management | Now correctly hides archived items |

---

## 🎯 Default Department Assignments

When you run setup, users get these departments:

```
Admins:
  - John → Administration
  - john → Administration  
  - Mark John Estorba → Finance

Operations Team:
  - Lance → Warehouse & Logistics
  - Randy Alvarez → Warehouse & Logistics
  - Daniel Zabat → Shipping & Delivery

Procurement:
  - Andrei → Procurement
  - Julian Castañares → Procurement

Support Functions:
  - Manang → Operations
  - Ariel Mendoza → Quality Control
  - Harley → Sales & Customer Service
```

---

## 💡 Tips

### Viewing Archives
- Filter by type to find specific archived items
- Each archive shows: ID, Type, Table, Who archived it, When, Why
- Click "View" to see the complete original data

### Restoring Items
- Click "Restore" to bring archived item back
- Item reappears in original table
- Archives show a "Restorable" count

### Permanent Deletion
- "Delete Permanently" is final - cannot be undone
- Use for cleaning up old archives
- Only items with restore_allowed=1 can be restored

---

## 🔄 Archive Locations

Archived items from these modules appear in one central location:

- ✅ Inbound Logistics (Shipments)
- ✅ Asset Management (Assets)
- ✅ Storage Inventory
- ✅ Purchase Orders
- ✅ Supply Requests
- ✅ Returns Management
- ✅ And 10+ more modules...

---

## ❓ FAQ

**Q: What happens when I archive an item?**
A: Item is moved to the `archived_items` table and removed from the original table. It's safe and restorable.

**Q: Can I recover a permanently deleted archive?**
A: No. Permanent deletion cannot be undone. Make sure before clicking it.

**Q: Can I change user departments?**
A: Yes. Go to the department API or modify `user_departments` table directly.

**Q: Will archived items affect reports?**
A: Depends on your report queries. Most default reports won't include archived items.

---

## 📞 Support

If something doesn't work:

1. **Check database tables**: `departments` and `user_departments` should exist
2. **Check API**: `/api/departments.php?action=user_department&user_id=1` should return JSON
3. **Check browser console**: Look for JavaScript errors
4. **Check server logs**: Look for PHP errors

---

## ✨ What's Better Now

**Before:**
- ❌ "Unknown Department" for all users
- ❌ No central archive location
- ❌ Archived items still visible in tables
- ❌ No way to restore items

**After:**
- ✅ Each user has a real department
- ✅ Central Archived Items dashboard
- ✅ Archived items hidden from tables
- ✅ Restore archived items anytime
- ✅ Track what was archived and why
