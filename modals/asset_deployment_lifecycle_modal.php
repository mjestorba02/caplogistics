<div id="deploymentModal" class="fixed inset-0 hidden z-50 flex items-center justify-center">
  <div class="absolute inset-0 bg-black/50" id="deploymentModalOverlay"></div>
  <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative z-10">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-2xl font-bold">Asset Deployment & Operational Life</h2>
      <button type="button" id="closeDeploymentModal" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
    </div>
    <form id="deploymentForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <input type="hidden" id="deploymentId">
      <div>
        <label class="block text-gray-700 text-sm mb-1">Asset (Onboarding)</label>
        <input type="number" id="asset_id" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-gray-700 text-sm mb-1">Assigned To</label>
        <input type="text" id="assigned_to" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-gray-700 text-sm mb-1">Assigned Location</label>
        <input type="text" id="assigned_location" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-gray-700 text-sm mb-1">Assignment Date</label>
        <input type="date" id="assignment_date" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-gray-700 text-sm mb-1">Status</label>
        <select id="status" class="w-full border rounded px-3 py-2">
          <option value="In Use">In Use</option>
          <option value="Transferred">Transferred</option>
          <option value="Returned">Returned</option>
          <option value="Lost">Lost</option>
        </select>
      </div>
      <div>
        <label class="block text-gray-700 text-sm mb-1">Custodian Acknowledged</label>
        <select id="custodian_acknowledged" class="w-full border rounded px-3 py-2">
          <option value="1">Yes</option>
          <option value="0">No</option>
        </select>
      </div>
      <div class="md:col-span-2 flex justify-end gap-3 mt-1">
        <button type="button" id="closeDeploymentModal2" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
      </div>
    </form>
  </div>
</div>
