<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location:https://log1.imarketph.com'); exit(); }
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
  <div class="text-sm text-gray-600 mb-6">
    <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
    <span>Analytics & Reporting</span>
  </div>

  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Analytics & Reporting</h1>
  </div>

  <div class="bg-white p-4 rounded-lg shadow mb-6">
    <div class="flex gap-3">
      <button id="loadSummary" class="bg-indigo-600 text-white px-4 py-2 rounded">Load Summary</button>
      <button id="loadMonthly" class="bg-gray-300 px-4 py-2 rounded">Monthly Volume</button>
    </div>
  </div>

  <div id="analyticsResult" class="bg-white p-4 rounded-lg shadow mt-4"></div>
</div>

<script src="../scripts/document_analytics.js"></script>
HTML;

adminLayout($children);