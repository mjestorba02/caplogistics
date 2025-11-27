document.addEventListener('DOMContentLoaded', () => {
  const docsTable = document.getElementById('docsTable');
  const empty = document.getElementById('emptyState');

  const createModal = document.getElementById('createModal');
  const openCreateModal = document.getElementById('openCreateModal');
  const closeCreate = document.getElementById('closeCreate');
  const createForm = document.getElementById('createForm');

  const uploadModal = document.getElementById('uploadModal');
  const openUploadModal = document.getElementById('openUploadModal');
  const closeUpload = document.getElementById('closeUpload');
  const uploadForm = document.getElementById('uploadForm');

  const search = document.getElementById('search');
  const filterType = document.getElementById('filterType');
  const applyFilter = document.getElementById('applyFilter');
  const clearFilter = document.getElementById('clearFilter');

  function open(modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
  function close(modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }

  openCreateModal.addEventListener('click', () => open(createModal));
  closeCreate.addEventListener('click', () => close(createModal));
  openUploadModal.addEventListener('click', () => open(uploadModal));
  closeUpload.addEventListener('click', () => close(uploadModal));

  createForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const data = new URLSearchParams(new FormData(createForm));
    fetch('../api/documents_create.php', { method: 'POST', body: data })
      .then(r => r.json())
      .then(d => {
        if (d.status === 'success') {
          close(createModal);
          createForm.reset();
          fetchDocs();
          alert('Document created');
        } else alert(d.message || 'Error');
      }).catch(e => alert('Error'));
  });

  uploadForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const fd = new FormData(uploadForm);
    fetch('../api/documents_create.php', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(d => {
        if (d.status === 'success') {
          close(uploadModal);
          uploadForm.reset();
          fetchDocs();
          alert('Uploaded successfully');
        } else alert(d.message || 'Upload failed');
      }).catch(e => alert('Upload failed'));
  });

  function fetchDocs() {
    const qs = new URLSearchParams();
    if (filterType.value) qs.set('document_type', filterType.value);
    if (search.value) qs.set('search', search.value);
    const url = '../api/documents_list.php' + (qs.toString() ? `?${qs.toString()}` : '');
    fetch(url).then(r => r.json()).then(d => {
      docsTable.innerHTML = '';
      if (d.documents && d.documents.length) {
        d.documents.forEach(doc => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td class="px-6 py-3">${doc.id}</td>
            <td class="px-6 py-3">${doc.doc_code || ''}</td>
            <td class="px-6 py-3">${doc.title || ''}</td>
            <td class="px-6 py-3">${doc.document_type || ''}</td>
            <td class="px-6 py-3">${doc.linked_module || ''} / ${doc.linked_id || ''}</td>
            <td class="px-6 py-3"><span class="px-2 py-1 rounded text-sm bg-blue-200 text-blue-800">${doc.status}</span></td>
            <td class="px-6 py-3">${doc.created_at ? doc.created_at.split(' ')[0] : ''}</td>
            <td class="px-6 py-3">
              <button class="viewBtn text-indigo-600 hover:underline" data-id="${doc.id}">View</button>
              <button class="deleteBtn text-red-600 hover:underline" data-id="${doc.id}">Delete</button>
            </td>`;
          docsTable.appendChild(tr);
        });
        empty.classList.add('hidden');
      } else empty.classList.remove('hidden');

      document.querySelectorAll('.viewBtn').forEach(b => b.addEventListener('click', (e) => viewDoc(e.target.dataset.id)));
      document.querySelectorAll('.deleteBtn').forEach(b => b.addEventListener('click', (e) => deleteDoc(e.target.dataset.id)));
    });
  }

  function viewDoc(id) {
    // Open a small viewer modal or use your verification page.
    window.location.href = `document_verification_validation.php?id=${id}`;
  }

  function deleteDoc(id) {
    if (!confirm('Delete this document?')) return;
    fetch(`../api/documents_delete.php?id=${id}`, { method: 'DELETE' })
      .then(r => r.json()).then(d => { if (d.status === 'success') fetchDocs(); else alert(d.message); });
  }

  applyFilter.addEventListener('click', fetchDocs);
  clearFilter.addEventListener('click', () => { filterType.value=''; search.value=''; fetchDocs(); });

  fetchDocs();
});