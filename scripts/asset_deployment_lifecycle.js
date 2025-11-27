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
    document.getElementById('modalTitle').textContent = 'Add Deployment';
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

  async function fetchDeployments(search = '') {
    try {
      const url = `../api/asset_deployment_lifecycle.php${search ? '?q=' + encodeURIComponent(search) : ''}`;
      const res = await fetch(url);
      const data = await res.json();
      if (data.status === 'success' && Array.isArray(data.data) && data.data.length) {
        renderDeployments(data.data);
      } else {
        tableBody.innerHTML = '';
        emptyState.classList.remove('hidden');
      }
    } catch (err) {
      console.error('Error fetching deployments:', err);
      Toastify({ text: 'Error loading deployments', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
    }
  }

  function renderDeployments(items) {
    emptyState.classList.add('hidden');
    tableBody.innerHTML = items
      .map((a) => {
        return `
      <tr class="border-b hover:bg-gray-50">
        <td class="px-6 py-3">${a.asset_id}</td>
        <td class="px-6 py-3 font-semibold">${a.assigned_to || 'N/A'}</td>
        <td class="px-6 py-3 text-sm">${a.assigned_location || 'N/A'}</td>
        <td class="px-6 py-3 text-sm">${a.assignment_date || 'N/A'}</td>
        <td class="px-6 py-3"><span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">${a.status}</span></td>
        <td class="px-6 py-3 text-sm">${a.custodian_acknowledged ? 'Yes' : 'No'}</td>
        <td class="px-6 py-3 flex gap-2"><button onclick='editDeployment(${JSON.stringify(a).replace(/"/g, '&quot;')})' class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Edit</button><button onclick="deleteDeployment(${a.id})" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Delete</button></td>
      </tr>`;
      })
      .join('');
  }

  function editDeployment(a) {
    document.getElementById('modalTitle').textContent = 'Edit Deployment';
    document.getElementById('itemId').value = a.id;
    document.getElementById('asset_id').value = a.asset_id;
    document.getElementById('assigned_to').value = a.assigned_to || '';
    document.getElementById('assigned_location').value = a.assigned_location || '';
    document.getElementById('assignment_date').value = a.assignment_date || '';
    document.getElementById('status').value = a.status || '';
    document.getElementById('custodian_acknowledged').value = a.custodian_acknowledged ? '1' : '0';
    openModal();
  }

  async function deleteDeployment(id) {
    if (!confirm('Delete this deployment?')) return;
    try {
      const res = await fetch(`../api/asset_deployment_lifecycle.php?id=${encodeURIComponent(id)}`, { method: 'DELETE' });
      const data = await res.json();
      if (res.ok && data.status === 'success') {
        Toastify({ text: 'Deployment deleted', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #16a34a, #86efac)' }).showToast();
        fetchDeployments();
      } else {
        throw new Error(data.message || 'Delete failed');
      }
    } catch (err) {
      console.error(err);
      Toastify({ text: 'Error: ' + (err.message || 'Delete failed'), duration: 5000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
    }
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const assetId = document.getElementById('asset_id').value.trim();
    const assignmentDate = document.getElementById('assignment_date').value.trim();
    if (!assetId) {
      Toastify({ text: 'Asset is required', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
      return;
    }
    if (!assignmentDate) {
      Toastify({ text: 'Assignment date is required', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
      return;
    }

    const itemId = document.getElementById('itemId').value;
    const payload = {
      id: itemId || undefined,
      asset_id: assetId,
      assigned_to: document.getElementById('assigned_to').value,
      assigned_location: document.getElementById('assigned_location').value,
      assignment_date: assignmentDate,
      status: document.getElementById('status').value,
      custodian_acknowledged: document.getElementById('custodian_acknowledged').value
    };
    try {
      const method = itemId ? 'PUT' : 'POST';
      const res = await fetch('../api/asset_deployment_lifecycle.php', {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const result = await res.json();
      if (res.ok && result.status === 'success') {
        Toastify({ text: result.message || 'Saved', duration: 3000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #16a34a, #86efac)' }).showToast();
        closeModalFn();
        fetchDeployments();
      } else {
        throw new Error(result.message || res.statusText || 'Save failed');
      }
    } catch (err) {
      console.error(err);
      Toastify({ text: 'Error: ' + (err.message || ''), duration: 5000, gravity: 'top', position: 'right', backgroundColor: 'linear-gradient(to right, #ef4444, #ef9a9a)' }).showToast();
    }
  });

  applyFilterBtn?.addEventListener('click', () => fetchDeployments(document.getElementById('filterInput').value));
  clearFilterBtn?.addEventListener('click', () => { document.getElementById('filterInput').value = ''; fetchDeployments(); });

  window.deleteDeployment = deleteDeployment;
  window.editDeployment = editDeployment;

  loadAssets();
  fetchDeployments();
});
