document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('modal');
  const openModalBtn = document.getElementById('openModal');
  const closeModalBtn = document.getElementById('closeModal');
  const closeBtn = document.getElementById('closeModalBtn');
  const form = document.getElementById('form');
  const tableBody = document.getElementById('tableBody');
  const emptyState = document.getElementById('emptyState');
  const applyFilterBtn = document.getElementById('applyFilter');
  const clearFilterBtn = document.getElementById('clearFilter');

  // Load onboarded assets for dropdown
  async function loadAssets() {
    try {
      const res = await fetch('../api/asset_onboarding_registration.php');
      const data = await res.json();
      const select = document.getElementById('asset_id');
      if (select && data.status === 'success' && Array.isArray(data.data) && data.data.length) {
        select.innerHTML = '<option value="">-- Select Asset --</option>';
        data.data.forEach(asset => {
          const option = document.createElement('option');
          option.value = asset.id;
          option.textContent = `${asset.asset_tag} - ${asset.asset_name} (${asset.status})`;
          select.appendChild(option);
        });
      }
    } catch (err) {
      console.error('Error loading assets:', err);
    }
  }

  function openModal() {
    document.getElementById('modalTitle').textContent = 'Add Disposal';
    document.getElementById('itemId').value = '';
    form.reset();
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  function closeModalFn() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    form.reset();
  }

  openModalBtn?.addEventListener('click', openModal);
  closeModalBtn?.addEventListener('click', closeModalFn);
  closeBtn?.addEventListener('click', closeModalFn);

  async function fetchDisposals(search = '') {
    try {
      const url = `../api/asset_end_of_life_disposal.php${search ? '?q=' + encodeURIComponent(search) : ''}`;
      const res = await fetch(url);
      const data = await res.json();
      if (data.status === 'success' && Array.isArray(data.data) && data.data.length) {
        renderDisposals(data.data);
      } else {
        tableBody.innerHTML = '';
        emptyState.classList.remove('hidden');
      }
    } catch (err) {
      console.error('Error fetching disposals:', err);
      Toastify({ text: 'Error loading disposal records', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
    }
  }

  function renderDisposals(items) {
    emptyState.classList.add('hidden');
    tableBody.innerHTML = items
      .map((a) => `
      <tr class="border-b hover:bg-gray-50">
        <td class="px-6 py-3">${a.asset_id}</td>
        <td class="px-6 py-3 font-semibold">${a.disposal_request_date}</td>
        <td class="px-6 py-3 text-sm">${a.approved_by || 'N/A'}</td>
        <td class="px-6 py-3 text-sm"><span class="px-2 py-1 rounded text-xs bg-purple-100 text-purple-800">${a.disposal_method}</span></td>
        <td class="px-6 py-3 text-sm">${a.disposal_date || 'Pending'}</td>
        <td class="px-6 py-3 text-sm">${a.archived ? 'Yes' : 'No'}</td>
        <td class="px-6 py-3 flex gap-2"><button onclick='editDisposal(${JSON.stringify(a).replace(/"/g, '&quot;')})' class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Edit</button><button onclick="archiveDisposal(${a.id})" class="bg-orange-600 text-white px-3 py-1 rounded text-xs hover:bg-orange-700">Archive</button></td>
      </tr>`)
      .join('');
  }

  function editDisposal(a) {
    document.getElementById('modalTitle').textContent = 'Edit Disposal';
    document.getElementById('itemId').value = a.id;
    document.getElementById('asset_id').value = a.asset_id;
    document.getElementById('disposal_request_date').value = a.disposal_request_date || '';
    document.getElementById('approved_by').value = a.approved_by || '';
    document.getElementById('approval_date').value = a.approval_date || '';
    document.getElementById('disposal_method').value = a.disposal_method || '';
    document.getElementById('disposal_date').value = a.disposal_date || '';
    document.getElementById('proceeds').value = a.proceeds || '';
    openModal();
  }

  async function archiveDisposal(id) {
    if (!confirm('Archive this disposal record?')) return;
    try {
      const res = await fetch('../api/archive_management.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ archive_type: 'asset_disposal', item_id: id, original_table: 'asset_end_of_life_disposal', reason: 'Archived from disposal' }) });
      const data = await res.json();
      if (data.status === 'success') {
        Toastify({ text: 'Disposal archived', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #16a34a, #86efac)' }).showToast();
        fetchDisposals();
      } else {
        throw new Error(data.message || 'Archive failed');
      }
    } catch (err) {
      console.error(err);
      alert('Error archiving disposal: ' + (err.message || err));
    }
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    // Validation
    const assetId = document.getElementById('asset_id').value.trim();
    const reqDate = document.getElementById('disposal_request_date').value.trim();
    if (!assetId) { Toastify({ text: 'Asset ID is required', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast(); return; }
    if (!reqDate) { Toastify({ text: 'Request date is required', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast(); return; }

    const itemId = document.getElementById('itemId').value;
    const payload = {
      id: itemId || undefined,
      asset_id: assetId,
      disposal_request_date: reqDate,
      approved_by: document.getElementById('approved_by').value,
      approval_date: document.getElementById('approval_date').value,
      disposal_method: document.getElementById('disposal_method').value,
      disposal_date: document.getElementById('disposal_date').value,
      proceeds: document.getElementById('proceeds').value
    };
    try {
      const method = itemId ? 'PUT' : 'POST';
      const res = await fetch('../api/asset_end_of_life_disposal.php', {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const result = await res.json();
      if (res.ok && result.status === 'success') {
        Toastify({ text: result.message || 'Saved', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #16a34a, #86efac)' }).showToast();
        closeModalFn();
        fetchDisposals();
      } else {
        throw new Error(result.message || res.statusText || 'Save failed');
      }
    } catch (err) {
      console.error(err);
      Toastify({ text: 'Error saving disposal: ' + (err.message || ''), duration: 5000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
    }
  });

  applyFilterBtn?.addEventListener('click', () => fetchDisposals(document.getElementById('filterInput').value));
  clearFilterBtn?.addEventListener('click', () => { document.getElementById('filterInput').value = ''; fetchDisposals(); });

  window.archiveDisposal = archiveDisposal;
  window.editDisposal = editDisposal;

  loadAssets();
  fetchDisposals();
});
