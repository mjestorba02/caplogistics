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
        <span>Document Tracking - Document Request</span>
    </div>

    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">Request Document</h2>
        <form id="requestForm" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium">Document Type *</label>
                <input id="document_type" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g., Invoice, Contract" required />
            </div>
            <div>
                <label class="block text-gray-700 font-medium">Description</label>
                <textarea id="description" class="w-full border rounded px-3 py-2" rows="3" placeholder="Additional details"></textarea>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700">Submit Request</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('requestForm');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            document_type: document.getElementById('document_type').value,
            description: document.getElementById('description').value
        };

        try {
            const res = await fetch('../api/document_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.status === 'success') {
                Toastify({ text: result.message || 'Request submitted', duration: 2500, gravity: 'top', position: 'right', backgroundColor: '#10b981' }).showToast();
                form.reset();
            } else {
                throw new Error(result.message || 'Failed');
            }
        } catch (err) {
            console.error(err);
            Toastify({ text: 'Error submitting request', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
        }
    });
});
</script>
HTML;
adminLayout($children);
?>