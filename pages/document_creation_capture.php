<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location:http://localhost/caplog1'); exit(); }
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
  <div class="text-sm text-gray-600 mb-6">
    <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
    <span>Document Creation & Capture</span>
  </div>

  <div class="flex justify-between items-center mb-6 gap-3">
    <h1 class="text-3xl font-bold">Document Creation & Capture</h1>
    <div class="flex gap-2">
      <button id="openCreateModal" class="bg-indigo-600 text-white px-4 py-2 rounded">Create Entry</button>
      <button id="openUploadModal" class="bg-green-600 text-white px-4 py-2 rounded">Upload Document</button>
    </div>
  </div>

  <div class="bg-white p-4 rounded-lg shadow mb-6">
    <div class="flex gap-3 items-center">
      <input id="search" type="text" placeholder="Search title / PO / shipment..." class="border rounded px-3 py-2 w-full md:w-1/2">
      <select id="filterType" class="border rounded px-3 py-2">
        <option value="">All Types</option>
        <option value="Invoice">Invoice</option>
        <option value="PO">Purchase Order</option>
        <option value="Delivery">Delivery Note</option>
      </select>
      <button id="applyFilter" class="bg-indigo-600 text-white px-4 py-2 rounded">Apply</button>
      <button id="clearFilter" class="bg-gray-300 px-4 py-2 rounded">Clear</button>
    </div>
  </div>

  <div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="w-full text-left text-sm">
      <thead class="bg-gray-200 border-b">
        <tr>
          <th class="px-6 py-3">ID</th>
          <th class="px-6 py-3">Code</th>
          <th class="px-6 py-3">Title</th>
          <th class="px-6 py-3">Type</th>
          <th class="px-6 py-3">Linked</th>
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

<!-- Create modal -->
<div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2 p-6">
    <h2 class="text-2xl font-bold mb-4">Create Document Entry</h2>
    <form id="createForm" class="space-y-4">
      <div><label class="block text-gray-700">Title</label><input id="title" name="title" class="w-full border rounded px-3 py-2" required></div>
      <div><label class="block text-gray-700">Description</label><textarea id="description" name="description" class="w-full border rounded px-3 py-2"></textarea></div>
      <div class="grid grid-cols-2 gap-2">
        <div><label>Type</label><input id="document_type" name="document_type" class="w-full border rounded px-3 py-2"></div>
        <div><label>Retention Days</label><input id="retention_days" name="retention_days" type="number" value="365" class="w-full border rounded px-3 py-2"></div>
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" id="closeCreate" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Create</button>
      </div>
    </form>
  </div>
</div>

<!-- Upload modal -->
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2 p-6">
    <h2 class="text-2xl font-bold mb-4">Upload Document</h2>
    <form id="uploadForm" enctype="multipart/form-data" class="space-y-4">
      <div><label class="block text-gray-700">File</label><input id="file" name="file" type="file" required></div>
      <div><label class="block text-gray-700">Title</label><input id="u_title" name="title" class="w-full border rounded px-3 py-2"></div>
      <div class="grid grid-cols-2 gap-2">
        <div><label>PO #</label><input id="po_no" name="po_no" class="w-full border rounded px-3 py-2"></div>
        <div><label>Shipment #</label><input id="shipment_no" name="shipment_no" class="w-full border rounded px-3 py-2"></div>
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" id="closeUpload" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Upload</button>
      </div>
    </form>
  </div>
</div>

<script src="../scripts/document_creation.js"></script>
HTML;
adminLayout($children);