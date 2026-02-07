<?php
session_start();
$_SESSION['id'] = 1;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Asset Management Full Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body class="bg-gray-100">
    <div class="p-6">
        <h1 class="text-3xl font-bold mb-4">Asset Management - Full Function Test</h1>
        
        <div class="bg-white p-4 rounded shadow mb-4">
            <h2 class="text-xl font-bold mb-2">Test API Connection</h2>
            <button id="testAPI" class="bg-blue-600 text-white px-4 py-2 rounded">Test API</button>
            <pre id="apiOutput" class="bg-gray-100 p-2 rounded mt-2 text-sm overflow-x-auto" style="display:none;"></pre>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <h2 class="text-xl font-bold mb-2">Test Modal Functionality</h2>
            <button id="openModalTest" class="bg-green-600 text-white px-4 py-2 rounded">Open Modal</button>
            
            <div id="testModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-[100]" style="display:none;">
                <div class="bg-white rounded-lg p-6 w-96">
                    <h2 class="text-xl font-bold mb-4">Test Modal</h2>
                    <p>If you see this, the modal is working!</p>
                    <button id="closeModalTest" class="bg-red-600 text-white px-4 py-2 rounded mt-4">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        console.log('✓ Test page loaded');

        // Test API
        document.getElementById('testAPI').addEventListener('click', async () => {
            console.log('Testing API...');
            try {
                const res = await fetch('./api/asset_management.php');
                const data = await res.json();
                console.log('API Response:', data);
                document.getElementById('apiOutput').textContent = JSON.stringify(data, null, 2);
                document.getElementById('apiOutput').style.display = 'block';
            } catch (err) {
                console.error('API Error:', err);
                alert('API Error: ' + err.message);
            }
        });

        // Test Modal
        const modal = document.getElementById('testModal');
        document.getElementById('openModalTest').addEventListener('click', () => {
            console.log('Opening modal...');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        });

        document.getElementById('closeModalTest').addEventListener('click', () => {
            console.log('Closing modal...');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        });
    </script>
</body>
</html>
