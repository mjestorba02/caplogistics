# Asset Management Debug Guide

## How to View Debug Messages

1. **Open Asset Management Page**: Go to your Asset Management page in the browser
2. **Open Browser Console**: 
   - Press `F12` (Windows/Linux) or `Cmd+Option+I` (Mac)
   - Click the "Console" tab at the top
3. **Look for Debug Output**: You should see messages starting with emoji symbols like 🔍, ✓, 📡, etc.

---

## Debug Messages Explained

### Page Load Phase
```
🔍 Asset Management Script Starting...
✓ DOM Content Loaded
📋 Elements Found:
  modal: <HTMLElement>
  openModalBtn: <HTMLElement>
  closeModalBtn: <HTMLElement>
  ... etc
```
**What it means**: The page is loading correctly and all HTML elements exist.

### Event Listeners Phase
```
✓ Open Modal Listener Added
✓ Close Modal Listener Added
✓ Search Button Listener Added
```
**What it means**: JavaScript event listeners are attached and ready to respond to clicks.

### Initial Data Load
```
🔄 Fetching Assets...
🔎 Search Params: {itemNumber: '', status: ''}
📡 API URL: ../api/asset_management.php?
📊 Response Status: 200 OK
✓ API Response: {status: 'success', assets: [...], total: 1}
📦 Assets Found: 1
🎨 renderAssets() called with 1 assets
📊 Asset details: [{id: 1, item_number: 'awdwa', ...}]
  🔄 Rendering asset 1/1: awdwa wadwa
📍 Attaching event listeners to buttons...
  ✓ Edit button 1 listener added
  ✓ Archive button 1 listener added
✅ All button listeners attached
```
**What it means**: Data loaded successfully and is being rendered into the table.

### When You Click "Add Asset"
```
🔓 Opening Modal
```
**What it means**: Modal visibility toggled on.

### When You Click "Edit" Button
```
✏️ Edit button clicked for asset: {"id":1,"item_number":"awdwa"...}
✏️ editAsset() called with: {id: 1, item_number: 'awdwa', ...}
📝 Form fields populated, opening modal...
🔓 Opening Modal
```
**What it means**: Asset data populated the form and modal opened.

### When You Submit the Form
```
📝 Form submitted!
📋 Asset ID: 1
📦 Form data: {item_number: 'test', type_of_asset: 'Equipment', ...}
📸 File selected: photo.jpg 2048576 bytes
🔄 Sending PUT request to API...
📊 API Response Status: 200 OK
✓ API Response: {status: 'success', message: 'Asset updated successfully'}
✅ Success! Message: Asset updated successfully
🔄 Fetching Assets...
... (page reloads data)
```
**What it means**: Form data was sent to the API and saved successfully.

### When You Click "Archive"
```
🗑️ Archive button clicked for asset ID: 1
🗑️ archiveAsset() called for ID: 1
📤 Sending archive request...
📊 Archive API Status: 200
✓ Archive API Response: {status: 'success', ...}
✅ Archive successful
🔄 Fetching Assets...
```
**What it means**: Asset was archived and page reloaded.

### When You Click Search
```
🔍 Search button clicked
🔎 Search params: {searchItem: 'test', searchStatus: 'Active'}
🔄 Fetching Assets...
🔎 Search Params: {itemNumber: 'test', status: 'Active'}
📡 API URL: ../api/asset_management.php?item_number=test&status=Active
... (API call proceeds)
```
**What it means**: Search filters are being applied.

### When You Click Clear Search
```
🗑️ Clear search button clicked
✓ Search fields cleared
🔄 Fetching Assets...
```
**What it means**: Search was reset and all assets reloaded.

---

## Troubleshooting

### Problem: "No assets found" but you know they exist
**Check for**:
- `❌ Fetch Error: ...` - API request failed
- `⚠️ No assets found` - API returned empty array
- Check the API URL in console - does it look correct?

### Problem: Modal doesn't open when clicking button
**Check for**:
- `❌ openModalBtn NOT FOUND!` - HTML element missing ID
- If button found, look for `🔓 Opening Modal` - should appear when clicking
- Check if `🔄 Rendering asset...` shows - assets must load first

### Problem: Edit button doesn't work
**Check for**:
- `🔄 Rendering asset 1/1:` - assets loading?
- `✓ Edit button 1 listener added` - listener attached?
- `✏️ Edit button clicked` - does message appear when you click?
- `✏️ editAsset() called with:` - should show asset data

### Problem: Form won't submit
**Check for**:
- `📝 Form submitted!` - check if message appears
- `📋 Asset ID:` - ID value shown?
- `📦 Form data:` - data correct?
- `🔄 Sending POST/PUT request` - API call being made?
- `📊 API Response Status: 200` - did API respond?

### Problem: Nothing appears in console
**Possible causes**:
- You're looking at the wrong tab - open "Console" tab specifically
- Page didn't load completely - refresh with F5
- JavaScript disabled in browser
- Check browser console for syntax errors (red text)

---

## Quick Copy-Paste Search Tips

In the console, type these to find messages:
- **Find all API calls**: Scroll up and look for lines starting with `📡 API URL:`
- **Find all errors**: Scroll up and look for lines starting with `❌ Error:` (red text)
- **Find renderAssets**: Look for `🎨 renderAssets()` 
- **Find all button clicks**: Look for `✏️ Edit button clicked` or `🗑️ Archive button clicked`

---

## Console Filtering

In the Console tab, you can filter messages:
1. Click the funnel icon (Filter)
2. Type one of these to see related messages:
   - `renderAssets` - see table rendering
   - `Fetch` - see API calls
   - `button clicked` - see button interactions
   - `Error` - see only error messages

---

## Report Format

If you report an issue, include this info from console:
```
When I [action], console shows:
[paste the debug messages from console]
```

Example:
```
When I click "Add Asset", console shows:
❌ openModalBtn NOT FOUND!

This tells us the HTML element with id="openModalBtn" doesn't exist in the page.
```
