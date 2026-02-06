<!DOCTYPE html>
<html>
<head>
    <title>Test Request Display</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-3xl font-bold mb-6">Test: Render Requests for User 19</h1>

<div id="requestsTableBody" class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-100 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Request ID</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Items</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Priority</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Created</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, fetching requests...');
    
    fetch('api/asset_requests.php?action=my_requests')
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('API Response:', data);
            
            if (data.status === 'success' && data.requests) {
                console.log('Found ' + data.requests.length + ' requests');
                renderRequests(data.requests);
            } else {
                console.error('API error:', data.message);
                document.getElementById('tableBody').innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error: ' + (data.message || 'Unknown error') + '</td></tr>';
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('tableBody').innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error: ' + error.message + '</td></tr>';
        });
});

function renderRequests(requests) {
    console.log('Rendering ' + requests.length + ' requests');
    
    if (requests.length === 0) {
        document.getElementById('tableBody').innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No requests found</td></tr>';
        return;
    }
    
    const html = requests.map(req => {
        console.log('Rendering request:', req.request_id);
        return `
            <tr class="border-b border-gray-200 hover:bg-gray-50">
                <td class="px-6 py-4 font-semibold text-blue-600">${req.request_id}</td>
                <td class="px-6 py-4 text-sm">${req.total_items} item(s)</td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                        ${req.status}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                        ${req.priority}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">${new Date(req.request_date).toLocaleDateString()}</td>
            </tr>
        `;
    }).join('');
    
    document.getElementById('tableBody').innerHTML = html;
    console.log('Rendering complete');
}
</script>
</body>
</html>
