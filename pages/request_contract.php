<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location:../index.php');
    exit();
}
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <span>Logistic Tracking - Request Contract</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Request Contract</h1>
        <button id="openContractModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Request Contract</button>
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4">Contract Requests</h2>
        <div id="contractsList" class="space-y-4">
            <!-- Contracts will be populated here -->
        </div>
    </div>
</div>

<!-- Contract Modal -->
<div id="contractModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-[100]">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
        <h2 class="text-2xl font-bold mb-4">Request Contract</h2>
        <form id="contractForm" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium">Project</label>
                <select id="contract_project_id" class="w-full border rounded px-3 py-2">
                    <option value="">Select Project</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Contract Title *</label>
                <input id="contract_title" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., Transportation Contract" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Supplier Name *</label>
                <input id="supplier_name" type="text" class="w-full border rounded px-3 py-2" placeholder="Supplier company name" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Contract Value</label>
                <input id="contract_value" type="number" step="0.01" class="w-full border rounded px-3 py-2" placeholder="0.00" />
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeContractModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Submit</button>
            </div>
        </form>
        <button id="closeContractModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const contractModal = document.getElementById('contractModal');
    const openContractModalBtn = document.getElementById('openContractModal');
    const closeContractModalBtn = document.getElementById('closeContractModal');
    const closeContractModalBtn2 = document.getElementById('closeContractModalBtn');
    const contractForm = document.getElementById('contractForm');

    function closeContractModal() {
        contractModal.classList.add('hidden');
        contractModal.classList.remove('flex');
        contractForm.reset();
    }

    openContractModalBtn.addEventListener('click', () => {
        loadProjectsForContract();
        contractModal.classList.remove('hidden');
        contractModal.classList.add('flex');
    });

    closeContractModalBtn.addEventListener('click', closeContractModal);
    closeContractModalBtn2.addEventListener('click', closeContractModal);

    async function fetchContracts() {
        try {
            const res = await fetch('../api/request_contract.php');
            const data = await res.json();
            if (data.status === 'success') {
                renderContracts(data.contracts);
            }
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error loading contracts', duration: 3000, backgroundColor: '#ef4444' }).showToast();
        }
    }

    function renderContracts(contracts) {
        const list = document.getElementById('contractsList');
        list.innerHTML = contracts.map(c => `
            <div class="border rounded p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-bold text-lg">${c.contract_title}</h3>
                        <p class="text-gray-600">Supplier: ${c.supplier_name}</p>
                        <p class="text-sm">Project: ${c.project_name || 'N/A'}</p>
                        <p class="text-sm">Value: ₱${c.contract_value || 'N/A'}</p>
                        <p class="text-sm">Requested: ${new Date(c.request_date).toLocaleDateString()}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 rounded text-xs ${c.status === 'Approved' ? 'bg-green-100 text-green-800' : c.status === 'Requested' ? 'bg-yellow-100 text-yellow-800' : c.status === 'Signed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100'}">${c.status}</span>
                        <div class="mt-2 space-x-2">
                            <button onclick='updateContractStatus(${c.id})' class="text-indigo-600 text-sm">Update Status</button>
                            <button onclick="archiveContract(${c.id})" class="text-yellow-600 text-sm">Archive</button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function updateContractStatus(id) {
        const status = prompt('Enter new status (Pending/Approved/Rejected):');
        if (status) {
            fetch('../api/request_contract.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ contract_id: id, status })
            }).then(res => res.json()).then(data => {
                if (data.status === 'success') {
                    Toastify({ text: data.message, duration: 2500, backgroundColor: '#10b981' }).showToast();
                    fetchContracts();
                }
            });
        }
    }

    async function archiveContract(id) {
        if (!confirm('Archive this contract request? It will be recoverable from Archive.')) return;
        try {
            const payload = { archive_type: 'contract_request', item_id: id, original_table: 'contract_requests', reason: 'Archived from UI' };
            const res = await fetch('../api/archive_management.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            const data = await res.json();
            if (data.status === 'success') {
                Toastify({ text: data.message || 'Contract request archived', duration: 2500, backgroundColor: '#10b981' }).showToast();
                fetchContracts();
            }
        } catch (err) {
            Toastify({ text: 'Error archiving contract', duration: 3000, backgroundColor: '#ef4444' }).showToast();
        }
    }

    async function loadProjectsForContract() {
        const res = await fetch('../api/project_planning_request.php');
        const data = await res.json();
        if (data.status === 'success') {
            const select = document.getElementById('contract_project_id');
            select.innerHTML = '<option value="">Select Project</option>' + data.projects.map(p => `<option value="${p.id}">${p.project_name}</option>`).join('');
        }
    }

    contractForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            project_id: document.getElementById('contract_project_id').value,
            contract_title: document.getElementById('contract_title').value,
            supplier_name: document.getElementById('supplier_name').value,
            contract_value: document.getElementById('contract_value').value
        };
        try {
            const res = await fetch('../api/request_contract.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({ text: result.message, duration: 2500, backgroundColor: '#10b981' }).showToast();
                closeContractModal();
                fetchContracts();
            }
        } catch (err) {
            Toastify({ text: 'Error submitting contract', duration: 3000, backgroundColor: '#ef4444' }).showToast();
        }
    });

    window.updateContractStatus = updateContractStatus;
    window.archiveContract = archiveContract;

    fetchContracts();
});
</script>
HTML;
adminLayout($children);
?>