<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location:http://localhost/caplog1'); exit(); }
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
  <div class="text-sm text-gray-600 mb-6">
    <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
    <span>Secure Storage & Organization</span>
  </div>

  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Secure Storage & Organization</h1>
  </div>

  <div class="bg-white p-4 rounded-lg shadow mb-6">
    <div class="flex gap-3">
      <input id="search" type="text" placeholder="Search files..." class="border rounded px-3 py-2 w-full md:w-1/2">
      <button id="apply" class="bg-indigo-600 text-white px-4 py-2 rounded">Apply</button>
    </div>
  </div>

  <div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="w-full text-left text-sm">
      <thead class="bg-gray-200 border-b">
        <tr>
          <th class="px-6 py-3">ID</th>
          <th class="px-6 py-3">Code</th>
          <th class="px-6 py-3">File</th>
          <th class="px-6 py-3">Module</th>
          <th class="px-6 py-3">Status</th>
          <th class="px-6 py-3">Actions</th>
        </tr>
      </thead>
      <tbody id="storageTable"></tbody>
    </table>
    <div id="emptyState" class="hidden text-center py-8 text-gray-600">No files</div>
  </div>
</div>

<script src="../scripts/secure_storage.js"></script>
HTML;

adminLayout($children);