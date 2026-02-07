<?php
session_start();

// This page displays recent logs for debugging
// Remove in production!
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Debug Logs - Auth Flow</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 p-8">

<div class="max-w-4xl mx-auto">
  <h1 class="text-3xl font-bold mb-6">🔍 Auth Flow Debug Logs</h1>
  
  <!-- Quick Actions -->
  <div class="mb-6 flex gap-2">
    <button onclick="location.reload()" class="bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded">Refresh</button>
    <button onclick="clearLogs()" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded">Clear Logs</button>
    <button onclick="goBack()" class="bg-gray-600 hover:bg-gray-700 px-4 py-2 rounded">← Back to Login</button>
  </div>

  <!-- Log Files -->
  <div class="grid grid-cols-2 gap-6">
    <!-- Auth Log -->
    <div class="bg-gray-800 rounded-lg p-6">
      <h2 class="text-xl font-bold mb-4">📋 Auth Log</h2>
      <div id="authLog" class="bg-gray-900 p-4 rounded text-xs font-mono overflow-y-auto h-96">
        <p class="text-gray-500">Loading...</p>
      </div>
    </div>

    <!-- Mail Log -->
    <div class="bg-gray-800 rounded-lg p-6">
      <h2 class="text-xl font-bold mb-4">📧 Mail Log</h2>
      <div id="mailLog" class="bg-gray-900 p-4 rounded text-xs font-mono overflow-y-auto h-96">
        <p class="text-gray-500">Loading...</p>
      </div>
    </div>
  </div>

  <!-- Session Info -->
  <div class="bg-gray-800 rounded-lg p-6 mt-6">
    <h2 class="text-xl font-bold mb-4">🔑 Session Info</h2>
    <pre class="bg-gray-900 p-4 rounded text-xs font-mono overflow-x-auto"><?php 
      echo json_encode($_SESSION, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    ?></pre>
  </div>

  <!-- Quick Test -->
  <div class="bg-gray-800 rounded-lg p-6 mt-6">
    <h2 class="text-xl font-bold mb-4">🧪 Quick Test</h2>
    <p class="text-gray-300 mb-4">Test if API is responding:</p>
    <button onclick="testApi()" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded">Test API Connection</button>
    <div id="apiTest" class="mt-4 text-sm"></div>
  </div>
</div>

<script>
  function loadLogs() {
    fetch('get_logs.php')
      .then(r => r.json())
      .then(data => {
        document.getElementById('authLog').innerHTML = data.auth ? 
          `<pre>${data.auth}</pre>` : 
          '<p class="text-yellow-400">No auth log found</p>';
        document.getElementById('mailLog').innerHTML = data.mail ? 
          `<pre>${data.mail}</pre>` : 
          '<p class="text-yellow-400">No mail log found</p>';
      })
      .catch(e => {
        document.getElementById('authLog').innerHTML = `<p class="text-red-400">Error: ${e.message}</p>`;
      });
  }

  function clearLogs() {
    if (confirm('Clear all logs?')) {
      fetch('get_logs.php?action=clear', { method: 'POST' })
        .then(() => {
          loadLogs();
          alert('Logs cleared');
        });
    }
  }

  function goBack() {
    window.location.href = '../index.php';
  }

  function testApi() {
    const testDiv = document.getElementById('apiTest');
    testDiv.innerHTML = '<p class="text-blue-400">Testing API...</p>';
    
    fetch('../api/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'test' })
    })
    .then(r => r.json())
    .then(data => {
      testDiv.innerHTML = `<p class="text-green-400">✓ API OK</p><pre class="text-xs bg-gray-900 p-2 rounded">${JSON.stringify(data, null, 2)}</pre>`;
    })
    .catch(e => {
      testDiv.innerHTML = `<p class="text-red-400">✗ API Error: ${e.message}</p>`;
    });
  }

  loadLogs();
  setInterval(loadLogs, 2000); // Auto-refresh every 2 seconds
</script>

</body>
</html>
