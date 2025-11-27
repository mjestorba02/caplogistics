document.addEventListener('DOMContentLoaded', () => {
  const table = document.getElementById('verifyTable');
  const empty = document.getElementById('emptyState');
  const viewModal = document.getElementById('viewModal');
  const viewContent = document.getElementById('viewContent');
  const closeView = document.getElementById('closeView');
  const verifyBtn = document.getElementById('verifyBtn');
  const rejectBtn = document.getElementById('rejectBtn');

  function fetchPending() {
    fetch('../api/documents_verification.php').then(r=>r.json()).then(d=>{
      table.innerHTML='';
      if (d.documents && d.documents.length) {
        d.documents.forEach(doc=>{
          const tr = table.insertRow();
          tr.innerHTML = `<td class="px-6 py-3">${doc.id}</td>
                          <td class="px-6 py-3">${doc.doc_code||''}</td>
                          <td class="px-6 py-3">${doc.title||doc.description||''}</td>
                          <td class="px-6 py-3">${doc.document_type||''}</td>
                          <td class="px-6 py-3">${doc.created_at.split(' ')[0]}</td>
                          <td class="px-6 py-3"><button class="viewBtn text-indigo-600" data-id="${doc.id}">View</button></td>`;
        });
        empty.classList.add('hidden');
        document.querySelectorAll('.viewBtn').forEach(b=>b.addEventListener('click', e=>viewDoc(e.target.dataset.id)));
      } else empty.classList.remove('hidden');
    });
  }

  function viewDoc(id) {
    fetch(`../api/documents_list.php?id=${id}`).then(r=>r.json()).then(d=>{
      const doc = (d.documents && d.documents[0]) || d.document || null;
      if (!doc) return alert('Not found');
      viewContent.innerHTML = `<h3 class="text-lg font-semibold">${doc.title||doc.doc_code}</h3>
        <p class="text-sm">Type: ${doc.document_type||'—'}</p>
        <p class="text-sm">Uploaded: ${doc.created_at}</p>
        <div class="mt-2"><iframe src="../api/documents_download.php?id=${doc.id}" class="w-full h-96"></iframe></div>
        <div class="mt-2"><label>Remarks</label><textarea id="remarks" class="w-full border rounded px-2 py-1"></textarea></div>`;
      verifyBtn.onclick = () => update('verify', id);
      rejectBtn.onclick = () => update('reject', id);
      viewModal.classList.remove('hidden'); viewModal.classList.add('flex');
    });
  }

  document.getElementById('closeView').addEventListener('click', ()=>{ viewModal.classList.add('hidden'); viewModal.classList.remove('flex'); });

  function update(action, id) {
    const remarks = document.getElementById('remarks').value || '';
    fetch('../api/documents_verification.php', { method: 'PUT', body: new URLSearchParams({ id, action: action==='verify'?'verify':'reject', remarks }) })
      .then(r=>r.json()).then(d=>{ if (d.status==='success') { alert('Document Updated Successfully'); fetchPending(); viewModal.classList.add('hidden'); } else alert(d.message); });
  }

  fetchPending();
});