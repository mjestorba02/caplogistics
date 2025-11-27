<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location: https://log1.imarketph.com'); exit(); }
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">Invoice Records</h2>
    <div class="flex items-center gap-2">
      <button id="exportCsv" class="px-3 py-1 bg-blue-600 text-white rounded">Export CSV</button>
      <button id="btnNewInvoice" class="px-3 py-1 bg-green-600 text-white rounded">New Invoice</button>
    </div>
  </div>

  <div class="mb-3">
    <input id="searchBox" type="search" placeholder="Search Invoice No, Customer, Delivery Route, Date, Subtotal..." class="w-full p-3 border rounded" />
  </div>

  <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <table id="invoiceTable" class="w-full text-left">
      <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
        <tr>
          <th class="p-4 font-semibold">Inv #</th>
          <th class="p-4 font-semibold">Customer</th>
          <th class="p-4 font-semibold">Route</th>
          <th class="p-4 font-semibold">Date</th>
          <th class="p-4 font-semibold">Due</th>
          <th class="p-4 font-semibold">Subtotal</th>
          <th class="p-4 font-semibold">Actions</th>
      </thead>
      <tbody id="invoiceBody"></tbody>
    </table>
  </div>

  <!-- View Modal -->
  <div id="viewModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded p-6 w-11/12 md:w-4/5 lg:w-3/4 max-h-screen overflow-y-auto">
      <h3 class="text-lg font-semibold mb-4">Invoice Details</h3>
      <div id="viewContent" class="mb-4"></div>
      <div class="text-right">
        <button id="closeView" class="px-3 py-1 bg-gray-300 rounded">Close</button>
      </div>
    </div>
  </div>

  <!-- Create/Edit Modal -->
  <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded p-4 w-11/12 md:w-2/3 lg:w-1/2">
      <h3 class="text-lg font-semibold mb-2">Invoice</h3>
      <form id="invoiceForm">
        <input type="hidden" id="invoiceId">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
          <input id="invoiceNumber" placeholder="Invoice No." class="p-2 border rounded" required />
          <input id="userName" placeholder="Customer Name" class="p-2 border rounded" />
          <input id="deliveryFrom" placeholder="Delivery From" class="p-2 border rounded" />
          <input id="deliveryTo" placeholder="Delivery To" class="p-2 border rounded" />
          <input id="date" type="date" class="p-2 border rounded" />
          <input id="dueDate" type="date" class="p-2 border rounded" />
          <input id="subtotal" type="number" step="0.01" placeholder="Subtotal" class="p-2 border rounded" />
          <textarea id="notes" placeholder="Notes" class="p-2 border rounded col-span-1 md:col-span-2"></textarea>
        </div>
        <div class="mt-4 text-right">
          <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded">Save</button>
          <button type="button" id="closeEdit" class="px-3 py-1 bg-gray-300 rounded">Cancel</button>
        </div>
      </form>
    </div>
  </div>

</div>

<script>
const API = 'https://log1.imarketph.com/api/invoices.php';
async function fetchInvoices(q=''){
  const url = new URL(API, location.origin);
  const resp = await fetch(url.toString(), { credentials: 'same-origin' });
  if (!resp.ok) return [];
  const data = await resp.json();
  return data;
}

function renderRow(inv){
  const deliveryRoute = `${inv.delivery_from || ''} → ${inv.delivery_to || ''}`;
  return `<tr class="border-b border-gray-200 hover:bg-blue-50 transition-colors duration-200"><td class="p-4 font-medium text-gray-900">${inv.invoice_number||''}</td><td class="p-4 text-gray-700">${inv.user_name||''}</td><td class="p-4 text-gray-700">${deliveryRoute}</td><td class="p-4 text-gray-700">${inv.date||''}</td><td class="p-4 text-gray-700">${inv.due_date||''}</td><td class="p-4 font-semibold text-green-600">₱${Number(inv.subtotal||0).toFixed(2)}</td><td class="p-4"><div class="flex space-x-2"><button data-id="${inv.id}" class="viewBtn inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-lg transition-all duration-200 transform hover:scale-105" title="View Invoice"><svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>View</button><button data-id="${inv.id}" class="editBtn inline-flex items-center px-3 py-1.5 text-sm font-medium text-green-700 bg-green-100 hover:bg-green-200 rounded-lg transition-all duration-200 transform hover:scale-105" title="Edit Invoice"><svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>Edit</button><button data-id="${inv.id}" class="delBtn inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-700 bg-red-100 hover:bg-red-200 rounded-lg transition-all duration-200 transform hover:scale-105" title="Delete Invoice"><svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>Delete</button></div></td></tr>`;
}

async function loadAndRender(){
  const invoices = await fetchInvoices();
  const body = document.getElementById('invoiceBody');
  body.innerHTML = invoices.map(renderRow).join('') || '<tr><td class="p-4" colspan="6">No invoices found</td></tr>';
  document.querySelectorAll('.viewBtn').forEach(b=>b.addEventListener('click', async e=>{
    const id = e.currentTarget.dataset.id;
    const r = await fetch(API + '?id=' + encodeURIComponent(id), { credentials: 'same-origin' });
    if (!r.ok) {
      alert('Failed to load invoice');
      return;
    }
    const inv = await r.json();
    const html = buildInvoiceHtml(inv);
    document.getElementById('viewContent').innerHTML = html;
    document.getElementById('viewModal').classList.remove('hidden');
  }));
  document.querySelectorAll('.editBtn').forEach(b=>b.addEventListener('click', async e=>{
    const id = e.currentTarget.dataset.id;
    const r = await fetch(API + '?id=' + encodeURIComponent(id), { credentials: 'same-origin' });
    const inv = await r.json();
    document.getElementById('invoiceId').value = inv.id;
    document.getElementById('invoiceNumber').value = inv.invoice_number||'';
    document.getElementById('userName').value = inv.user_name||'';
    document.getElementById('deliveryFrom').value = inv.delivery_from||'';
    document.getElementById('deliveryTo').value = inv.delivery_to||'';
    document.getElementById('date').value = inv.date||'';
    document.getElementById('dueDate').value = inv.due_date||'';
    document.getElementById('subtotal').value = inv.subtotal||0;
    document.getElementById('notes').value = inv.notes||'';
    document.getElementById('editModal').classList.remove('hidden');
  }));
  document.querySelectorAll('.delBtn').forEach(b=>b.addEventListener('click', async e=>{
    if (!confirm('Delete invoice?')) return;
    const id = e.currentTarget.dataset.id;
    const r = await fetch(API + '?id=' + encodeURIComponent(id), { method:'DELETE', credentials:'same-origin' });
    if (r.ok) { await loadAndRender(); }
  }));

  // helper: build a presentable invoice HTML fragment
  function buildInvoiceHtml(inv) {
    const companyName = 'Logistics1 Ecommerce';
    const companyAddress = '123 Warehouse Ave, City, Country';
    const companyPhone = '+1 (555) 123-4567';
    const companyEmail = 'info@logistics1.com';
    const logoUrl = 'https://log1.imarketph.com/images/logo.jpg'; // adjust path as needed
    const invoiceNumber = inv.invoice_number || ('#' + (inv.id || ''));
    const date = inv.date || (inv.created_at ? inv.created_at.split(' ')[0] : '');
    const due = inv.due_date || '';
    const subtotal = Number(inv.subtotal || 0).toFixed(2);
    const notes = inv.notes || '';
    const shipment = inv.shipment_id ? `<div><strong>Shipment ID:</strong> ${inv.shipment_id}</div>` : '';

    // Decode items JSON and calculate subtotal if needed
    let items = [];
    try {
      items = inv.items ? (typeof inv.items === 'string' ? JSON.parse(inv.items) : inv.items) : [];
    } catch (e) {
      items = [];
    }

    // If the invoice contains items (array), render them; otherwise show subtotal only
    let itemsHtml = '';
    if (Array.isArray(items) && items.length) {
      itemsHtml = `<table style="width:100%;border-collapse:collapse;margin-top:20px;border:1px solid #ddd"><thead><tr style="background:#f9f9f9"><th style="border:1px solid #ddd;padding:8px;text-align:left">Description</th><th style="border:1px solid #ddd;padding:8px;text-align:center">Qty</th><th style="border:1px solid #ddd;padding:8px;text-align:right">Unit Price</th><th style="border:1px solid #ddd;padding:8px;text-align:right">Total</th></tr></thead><tbody>`;
      items.forEach(it=>{
        itemsHtml += `<tr><td style="border:1px solid #ddd;padding:8px">${escapeHtml(it.name||it.description||'Item')}</td><td style="border:1px solid #ddd;padding:8px;text-align:center">${it.quantity||1}</td><td style="border:1px solid #ddd;padding:8px;text-align:right">₱${Number(it.unit_price||0).toFixed(2)}</td><td style="border:1px solid #ddd;padding:8px;text-align:right">₱${Number((it.quantity||1)*(it.unit_price||0)).toFixed(2)}</td></tr>`;
      });
      itemsHtml += `</tbody></table>`;
    } else {
      itemsHtml = `<div style="margin-top:20px;padding:10px;border:1px solid #ddd;background:#f9f9f9"><strong>Subtotal:</strong> ₱${subtotal}</div>`;
    }

    const footer = notes ? `<div style="margin-top:20px;padding:10px;border:1px solid #ddd;background:#f9f9f9"><strong>Notes:</strong><br>${escapeHtml(notes)}</div>` : '';

    const invoiceHtml = `
      <div style="font-family:Arial,Helvetica,sans-serif;max-width:800px;margin:0 auto;padding:20px;border:1px solid #ccc;background:#fff">
        <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #333;padding-bottom:10px">
          <div style="display:flex;align-items:center">
            <img src="${logoUrl}" alt="Logo" style="height:60px;margin-right:15px">
            <div>
              <h2 style="margin:0;color:#333">${escapeHtml(companyName)}</h2>
              <div style="color:#666;font-size:14px">${escapeHtml(companyAddress)}</div>
              <div style="color:#666;font-size:14px">${escapeHtml(companyPhone)} | ${escapeHtml(companyEmail)}</div>
            </div>
          </div>
          <div style="text-align:right">
            <h1 style="margin:0;color:#333;font-size:28px">INVOICE</h1>
            <div style="font-size:16px;font-weight:bold">${escapeHtml(invoiceNumber)}</div>
            <div style="color:#666">Date: ${escapeHtml(date)}</div>
            <div style="color:#666">Due Date: ${escapeHtml(due)}</div>
          </div>
        </div>

        <div style="display:flex;justify-content:space-between;margin-top:20px">
          <div style="flex:1">
            <h3 style="margin:0 0 10px 0;color:#333">Bill To:</h3>
            <div>${escapeHtml(inv.user_name || inv.user || 'Customer')}</div>
            <div>${escapeHtml(inv.user_address || '')}</div>
          </div>
          <div style="flex:1;text-align:right">
            <h3 style="margin:0 0 10px 0;color:#333">Invoice Details:</h3>
            <div><strong>Delivery Route:</strong> ${escapeHtml(inv.delivery_from || '')} → ${escapeHtml(inv.delivery_to || '')}</div>
            ${shipment}
          </div>
        </div>

        ${itemsHtml}

        <div style="display:flex;justify-content:flex-end;margin-top:20px">
          <div style="text-align:right;min-width:200px">
            <div style="padding:5px 0;border-bottom:1px solid #ddd"><span style="font-weight:bold">Subtotal:</span> ₱${subtotal}</div>
            <div style="padding:5px 0;border-bottom:1px solid #ddd"><span style="font-weight:bold">Tax (0%):</span> ₱0.00</div>
            <div style="padding:10px 0;font-size:18px;font-weight:bold;border-top:2px solid #333">Total: ₱${subtotal}</div>
          </div>
        </div>

        ${footer}

        <div style="margin-top:30px;text-align:center;color:#666;font-size:12px;border-top:1px solid #ddd;padding-top:10px">
          Thank you for your business! Payment is due within 30 days. Please include the invoice number on your payment.
        </div>

        <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end">
          <button id="printInvoiceBtn" style="padding:8px 16px;background:#007bff;color:#fff;border:none;border-radius:4px;cursor:pointer">Print Invoice</button>
        </div>
      </div>
    `;
    // attach print handler after returning
    setTimeout(()=>{
      const btn = document.getElementById('printInvoiceBtn');
      if (btn) btn.addEventListener('click', ()=>printInvoiceWindow(invoiceHtml));
    }, 40);
    return invoiceHtml;
  }

  function escapeHtml(str) {
    if (!str && str !== 0) return '';
    return String(str).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#039;"})[m]; });
  }

  // open a printable window with the invoice HTML
  function printInvoiceWindow(contentHtml) {
    const win = window.open('', '_blank', 'width=900,height=700');
    if (!win) { alert('Popup blocked - allow popups to print invoice'); return; }
    const doc = win.document.open();
    const full = `<!doctype html><html><head><meta charset="utf-8"><title>Invoice</title><style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;color:#222} table{width:100%;border-collapse:collapse}</style></head><body>${contentHtml}<script>window.onload=function(){setTimeout(()=>{window.print();},200);}<\/script></body></html>`;
    doc.write(full);
    doc.close();
  }
}

document.getElementById('btnNewInvoice').addEventListener('click', ()=>{
  document.getElementById('invoiceForm').reset();
  document.getElementById('invoiceId').value='';
  document.getElementById('editModal').classList.remove('hidden');
});

document.getElementById('closeView').addEventListener('click', ()=>document.getElementById('viewModal').classList.add('hidden'));
document.getElementById('closeEdit').addEventListener('click', ()=>document.getElementById('editModal').classList.add('hidden'));

document.getElementById('invoiceForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const id = document.getElementById('invoiceId').value;
  const payload = {
    invoice_number: document.getElementById('invoiceNumber').value,
    user_name: document.getElementById('userName').value,
    delivery_from: document.getElementById('deliveryFrom').value,
    delivery_to: document.getElementById('deliveryTo').value,
    date: document.getElementById('date').value,
    due_date: document.getElementById('dueDate').value,
    subtotal: parseFloat(document.getElementById('subtotal').value||0),
    notes: document.getElementById('notes').value
  };
  const opts = { headers:{'Content-Type':'application/json'}, credentials:'same-origin' };
  if (id) {
    const r = await fetch(API + '?id=' + encodeURIComponent(id), { ...opts, method:'PUT', body:JSON.stringify(payload) });
    if (r.ok) { document.getElementById('editModal').classList.add('hidden'); await loadAndRender(); }
  } else {
    const r = await fetch(API, { ...opts, method:'POST', body:JSON.stringify(payload) });
    if (r.ok) { document.getElementById('editModal').classList.add('hidden'); await loadAndRender(); }
  }
});

document.getElementById('exportCsv').addEventListener('click', async ()=>{
  const data = await fetchInvoices();
  if (!data || data.length===0) return alert('No invoices to export');
  const rows = [Object.keys(data[0])].concat(data.map(r=>Object.values(r)));
  const csv = rows.map(r=>r.map(c=>`"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
  const blob = new Blob([csv], { type:'text/csv' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a'); a.href = url; a.download = 'invoices.csv'; a.click(); URL.revokeObjectURL(url);
});

document.getElementById('searchBox').addEventListener('input', async (e)=>{
  const q = e.target.value.toLowerCase();
  const rows = Array.from(document.querySelectorAll('#invoiceBody tr'));
  rows.forEach(r=> r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none');
});

// initial load
loadAndRender();
</script>
HTML;

adminLayout($children);

?>
