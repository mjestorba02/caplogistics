<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location:http://localhost/caplog1'); exit(); }
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
  <div class="text-sm text-gray-600 mb-6">
    <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
    <span>Document Verification & Validation</span>
  </div>

  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Document Verification & Validation</h1>
  </div>

  <div class="bg-white p-4 rounded-lg shadow mb-6">
    <div class="flex gap-3">
      <input id="search" type="text" placeholder="Search..." class="border rounded px-3 py-2 w-full md:w-1/2">
      <button id="apply" class="bg-indigo-600 text-white px-4 py-2 rounded">Apply</button>
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
          <th class="px-6 py-3">Uploaded</th>
          <th class="px-6 py-3">Actions</th>
        </tr>
      </thead>
      <tbody id="verifyTable"></tbody>
    </table>
    <div id="emptyState" class="hidden text-center py-8 text-gray-600">No documents pending verification</div>
  </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-3/5 p-6 max-h-[85vh] overflow-y-auto">
    <div id="viewContent"></div>
    <div class="flex justify-end gap-2 mt-4">
      <button id="closeView" class="px-4 py-2 bg-gray-300 rounded">Close</button>
      <button id="rejectBtn" class="px-4 py-2 bg-red-600 text-white rounded">Reject</button>
      <button id="verifyBtn" class="px-4 py-2 bg-green-600 text-white rounded">Verify</button>
    </div>
  </div>
</div>

<script src="../scripts/document_verification.js"></script>
HTML;

adminLayout($children);