<div id="receivingModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative max-h-[90vh] overflow-y-auto">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-2xl font-bold">Receiving & Logistics Intake</h3>
      <button type="button" id="closeReceivingModalBtn" class="text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
    <form id="receivingForm" class="space-y-4">
      <input type="hidden" id="receivingId">
      
      <!-- Row 1: PO Number & Received Date -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold mb-2">PO Number *</label>
          <input type="text" id="po_number" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
        </div>
        <div>
          <label class="block text-sm font-semibold mb-2">Received Date *</label>
          <input type="date" id="received_date" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
        </div>
      </div>

      <!-- Row 2: Received By & Supplier Name -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold mb-2">Received By *</label>
          <input type="text" id="received_by" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
        </div>
        <div>
          <label class="block text-sm font-semibold mb-2">Supplier Name</label>
          <input type="text" id="supplier_name" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
      </div>

      <!-- Row 3: Item Description (Full Width) -->
      <div>
        <label class="block text-sm font-semibold mb-2">Item Description</label>
        <input type="text" id="item_description" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      </div>

      <!-- Row 4: Quantity Fields -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold mb-2">Quantity Received *</label>
          <input type="number" id="quantity_received" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
        </div>
        <div>
          <label class="block text-sm font-semibold mb-2">Quantity Expected *</label>
          <input type="number" id="quantity_expected" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
        </div>
      </div>

      <!-- Row 5: Notes -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold mb-2">Damage Notes</label>
          <textarea id="damage_notes" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none" rows="3"></textarea>
        </div>
        <div>
          <label class="block text-sm font-semibold mb-2">Discrepancy Notes</label>
          <textarea id="discrepancy_notes" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none" rows="3"></textarea>
        </div>
      </div>

      <!-- Row 6: Status -->
      <div>
        <label class="block text-sm font-semibold mb-2">Status *</label>
        <select id="status" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
          <option value="Received">Received</option>
          <option value="Pending">Pending</option>
          <option value="Discrepancy">Discrepancy</option>
          <option value="Damaged">Damaged</option>
        </select>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
        <button type="button" id="closeReceivingModal" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded transition">Cancel</button>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded transition">Save</button>
      </div>
    </form>
  </div>
</div>
