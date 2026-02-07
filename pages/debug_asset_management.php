<!DOCTYPE html>
<html>
<head>
    <title>Asset Management - Debug</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body class="bg-gray-100">
    <div class="p-6">
        <h1 class="text-3xl font-bold mb-4">Asset Management - Debug Test</h1>
        
        <div class="bg-yellow-100 border border-yellow-400 p-4 rounded mb-6">
            <p><strong>Debug Info:</strong> This page tests the asset loading functionality</p>
        </div>

        <div class="flex gap-3 mb-6">
            <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Add Asset</button>
            <button id="testFetch" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Test API Fetch</button>
        </div>

        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-200 border-b">
                    <tr>
                        <th class="px-6 py-3">Item Number</th>
                        <th class="px-6 py-3">Image</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Item Code</th>
                        <th class="px-6 py-3">Item Name</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Date</th>
                    </tr>
                </thead>
                <tbody id="assetsTable"></tbody>
            </table>
            <div id="emptyState" class="hidden text-center py-8 text-gray-600">No assets found</div>
        </div>

        <pre id="debugOutput" class="bg-gray-800 text-white p-4 rounded mt-6 text-xs" style="max-height: 300px; overflow-y: auto;">Debug output will appear here...</pre>
    </div>

    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-[100]">
        <div class="bg-white rounded-lg p-6 w-96">
            <h2 class="text-2xl font-bold mb-4">Add Asset</h2>
            <form id="assetForm">
                <input type="hidden" id="assetId" />
                <div class="mb-4">
                    <label class="block text-gray-700">Item Number *</label>
                    <input id="item_number" type="text" required class="w-full border rounded px-3 py-2" />
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const debugOutput = document.getElementById('debugOutput');
        let logBuffer = '';

        function log(msg) {
            console.log(msg);
            logBuffer += msg + '\n';
            debugOutput.textContent = logBuffer;
            debugOutput.scrollTop = debugOutput.scrollHeight;
        }

        log('🔍 Asset Management Debug Test Started');
        log('📍 Checking DOM elements...');

        const modal = document.getElementById('modal');
        const openModalBtn = document.getElementById('openModal');
        const closeModalBtn = document.getElementById('closeModal');
        const form = document.getElementById('assetForm');
        const tableBody = document.getElementById('assetsTable');
        const emptyState = document.getElementById('emptyState');
        const testFetchBtn = document.getElementById('testFetch');

        log('✓ Modal: ' + (modal ? 'Found' : 'NOT FOUND'));
        log('✓ Open Button: ' + (openModalBtn ? 'Found' : 'NOT FOUND'));
        log('✓ Table Body: ' + (tableBody ? 'Found' : 'NOT FOUND'));
        log('✓ Empty State: ' + (emptyState ? 'Found' : 'NOT FOUND'));

        function openModal() {
            log('🔓 Opening modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            log('🔒 Closing modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        openModalBtn.addEventListener('click', openModal);
        closeModalBtn.addEventListener('click', closeModal);

        async function fetchAssets() {
            log('\n🔄 Fetching assets from API...');
            try {
                const url = './api/asset_management.php';
                log('📡 URL: ' + url);
                
                const res = await fetch(url);
                log('📊 Response Status: ' + res.status + ' ' + res.statusText);
                
                const data = await res.json();
                log('✓ Parsed JSON');
                log('Status: ' + data.status);
                
                if (data.status === 'success' && data.assets) {
                    log('✓ Got ' + data.assets.length + ' assets');
                    renderAssets(data.assets);
                } else {
                    log('⚠️ No assets or error: ' + JSON.stringify(data));
                    tableBody.innerHTML = '';
                    emptyState.classList.remove('hidden');
                }
            } catch (err) {
                log('❌ ERROR: ' + err.message);
                log('Stack: ' + err.stack);
            }
        }

        function renderAssets(assets) {
            log('🎨 Rendering ' + assets.length + ' assets');
            
            if (assets.length === 0) {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
                log('⚠️ No assets to display, showing empty state');
                return;
            }

            emptyState.classList.add('hidden');
            tableBody.innerHTML = assets.map((a) => `
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-3">${a.item_number}</td>
                    <td class="px-6 py-3">${a.image ? '✓ Image' : 'N/A'}</td>
                    <td class="px-6 py-3">${a.type_of_asset || ''}</td>
                    <td class="px-6 py-3">${a.item_code || ''}</td>
                    <td class="px-6 py-3">${a.item_name || ''}</td>
                    <td class="px-6 py-3">${a.status}</td>
                    <td class="px-6 py-3">${new Date(a.date).toLocaleDateString()}</td>
                </tr>
            `).join('');
            
            log('✓ Table rendered with ' + assets.length + ' rows');
        }

        testFetchBtn.addEventListener('click', () => {
            logBuffer = '';
            fetchAssets();
        });

        // Auto-fetch on page load
        window.addEventListener('load', () => {
            log('\n📄 Page loaded, auto-fetching assets...');
            setTimeout(fetchAssets, 500);
        });

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            log('📝 Form submitted - not implemented in debug');
        });
    </script>
</body>
</html>
