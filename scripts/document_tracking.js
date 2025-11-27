document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('viewTrack');
  const out = document.getElementById('trackingResult');

  btn.addEventListener('click', () => {
    const id = document.getElementById('docId').value;
    if (!id) return alert('Enter document id');
    fetch(`../api/documents_tracking.php?id=${id}`).then(r=>r.json()).then(d=>{
      if (d.status !== 'success') return alert('Not found');
      let html = `<h3 class="text-lg font-semibold">${d.document.title || d.document.doc_code}</h3>`;
      html += `<p>Status: ${d.document.status}</p><div class="mt-3"><h4 class="font-semibold">Audit</h4>`;
      d.audit.forEach(a => {
        html += `<div class="border-b py-2"><strong>${a.action}</strong> by ${a.user_id || 'system'} at ${a.created_at}<br>${a.note || ''}</div>`;
      });
      html += '</div>';
      out.innerHTML = html; out.classList.remove('hidden');
    });
  });
});