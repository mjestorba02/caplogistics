<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location:http://localhost/caplog1'); exit(); }
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
  <div class="text-sm text-gray-600 mb-6">
    <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
    <span>Retention & Compliance Management</span>
  </div>

  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Retention & Compliance Management</h1>
    <button id="runRetention" class="bg-red-600 text-white px-4 py-2 rounded">Run Retention Now (Admin)</button>
  </div>

  <div class="bg-white p-4 rounded-lg shadow mb-6">
    <div class="flex gap-3 items-center">
      <input id="r_docId" placeholder="Document ID" class="border rounded px-3 py-2 w-48">
      <input id="r_days" type="number" placeholder="Retention days" class="border rounded px-3 py-2 w-48">
      <button id="setRetention" class="bg-indigo-600 text-white px-4 py-2 rounded">Set</button>
    </div>
    <div id="retentionResult" class="mt-3"></div>
  </div>
</div>

<script src="../scripts/document_retention.js"></script>
HTML;

adminLayout($children);