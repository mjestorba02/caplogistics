document.addEventListener('DOMContentLoaded', () => {
  const out = document.getElementById('analyticsResult');
  document.getElementById('loadSummary').addEventListener('click', ()=> {
    fetch('../api/documents_analytics.php?action=summary').then(r=>r.json()).then(d=> {
      if (d.status==='success') {
        let html = '<ul>';
        d.counts.forEach(c => html += `<li>${c.status}: ${c.cnt}</li>`);
        html += '</ul>'; out.innerHTML = html;
      }
    });
  });
  document.getElementById('loadMonthly').addEventListener('click', ()=> {
    fetch('../api/documents_analytics.php?action=monthly').then(r=>r.json()).then(d=> {
      if (d.status==='success') {
        let html = '<ol>';
        d.monthly.forEach(m => html += `<li>${m.month}: ${m.cnt}</li>`);
        html += '</ol>'; out.innerHTML = html;
      }
    });
  });
});