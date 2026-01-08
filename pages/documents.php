<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location:http://localhost/caplog1'); exit(); }
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
  <div class="text-sm text-gray-600 mb-6">
    <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt; <span>Document Tracking & Records</span>
  </div>

  <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
      <h1 class="text-3xl font-bold text-gray-800">Document Tracking & Records</h1>
      <div class="flex gap-2">
          <button id="openUpload" class="bg-indigo-600 text-white px-4 py-2 rounded">Upload Document</button>
          <button id="openCreate" class="bg-gray-200 px-4 py-2 rounded">Create Entry</button>
      </div>
  </div>

  <div class="bg-white p-4 rounded-lg shadow mb-6">
    <div class="flex flex-wrap gap-3 items-center">
      <input id="searchInput" type="text" placeholder="Search title, description, PO, shipment..." class="border rounded px-3 py-2 w-full md:w-1/2">
      <select id="filterStatus" class="border rounded px-3 py-2">
        <option value="all">All</option>
        <option value="created">Created</option>
        <option value="pending_verification">Pending Verification</option>
        <option value="verified">Verified</option>
        <option value="approved">Approved</option>
        <option value="archived">Archived</option>
      </select>
      <button id="applyFilters" class="bg-indigo-600 text-white px-4 py-2 rounded">Apply</button>
      <button id="clearFilters" class="bg-gray-300 px-4 py-2 rounded">Clear</button>
    </div>
  </div>

  <div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="w-full text-left text-sm">
      <thead class="bg-gray-200 border-b">
        <tr>
          <th class="px-6 py-3">ID</th>
          <th class="px-6 py-3">Title</th>
          <th class="px-6 py-3">Type</th>
          <th class="px-6 py-3">PO / Shipment</th>
          <th class="px-6 py-3">Supplier</th>
          <th class="px-6 py-3">Status</th>
          <th class="px-6 py-3">Uploaded</th>
          <th class="px-6 py-3">Actions</th>
        </tr>
      </thead>
      <tbody id="docsTable"></tbody>
    </table>
    <div id="emptyState" class="hidden text-center py-8 text-gray-600">No documents found</div>
  </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-2/5 p-6 max-h-[80vh] overflow-y-auto">
    <h2 class="text-2xl font-bold mb-4">Upload Document</h2>
    <form id="uploadForm" enctype="multipart/form-data" class="space-y-4">
      <div><label class="block text-gray-700">File</label><input type="file" name="file" id="fileInput" required></div>
      <div><label class="block text-gray-700">Title</label><input name="title" id="title" class="w-full border rounded px-3 py-2"></div>
      <div class="grid grid-cols-2 gap-2">
        <div><label>PO #</label><input name="po_no" id="po_no" class="w-full border rounded px-3 py-2"></div>
        <div><label>Shipment #</label><input name="shipment_no" id="shipment_no" class="w-full border rounded px-3 py-2"></div>
      </div>
      <div><label>Supplier</label><input name="supplier" id="supplier" class="w-full border rounded px-3 py-2"></div>
      <div class="flex justify-end gap-2">
        <button type="button" id="closeUpload" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Upload</button>
      </div>
    </form>
  </div>
</div>

<!-- View/Approve Modal (reused) -->
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-3/5 p-6 max-h-[85vh] overflow-y-auto">
    <div id="viewContent"></div>
    <div class="flex justify-end gap-2 mt-4">
      <button id="closeView" class="px-4 py-2 bg-gray-300 rounded">Close</button>
      <button id="approveBtn" class="px-4 py-2 bg-green-600 text-white rounded hidden">Approve</button>
      <button id="downloadBtn" class="px-4 py-2 bg-indigo-600 text-white rounded hidden">Download</button>
    </div>
  </div>
</div>

<script src="../scripts/documents.js"></script>
HTML;

adminLayout($children);