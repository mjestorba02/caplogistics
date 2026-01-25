# SVG Icons Reference Guide

## Complete SVG Icon Library Used in System

### 1. View / Eye Icon
```html
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
</svg>
```
**Usage**: View details, preview
**Color**: Blue (#3B82F6)
**Size**: Small (w-4 h-4)

---

### 2. Edit / Pencil Icon
```html
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
</svg>
```
**Usage**: Edit item, modify details
**Color**: Indigo (#4F46E5)
**Size**: Small (w-4 h-4)

---

### 3. Approve / Checkmark Circle Icon
```html
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
</svg>
```
**Usage**: Approve shipment, confirm action
**Color**: Green (#16A34A)
**Size**: Small (w-4 h-4)

---

### 4. Delete / Trash Icon
```html
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
</svg>
```
**Usage**: Remove item, delete entry
**Color**: Red (#DC2626)
**Size**: Small (w-4 h-4)

---

### 5. Add / Plus Icon
```html
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
</svg>
```
**Usage**: Add shipment, request supplies, create new item
**Color**: White (on Indigo background)
**Size**: Medium (w-5 h-5)

---

### 6. Filter / Funnel Icon
```html
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
</svg>
```
**Usage**: Apply filters/search
**Color**: White (on Indigo background)
**Size**: Small (w-4 h-4)

---

### 7. Clear / X (Close) Icon
```html
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
</svg>
```
**Usage**: Clear filters, close modal, reset
**Color**: White (on Gray background)
**Size**: Small (w-4 h-4)

---

### 8. Request / Supply Icon (Plus Variation)
```html
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
</svg>
```
**Usage**: Request supplies for low stock items
**Color**: White (on Orange background)
**Size**: Small (w-4 h-4)

---

## CSS Classes Reference

### Size Classes
```css
.w-4 .h-4    /* 1rem / 16px - Small icons */
.w-5 .h-5    /* 1.25rem / 20px - Medium icons */
.w-6 .h-6    /* 1.5rem / 24px - Large icons */
```

### Color Classes
```css
text-white         /* Default for buttons */
fill-none          /* For outlined icons */
stroke="currentColor"  /* Use button color */
stroke-width="2"   /* Consistent thickness */
```

---

## Button Style Guide

### Table Action Buttons
```html
<!-- Edit Button (Indigo) -->
<button class="bg-indigo-600 text-white px-2 py-1 rounded text-xs hover:bg-indigo-700">
    <svg class="w-4 h-4" ...></svg>
</button>

<!-- Delete Button (Red) -->
<button class="bg-red-600 text-white px-2 py-1 rounded text-xs hover:bg-red-700">
    <svg class="w-4 h-4" ...></svg>
</button>

<!-- Request Button (Orange) -->
<button class="bg-orange-500 text-white px-2 py-1 rounded text-xs hover:bg-orange-600">
    <svg class="w-4 h-4" ...></svg>
</button>
```

### Main Action Buttons
```html
<!-- Add/Create Button -->
<button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 flex items-center gap-2">
    <svg class="w-5 h-5" ...></svg>
    Add Shipment
</button>

<!-- Filter Button -->
<button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 flex items-center gap-2">
    <svg class="w-4 h-4" ...></svg>
    Filter
</button>
```

---

## Accessibility Notes

### Best Practices
1. **Always add `title` attribute** for table action buttons:
   ```html
   <button title="View Details">...</button>
   <button title="Edit">...</button>
   <button title="Delete">...</button>
   ```

2. **Use semantic HTML**:
   ```html
   <button type="button"> <!-- Not <div> -->
   ```

3. **Include visible labels for main actions**:
   ```html
   <!-- Good: Icon + Text -->
   <button class="flex items-center gap-2">
       <svg>...</svg>
       Add Shipment
   </button>

   <!-- Not ideal: Icon only -->
   <button>+</button>
   ```

4. **Maintain sufficient contrast**:
   - Indigo (#4F46E5) on white ✅
   - Red (#DC2626) on white ✅
   - Orange (#F97316) on white ✅

---

## Responsive Behavior

### Buttons Stack on Mobile
```html
<div class="flex flex-col md:flex-row items-center gap-4">
    <!-- On mobile: stack vertically -->
    <!-- On tablet+: horizontal layout -->
</div>
```

### Action Buttons Spacing
```css
gap-1   /* Between icon buttons in tables */
gap-2   /* Between main action buttons */
```

---

## How to Add SVG Icons to New Features

### Step 1: Copy the SVG
Select icon from library above

### Step 2: Adjust Size
- Small actions in tables: `w-4 h-4`
- Main buttons: `w-5 h-5`

### Step 3: Match Button Color
```html
<button class="bg-indigo-600 ...">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" ...></svg>
</button>
```

### Step 4: Add Title for Accessibility
```html
<button title="Description of action">
    <svg ...></svg>
</button>
```

---

## Files Using SVG Icons

- ✅ `scripts/Inbound_logistics.js` - View, Edit, Approve, Delete
- ✅ `scripts/storage_inventory.js` - Edit, Request, Delete
- ✅ `scripts/request_supplies.js` - Edit, Delete
- ✅ `pages/inbound_logistics.php` - Add, Filter, Clear buttons
- ✅ `pages/storage_inventory.php` - Filter, Clear buttons
- ✅ `pages/request_supplies.php` - Add, Filter, Clear buttons

---

## Design Consistency Checklist

- [ ] All action buttons use small SVG (w-4 h-4)
- [ ] All main buttons use medium SVG (w-5 h-5)
- [ ] Icons match button color (white on colored background)
- [ ] Proper spacing with `gap-2` or `gap-1`
- [ ] Title attributes on all icon buttons
- [ ] Hover states on all buttons
- [ ] Responsive design maintained
- [ ] Consistent stroke-width of 2
- [ ] Using `fill="none"` for outlined icons

---

## Quick Copy-Paste Templates

### Complete Icon Button
```html
<button onclick="actionName(${id})" 
    class="bg-indigo-600 text-white px-2 py-1 rounded text-xs hover:bg-indigo-700" 
    title="Edit Item">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
    </svg>
</button>
```

### Complete Main Button
```html
<button id="actionButton" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition flex items-center gap-2">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Add New Item
</button>
```

---

This guide ensures consistent, professional SVG icon usage across the entire system! 🎨
