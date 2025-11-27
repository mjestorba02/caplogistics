<div id="maintenanceModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
    <h3 class="text-xl font-bold mb-4">Asset Maintenance & Servicing</h3>
    <form id="maintenanceForm">
      <input type="hidden" id="maintenanceId">
      <div class="mb-2"><label>Asset (Onboarding)</label><input type="number" id="asset_id" class="input"></div>
      <div class="mb-2"><label>Work Order #</label><input type="text" id="work_order_number" class="input"></div>
      <div class="mb-2"><label>Trigger Type</label>
        <select id="trigger_type" class="input">
          <option value="Scheduled">Scheduled</option>
          <option value="Manual">Manual</option>
        </select>
      </div>
      <div class="mb-2"><label>Maintenance Type</label><input type="text" id="maintenance_type" class="input"></div>
      <div class="mb-2"><label>Scheduled Date</label><input type="date" id="scheduled_date" class="input"></div>
      <div class="mb-2"><label>Completed Date</label><input type="date" id="completed_date" class="input"></div>
      <div class="mb-2"><label>Technician</label><input type="text" id="technician" class="input"></div>
      <div class="mb-2"><label>Status</label>
        <select id="status" class="input">
          <option value="Pending">Pending</option>
          <option value="Under Maintenance">Under Maintenance</option>
          <option value="Completed">Completed</option>
        </select>
      </div>
      <div class="mb-2"><label>Parts Used</label><textarea id="parts_used" class="input"></textarea></div>
      <div class="mb-2"><label>Labor Hours</label><input type="number" step="0.01" id="labor_hours" class="input"></div>
      <div class="mb-2"><label>Notes</label><textarea id="notes" class="input"></textarea></div>
      <div class="flex justify-end mt-4">
        <button type="button" id="closeMaintenanceModal" class="bg-gray-400 text-white px-4 py-2 rounded mr-2">Cancel</button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
      </div>
    </form>
  </div>
</div>
