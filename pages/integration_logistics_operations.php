<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location:http://localhost/caplog1'); exit(); }
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
  <div class="text-sm text-gray-600 mb-6">
    <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
    <span>Integration with Logistics Operations</span>
  </div>

  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Integration with Logistics Operations</h1>
  </div>

  <div class="bg-white p-4 rounded-lg shadow mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <input id="docId" placeholder="Document ID" class="border rounded px-3 py-2">
      <input id="linked_module" placeholder="Linked Module e.g. PSM/SWS/PLT" class="border rounded px-3 py-2">
      <input id="linked_id" placeholder="Linked ID" class="border rounded px-3 py-2">
    </div>
    <div class="mt-3">
      <button id="linkBtn" class="bg-indigo-600 text-white px-4 py-2 rounded">Link</button>
      <button id="unlinkBtn" class="bg-gray-300 px-4 py-2 rounded">Unlink</button>
    </div>
  </div>

  <div id="integrationResult" class="bg-white p-4 rounded-lg shadow hidden"></div>
</div>

<script src="../scripts/integration_logistics.js"></script>
HTML;

adminLayout($children);