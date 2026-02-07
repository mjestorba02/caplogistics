<?php
session_start();
if (!isset($_SESSION['id'])) {
    $_SESSION['id'] = 1; // For testing
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Asset Management - Minimal Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
    <h1 class="text-3xl font-bold mb-6">Asset Management - Minimal Test</h1>
    
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-200 border-b">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Item Number</th>
                    <th class="px-6 py-3">Item Name</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody id="assetsTable"></tbody>
        </table>
        <div id="emptyState" class="hidden text-center py-8 text-gray-600">No assets found</div>
    </div>

    <script>
        console.log('✓ Page loaded, starting fetch...');
        
        fetch('./api/asset_management.php')
            .then(res => {
                console.log('Response status:', res.status);
                return res.json();
            })
            .then(data => {
                console.log('API Response:', data);
                
                if (data.status === 'success' && data.assets && data.assets.length > 0) {
                    console.log('Found', data.assets.length, 'assets');
                    
                    const tableBody = document.getElementById('assetsTable');
                    const emptyState = document.getElementById('emptyState');
                    
                    // Hide empty state
                    emptyState.classList.add('hidden');
                    
                    // Render table
                    tableBody.innerHTML = data.assets.map(asset => `
                        <tr class="border-b">
                            <td class="px-6 py-3">${asset.id}</td>
                            <td class="px-6 py-3">${asset.item_number}</td>
                            <td class="px-6 py-3">${asset.item_name}</td>
                            <td class="px-6 py-3">${asset.status}</td>
                        </tr>
                    `).join('');
                    
                    console.log('✓ Table rendered');
                } else {
                    console.log('No assets found or error');
                    document.getElementById('emptyState').classList.remove('hidden');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error: ' + err.message);
            });
    </script>
</body>
</html>
