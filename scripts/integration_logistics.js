document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('linkBtn').addEventListener('click', ()=> {
    const id = document.getElementById('docId').value;
    const lm = document.getElementById('linked_module').value;
    const lid = document.getElementById('linked_id').value;
    if (!id || !lm) return alert('Provide doc id and module');
    fetch('../api/documents_integration.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ document_id: id, linked_module: lm, linked_id: lid })})
      .then(r=>r.json()).then(d=>{ if (d.status==='success') alert('Linked'); else alert(d.message); });
  });

  document.getElementById('unlinkBtn').addEventListener('click', ()=> {
    const id = document.getElementById('docId').value;
    if (!id) return alert('Provide doc id');
    fetch(`../api/documents_integration.php?id=${id}`, { method: 'DELETE' }).then(r=>r.json()).then(d=>{ if (d.status==='success') alert('Unlinked'); else alert(d.message); });
  });
});