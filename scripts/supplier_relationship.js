document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openBtn = document.getElementById('openModal');
    const closeBtn = document.getElementById('closeModal');
    const closeBtnX = document.getElementById('closeModalBtn');
    const form = document.getElementById('supplierForm');
    const grid = document.getElementById('supplierGrid');
    const empty = document.getElementById('emptyState');
    const filterPerf = document.getElementById('filterPerformance');
    const applyFilter = document.getElementById('applyFilter');
    const clearFilter = document.getElementById('clearFilter');

    function openModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
    function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    closeBtnX.addEventListener('click', closeModal);

    function fetchSuppliers(perf = 'all') {
        const url = perf && perf !== 'all' ? `../api/supplier_relationship.php?performance=${perf}` : '../api/supplier_relationship.php';
        fetch(url).then(r => r.json()).then(d => {
            grid.innerHTML = '';
            if (d.suppliers && d.suppliers.length) {
                d.suppliers.forEach(s => {
                    const perfColor = s.performance_rating === 'Excellent' ? 'text-green-600' : s.performance_rating === 'Good' ? 'text-blue-600' : 'text-yellow-600';
                    const card = document.createElement('div');
                    card.className = 'bg-white p-4 rounded shadow';
                    card.innerHTML = `
                        <h3 class='font-bold text-lg mb-2'>${s.supplier_name}</h3>
                        <p class='text-sm text-gray-600 mb-2'>${s.contact_email}</p>
                        <p class='${perfColor} font-semibold mb-2'>${s.performance_rating}</p>
                        <div class='text-sm text-gray-700 mb-3'>
                            <p>On-Time: ${s.ontime_delivery}%</p>
                            <p>Quality: ${s.quality_score}%</p>
                        </div>
                        <button onclick="deleteSupplier(${s.id})" class='w-full bg-red-200 text-red-800 px-2 py-1 rounded text-sm hover:bg-red-300'>Delete</button>
                    `;
                    grid.appendChild(card);
                });
                empty.classList.add('hidden');
            } else {
                empty.classList.remove('hidden');
            }
        });
    }

    form.addEventListener('submit', e => {
        e.preventDefault();
        const supplierName = document.getElementById('supplier_name').value;
        
        if (!supplierName) {
            Toastify({
                text: 'Please enter supplier name',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#ef4444',
            }).showToast();
            return;
        }
        
        const data = {
            supplier_name: supplierName,
            contact_email: document.getElementById('contact_email').value,
            performance_rating: document.getElementById('performance_rating').value,
            ontime_delivery: document.getElementById('ontime_delivery').value,
            quality_score: document.getElementById('quality_score').value
        };
        
        console.log('Submitting relationship data:', data);
        
        fetch('../api/supplier_relationship.php', { method: 'POST', body: JSON.stringify(data) })
            .then(r => r.json())
            .then(result => {
                console.log('Relationship response:', result);
                if(result.status === 'success') {
                    Toastify({
                        text: result.message || 'Supplier relationship updated successfully',
                        duration: 3000,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: '#10b981',
                    }).showToast();
                    closeModal(); 
                    form.reset(); 
                    fetchSuppliers();
                } else {
                    throw new Error(result.message || 'Failed to update relationship');
                }
            })
            .catch(error => {
                console.error('Relationship error:', error);
                Toastify({
                    text: 'Error: ' + error.message,
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#ef4444',
                }).showToast();
            });
    });

    applyFilter.addEventListener('click', () => fetchSuppliers(filterPerf.value));
    clearFilter.addEventListener('click', () => { filterPerf.value = 'all'; fetchSuppliers(); });

    window.deleteSupplier = (id) => {
        if (confirm('Delete this supplier?')) {
            fetch(`../api/supplier_relationship.php?id=${id}`, { method: 'DELETE' })
                .then(r => r.json())
                .then(() => fetchSuppliers());
        }
    };

    fetchSuppliers();
});
