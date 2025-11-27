<div id="onboardingModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
    <h3 class="text-xl font-bold mb-4">Asset Onboarding & Registration</h3>
    <form id="onboardingForm">
      <input type="hidden" id="onboardingId">
      <div class="mb-2"><label>Receiving (Intake)</label><input type="number" id="receiving_id" class="input"></div>
      <div class="mb-2"><label>Asset Tag</label><input type="text" id="asset_tag" class="input"></div>
      <div class="mb-2"><label>Asset Name</label><input type="text" id="asset_name" class="input"></div>
      <div class="mb-2"><label>Asset Type</label><input type="text" id="asset_type" class="input"></div>
      <div class="mb-2"><label>Serial Number</label><input type="text" id="serial_number" class="input"></div>
      <div class="mb-2"><label>Registration Date</label><input type="date" id="registration_date" class="input"></div>
      <div class="mb-2"><label>Registered By</label><input type="text" id="registered_by" class="input"></div>
      <div class="mb-2"><label>Status</label>
        <select id="status" class="input">
          <option value="In Inventory">In Inventory</option>
          <option value="Ready for Deployment">Ready for Deployment</option>
          <option value="Registered">Registered</option>
        </select>
      </div>
      <div class="flex justify-end mt-4">
        <button type="button" id="closeOnboardingModal" class="bg-gray-400 text-white px-4 py-2 rounded mr-2">Cancel</button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
      </div>
    </form>
  </div>
</div>
