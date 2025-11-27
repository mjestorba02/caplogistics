<div id="disposalModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
    <h3 class="text-xl font-bold mb-4">Asset End-of-Life & Disposal</h3>
    <form id="disposalForm">
      <input type="hidden" id="disposalId">
      <div class="mb-2"><label>Asset (Onboarding)</label><input type="number" id="asset_id" class="input"></div>
      <div class="mb-2"><label>Disposal Request Date</label><input type="date" id="disposal_request_date" class="input"></div>
      <div class="mb-2"><label>Approved By</label><input type="text" id="approved_by" class="input"></div>
      <div class="mb-2"><label>Approval Date</label><input type="date" id="approval_date" class="input"></div>
      <div class="mb-2"><label>Disposal Method</label>
        <select id="disposal_method" class="input">
          <option value="Sold">Sold</option>
          <option value="Recycled">Recycled</option>
          <option value="Scrapped">Scrapped</option>
        </select>
      </div>
      <div class="mb-2"><label>Disposal Date</label><input type="date" id="disposal_date" class="input"></div>
      <div class="mb-2"><label>Proceeds</label><input type="number" step="0.01" id="proceeds" class="input"></div>
      <div class="mb-2"><label>Financial Closure Notes</label><textarea id="financial_closure_notes" class="input"></textarea></div>
      <div class="mb-2"><label>Archived</label>
        <select id="archived" class="input">
          <option value="1">Yes</option>
          <option value="0">No</option>
        </select>
      </div>
      <div class="flex justify-end mt-4">
        <button type="button" id="closeDisposalModal" class="bg-gray-400 text-white px-4 py-2 rounded mr-2">Cancel</button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
      </div>
    </form>
  </div>
</div>
