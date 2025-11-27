document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('setRetention').addEventListener('click', ()=> {
    const id = document.getElementById('r_docId').value;
    const days = document.getElementById('r_days').value;
    if (!id || !days) return alert('Provide id and days');
    fetch('../api/documents_retention.php', { method:'PUT', body: new URLSearchParams({ id, retention_days: days }) })
      .then(r=>r.json()).then(d=>{ if (d.status==='success') alert('Updated'); else alert(d.message); });
  });

  document.getElementById('runRetention').addEventListener('click', ()=> {
    if (!confirm('Run retention now?')) return;
    fetch('../api/documents_retention.php', { method:'POST' }).then(r=>r.json()).then(d=>{ if (d.status==='success') alert('Archived: '+d.archived); else alert(d.message); });
  });
});