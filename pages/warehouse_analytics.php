<?php
session_start(); 

if (!isset($_SESSION['id'])) {
    header('Location:https://log1.imarketph.com');
    exit();
}
include '../layout/adminLayout.php';

$children = <<<HTML
<div class="p-6">

    <!-- Breadcrumb -->
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Logistics Dashboard</a> &gt;
        <span>Warehouse Analytics</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Warehouse Analytics</h1>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-gray-500 text-sm mb-2">Total Stock Items</h2>
            <p class="text-2xl font-bold text-indigo-600">320</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-gray-500 text-sm mb-2">Low Stock Alerts</h2>
            <p class="text-2xl font-bold text-red-600">12</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-gray-500 text-sm mb-2">Incoming Shipments</h2>
            <p class="text-2xl font-bold text-green-600">18</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-gray-500 text-sm mb-2">Outgoing Shipments</h2>
            <p class="text-2xl font-bold text-yellow-500">25</p>
        </div>

    </div>

    <!-- Stock Trends Chart -->
    <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
        <h2 class="text-xl font-semibold mb-4">Stock Movement Trends</h2>
            <canvas id="stockChart" class="w-full" style="height:320px;" height="320"></canvas>
            <div id="shipmentsWrapper" class="mt-6 hidden">
                <h3 class="text-lg font-semibold mb-3">Shipments Over Time</h3>
                <canvas id="shipmentsChart" class="w-full" style="height:320px;" height="400"></canvas>
            </div>
        </div>

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        async function loadAnalytics(){
            // instrumentation: count calls
            window._analyticsCalls = (window._analyticsCalls || 0) + 1;
            console.log('loadAnalytics called', window._analyticsCalls);
            const dbgEl = document.getElementById('analyticsDebug');
            if (dbgEl) dbgEl.textContent = 'loadAnalytics calls: ' + window._analyticsCalls;

            // prevent multiple runs causing repeated chart creation
            if (window._analyticsLoaded) return;
            if (window._analyticsLoading) return;
            // mark loaded early to avoid re-entrancy loops
            window._analyticsLoading = true;
            window._analyticsLoaded = true;
            // safe number parser
            const parseNum = v => { if (v === null || v === undefined) return 0; const s = String(v).replace(/,/g,'').trim(); const n = parseFloat(s); return Number.isFinite(n)?n:0 };

            try {
                const r = await fetch('https://log1.imarketph.com/api/analytics.php', { credentials: 'same-origin' });
                let j = null;
                if (r.ok) j = await r.json();

                // If analytics.php didn't return useful KPIs, fallback to inventory for totals
                if (!j || (!j.total_stock_items && !j.timeseries)) {
                    const inv = await fetch('https://log1.imarketph.com/api/inventory.php', { credentials: 'same-origin' });
                    const items = await inv.json();
                    const totalStock = Array.isArray(items) ? items.reduce((s,it)=>s+parseNum(it.stock_level),0) : 0;
                    const lowStock = Array.isArray(items) ? items.filter(it => parseNum(it.stock_level) <= parseNum(it.reorder_level)).length : 0;
                    j = j || {};
                    j.total_stock_items = j.total_stock_items || totalStock;
                    j.low_stock_alerts = j.low_stock_alerts || lowStock;
                }

                // Update KPI cards (existing selectors)
                const indigoEl = document.querySelector('.text-2xl.font-bold.text-indigo-600');
                const redEl = document.querySelectorAll('.text-2xl.font-bold.text-red-600')[0];
                const greenEl = document.querySelectorAll('.text-2xl.font-bold.text-green-600')[0];
                const yellowEl = document.querySelectorAll('.text-2xl.font-bold.text-yellow-500')[0];
                if (indigoEl) indigoEl.textContent = (j.total_stock_items ?? 0).toLocaleString();
                if (redEl) redEl.textContent = (j.low_stock_alerts ?? 0).toLocaleString();
                if (greenEl) greenEl.textContent = (j.incoming_shipments ?? '-');
                if (yellowEl) yellowEl.textContent = (j.outgoing_shipments ?? '-');

                // Render KPI bar chart (total, incoming, outgoing, low)
                const kpiLabels = ['Total Stock','Incoming','Outgoing','Low Stock'];
                const kpiValues = [ parseNum(j.total_stock_items), parseNum(j.incoming_shipments), parseNum(j.outgoing_shipments), parseNum(j.low_stock_alerts) ];
                const kctx = document.getElementById('stockChart').getContext('2d');
                if (window.kpiChart) window.kpiChart.destroy();
                window.kpiChart = new Chart(kctx, {
                    type: 'bar',
                    data: { labels: kpiLabels, datasets: [{ label: 'Counts', data: kpiValues, backgroundColor: ['rgba(59,130,246,0.8)','rgba(34,197,94,0.8)','rgba(234,179,8,0.8)','rgba(244,63,94,0.8)'], borderColor: ['rgba(29,78,216,1)','rgba(16,185,129,1)','rgba(180,138,21,1)','rgba(190,18,60,1)'], borderWidth:1 }] },
                    options: { responsive:false, maintainAspectRatio:false, animation:false, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true } } }
                });

                // If timeseries available, render shipments line chart
                if (j.timeseries && (Array.isArray(j.timeseries.incoming) || Array.isArray(j.timeseries.outgoing))) {
                    document.getElementById('shipmentsWrapper').classList.remove('hidden');
                    const sctx = document.getElementById('shipmentsChart').getContext('2d');

                    // Build robust multi-line datasets (incoming, outgoing, delivered, canceled, rates)
                    const labels = j.timeseries.labels || [];
                    const incoming = (j.timeseries.incoming || []).map(v => parseNum(v));
                    const outgoing = (j.timeseries.outgoing || []).map(v => parseNum(v));
                    const delivered = Array.isArray(j.timeseries.delivered) ? j.timeseries.delivered.map(v => parseNum(v)) : null;
                    const canceled = Array.isArray(j.timeseries.canceled) ? j.timeseries.canceled.map(v => parseNum(v)) : null;

                    const providedDeliveryRate = Array.isArray(j.timeseries.delivery_rate) ? j.timeseries.delivery_rate.map(v => parseNum(v)) : null;
                    const providedCancellationRate = Array.isArray(j.timeseries.cancellation_rate) ? j.timeseries.cancellation_rate.map(v => parseNum(v)) : null;

                    let deliveryRate = providedDeliveryRate;
                    let cancellationRate = providedCancellationRate;
                    // Derive rates as percent if not provided
                    if (!deliveryRate && delivered && Array.isArray(incoming)) {
                        deliveryRate = incoming.map((inc, i) => {
                            const del = delivered[i] || 0;
                            return inc > 0 ? (del / inc) * 100 : 0;
                        });
                    }
                    if (!cancellationRate && canceled && Array.isArray(incoming)) {
                        cancellationRate = incoming.map((inc, i) => {
                            const can = canceled[i] || 0;
                            return inc > 0 ? (can / inc) * 100 : 0;
                        });
                    }

                    const datasets = [];
                    datasets.push({ label:'Incoming', data: incoming, borderColor:'rgba(34,197,94,1)', backgroundColor:'rgba(34,197,94,0.12)', tension:0.2, fill:true, pointRadius:4, yAxisID: 'y' });
                    datasets.push({ label:'Outgoing', data: outgoing, borderColor:'rgba(234,179,8,1)', backgroundColor:'rgba(234,179,8,0.12)', tension:0.2, fill:true, pointRadius:4, yAxisID: 'y' });
                    if (delivered) datasets.push({ label:'Delivered', data: delivered, borderColor:'rgba(99,102,241,1)', fill:false, tension:0.2, borderDash:[4,2], pointRadius:3, yAxisID: 'y' });
                    if (canceled) datasets.push({ label:'Canceled', data: canceled, borderColor:'rgba(244,63,94,1)', fill:false, tension:0.2, borderDash:[4,2], pointRadius:3, yAxisID: 'y' });
                    if (deliveryRate) datasets.push({ label:'Delivery Rate (%)', data: deliveryRate.map(v => (v > 1 ? v : v * 100)), borderColor:'rgba(16,185,129,1)', fill:false, tension:0.2, pointRadius:3, yAxisID: 'yRate' });
                    if (cancellationRate) datasets.push({ label:'Cancellation Rate (%)', data: cancellationRate.map(v => (v > 1 ? v : v * 100)), borderColor:'rgba(234,88,12,1)', fill:false, tension:0.2, pointRadius:3, yAxisID: 'yRate' });

                    // show plotted series in debug
                    const dbg = document.getElementById('analyticsDebug'); if (dbg) dbg.textContent = 'Plotted series: ' + datasets.map(ds => ds.label).join(', ');

                    if (window.shipmentsChartInstance) try { window.shipmentsChartInstance.destroy(); } catch(e){}
                    window.shipmentsChartInstance = new Chart(sctx, {
                        type: 'line',
                        data: { labels: labels, datasets: datasets },
                        options: { responsive:false, maintainAspectRatio:false, animation:false, plugins:{ legend:{ position:'top' } }, interaction:{ mode:'nearest', intersect:false }, scales:{ x:{ ticks:{ autoSkip:true } }, y:{ beginAtZero:true, title: { display:true, text:'Count' } }, yRate: { position:'right', min:0, max:100, title: { display:true, text:'Percent' }, ticks:{ callback: v => v + '%' } } } }
                    });
                } else {
                    document.getElementById('shipmentsWrapper').classList.add('hidden');
                }

            } catch (err) {
                console.error(err);
                document.getElementById('stockChart').parentElement.innerHTML = '<div class="text-center text-danger">Failed to load analytics.</div>';
            } finally {
                window._analyticsLoading = false;
                window._analyticsLoaded = true;
            }
        }

        // Run once when DOM is ready
        document.addEventListener('DOMContentLoaded', loadAnalytics, { once: true });
        </script>
HTML;

adminLayout($children);
?>