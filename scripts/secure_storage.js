document.addEventListener('DOMContentLoaded', () => {
  const table = document.getElementById('storageTable');
  const empty = document.getElementById('emptyState');

  function load() {
    fetch('../api/documents_storage.php').then(r=>r.json()).then(d=>{
      table.innerHTML='';
      if (d.files && d.files.length) {
        d.files.forEach(f=>{
          const tr = document.createElement('tr');
          tr.innerHTML = `<td class="px-6 py-3">${f.id}</td>
                          <td class="px-6 py-3">${f.doc_code||''}</td>
                          <td class="px-6 py-3">${f.file_name||''}</td>
                          <td class="px-6 py-3">${f.linked_module||''}</td>
                          <td class="px-6 py-3">${f.status||''}</td>
                          <td class="px-6 py-3"><a href="../api/documents_download.php?id=${f.id}" class="text-indigo-600">Download</a></td>`;
          table.appendChild(tr);
        });
        empty.classList.add('hidden');
      } else empty.classList.remove('hidden');
    });
  }

  load();
});