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
    document.getElementById('modalTitle').textContent = 'Add Maintenance';
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

  async function fetchMaintenance(search = '') {
    try {
      const url = `../api/asset_maintenance_servicing.php${search ? '?q=' + encodeURIComponent(search) : ''}`;
      const res = await fetch(url);
      const data = await res.json();
      if (data.status === 'success' && Array.isArray(data.data) && data.data.length) {
        renderMaintenance(data.data);
      } else {
        tableBody.innerHTML = '';
        emptyState.classList.remove('hidden');
      }
    } catch (err) {
      console.error('Error fetching maintenance:', err);
      Toastify({ text: 'Error loading maintenance records', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
    }
  }

  function renderMaintenance(items) {
    emptyState.classList.add('hidden');
    tableBody.innerHTML = items
      .map((a) => `
      <tr class="border-b hover:bg-gray-50">
        <td class="px-6 py-3">${a.asset_id}</td>
        <td class="px-6 py-3 font-semibold">${a.work_order_number}</td>
        <td class="px-6 py-3 text-sm">${a.maintenance_type || 'N/A'}</td>
        <td class="px-6 py-3 text-sm">${a.scheduled_date || 'N/A'}</td>
        <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">${a.status}</span></td>
        <td class="px-6 py-3 text-sm">${a.technician || 'N/A'}</td>
        <td class="px-6 py-3 flex gap-2"><button onclick='editMaintenance(${JSON.stringify(a).replace(/"/g, '&quot;')})' class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Edit</button><button onclick="deleteMaintenance(${a.id})" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Delete</button></td>
      </tr>`)
      .join('');
  }

  function editMaintenance(a) {
    document.getElementById('modalTitle').textContent = 'Edit Maintenance';
    document.getElementById('itemId').value = a.id;
    document.getElementById('asset_id').value = a.asset_id;
    document.getElementById('work_order_number').value = a.work_order_number;
    document.getElementById('maintenance_type').value = a.maintenance_type || '';
    document.getElementById('scheduled_date').value = a.scheduled_date || '';
    document.getElementById('completed_date').value = a.completed_date || '';
    document.getElementById('technician').value = a.technician || '';
    document.getElementById('status').value = a.status || '';
    openModal();
  }

  async function deleteMaintenance(id) {
    if (!confirm('Delete this maintenance record?')) return;
    try {
      const res = await fetch(`../api/asset_maintenance_servicing.php?id=${encodeURIComponent(id)}`, { method: 'DELETE' });
      const data = await res.json();
      if (res.ok && data.status === 'success') {
        Toastify({ text: 'Maintenance deleted', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #16a34a, #86efac)' }).showToast();
        fetchMaintenance();
      } else {
        throw new Error(data.message || 'Delete failed');
      }
    } catch (err) {
      console.error(err);
      alert('Error deleting maintenance: ' + (err.message || err));
    }
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    // Validation
    const assetId = document.getElementById('asset_id').value.trim();
    const wo = document.getElementById('work_order_number').value.trim();
    const scheduled = document.getElementById('scheduled_date').value.trim();
    if (!assetId) { Toastify({ text: 'Asset ID is required', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast(); return; }
    if (!wo) { Toastify({ text: 'Work order number is required', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast(); return; }
    if (!scheduled) { Toastify({ text: 'Scheduled date is required', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast(); return; }

    const itemId = document.getElementById('itemId').value;
    const payload = {
      id: itemId || undefined,
      asset_id: assetId,
      work_order_number: wo,
      maintenance_type: document.getElementById('maintenance_type').value,
      scheduled_date: scheduled,
      completed_date: document.getElementById('completed_date').value,
      technician: document.getElementById('technician').value,
      status: document.getElementById('status').value
    };
    try {
      const method = itemId ? 'PUT' : 'POST';
      const res = await fetch('../api/asset_maintenance_servicing.php', {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const result = await res.json();
      if (res.ok && result.status === 'success') {
        Toastify({ text: result.message || 'Saved', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #16a34a, #86efac)' }).showToast();
        closeModalFn();
        fetchMaintenance();
      } else {
        throw new Error(result.message || res.statusText || 'Save failed');
      }
    } catch (err) {
      console.error(err);
      Toastify({ text: 'Error saving maintenance: ' + (err.message || ''), duration: 5000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
    }
  });

  applyFilterBtn?.addEventListener('click', () => fetchMaintenance(document.getElementById('filterInput').value));
  clearFilterBtn?.addEventListener('click', () => { document.getElementById('filterInput').value = ''; fetchMaintenance(); });

  window.deleteMaintenance = deleteMaintenance;
  window.editMaintenance = editMaintenance;

  loadAssets();
  fetchMaintenance();
});
