document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('archivesTableBody');
    const emptyState = document.getElementById('emptyState');
    const filterBtn = document.getElementById('applyFilterBtn');
    const clearFilterBtn = document.getElementById('clearFilterBtn');
    const filterSelect = document.getElementById('archiveTypeFilter');
    const detailsModal = document.getElementById('detailsModal');
    const closeDetailsModal = document.getElementById('closeDetailsModal');
    const restoreBtn = document.getElementById('restoreBtn');

    let currentArchiveId = null;

    // Toast helper
    function showToast(message, type) {
        Toastify({
            text: message,
            style: {
                background: type === 'success'
                    ? "linear-gradient(to right, #00b09b, #96c93d)"
                    : "linear-gradient(to right, #ff5f6d, #ffc371)"
            },
            duration: 3000,
            close: true
        }).showToast();
    }

    // Format date
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    // Fetch and display archived items
    async function fetchArchives(archiveType = '') {
        try {
            const url = archiveType
                ? `../api/archive_management.php?action=list&archive_type=${archiveType}`
                : `../api/archive_management.php?action=list`;

            const res = await fetch(url);
            const data = await res.json();

            if (data.status === 'success' && data.items.length > 0) {
                renderArchives(data.items);
                updateStatistics(data.items);
            } else {
                tableBody.innerHTML = '';
                emptyState.classList.remove('hidden');
                updateStatistics([]);
            }
        } catch (err) {
            console.error(err);
            showToast('Error loading archived items', 'error');
        }
    }

    // Render archives table
    function renderArchives(archives) {
        emptyState.classList.add('hidden');
        tableBody.innerHTML = archives.map(item => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-3">${item.id}</td>
                <td class="px-6 py-3"><span class="px-2 py-1 bg-gray-200 rounded text-xs font-semibold">${item.archive_type}</span></td>
                <td class="px-6 py-3 text-sm">${item.original_table}</td>
                <td class="px-6 py-3">${item.archived_by || 'Unknown'}</td>
                <td class="px-6 py-3 text-sm">${formatDate(item.archived_at)}</td>
                <td class="px-6 py-3 text-sm text-gray-600">${item.reason || '-'}</td>
                <td class="px-6 py-3 flex gap-2">
                    <button onclick="viewDetails(${item.id})" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">View</button>
                    ${item.restore_allowed == 1 ? `<button onclick="restoreArchive(${item.id})" class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700">Restore</button>` : ''}
                    <button onclick=\"deleteArchive(${item.id})\" class=\"bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700\">Delete Permanently</button>
                </td>
            </tr>
        `).join('');
    }

    // Update statistics
    function updateStatistics(archives) {
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);

        const totalCount = archives.length;
        const todayCount = archives.filter(a => new Date(a.archived_at) >= today).length;
        const monthCount = archives.filter(a => new Date(a.archived_at) >= monthStart).length;
        const restorableCount = archives.filter(a => a.restore_allowed == 1).length;

        document.getElementById('totalArchived').textContent = totalCount;
        document.getElementById('todayArchived').textContent = todayCount;
        document.getElementById('monthArchived').textContent = monthCount;
        document.getElementById('restorableArchived').textContent = restorableCount;
    }

    // View archive details
    window.viewDetails = async function(archiveId) {
        try {
            const res = await fetch(`../api/archive_management.php?id=${archiveId}`);
            const data = await res.json();

            if (data.status === 'success') {
                const item = data.item;
                const itemData = JSON.parse(item.item_data);

                let detailsHtml = `
                    <p><strong>ID:</strong> ${item.id}</p>
                    <p><strong>Type:</strong> ${item.archive_type}</p>
                    <p><strong>Table:</strong> ${item.original_table}</p>
                    <p><strong>Archived By:</strong> ${item.archived_by || 'Unknown'}</p>
                    <p><strong>Archived At:</strong> ${formatDate(item.archived_at)}</p>
                    ${item.reason ? `<p><strong>Reason:</strong> ${item.reason}</p>` : ''}
                    <hr class="my-4">
                    <p class="font-bold">Item Data:</p>
                    <pre class="bg-gray-100 p-3 rounded text-xs overflow-auto">${JSON.stringify(itemData, null, 2)}</pre>
                `;

                document.getElementById('detailsContent').innerHTML = detailsHtml;
                currentArchiveId = archiveId;
                detailsModal.classList.remove('hidden');
                detailsModal.classList.add('flex');
            }
        } catch (err) {
            console.error(err);
            showToast('Error loading details', 'error');
        }
    };

    // Restore archive
    window.restoreArchive = async function(archiveId) {
        if (!confirm('Are you sure you want to restore this item? It will return to its original table.')) return;

        try {
            const res = await fetch('../api/archive_management.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ archive_id: archiveId })
            });
            const data = await res.json();

            if (data.status === 'success') {
                showToast('Item restored successfully', 'success');
                fetchArchives(filterSelect.value);
            } else {
                showToast(data.message || 'Restore failed', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error restoring item', 'error');
        }
    };

    // Delete archive
    window.deleteArchive = async function(archiveId) {
        if (!confirm('Permanently delete this archived item? This action cannot be undone.')) return;

        try {
            const res = await fetch('../api/archive_management.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ archive_id: archiveId })
            });
            const data = await res.json();

            if (data.status === 'success') {
                showToast('Archived item permanently deleted', 'success');
                fetchArchives(filterSelect.value);
            } else {
                showToast(data.message || 'Deletion failed', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error deleting archived item', 'error');
        }
    };

    // Restore button in modal
    restoreBtn.addEventListener('click', async () => {
        if (currentArchiveId) {
            detailsModal.classList.add('hidden');
            detailsModal.classList.remove('flex');
            await window.restoreArchive(currentArchiveId);
        }
    });

    // Close details modal
    closeDetailsModal.addEventListener('click', () => {
        detailsModal.classList.add('hidden');
        detailsModal.classList.remove('flex');
    });

    // Filter buttons
    filterBtn.addEventListener('click', () => {
        fetchArchives(filterSelect.value);
    });

    clearFilterBtn.addEventListener('click', () => {
        filterSelect.value = '';
        fetchArchives('');
    });

    // Load archives on page load
    fetchArchives();
});
