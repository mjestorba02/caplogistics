<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location:https://log1.imarketph.com'); exit(); }
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
  <div class="text-sm text-gray-600 mb-6">
    <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
    <span>Document Tracking & Visibility</span>
  </div>

  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Document Tracking & Visibility</h1>
  </div>

  <div class="bg-white p-4 rounded-lg shadow mb-6">
    <div class="flex gap-3">
      <input id="docId" type="text" placeholder="Document ID" class="border rounded px-3 py-2 w-48">
      <button id="viewTrack" class="bg-indigo-600 text-white px-4 py-2 rounded">View Tracking</button>
    </div>
  </div>

  <div id="trackingResult" class="bg-white p-4 rounded-lg shadow hidden"></div>
</div>

<script src="../scripts/document_tracking.js"></script>
HTML;

adminLayout($children);