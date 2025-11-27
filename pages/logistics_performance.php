<?php
session_start(); 

if (!isset($_SESSION['id'])) {
    header('Location:http://localhost/caplog1');
    exit();
}
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6">

    <!-- Breadcrumb -->
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Logistics Dashboard</a> &gt;
        <span>Logistics Performance</span>
    </div>

    <!-- Page Header -->
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Logistics Performance</h1>
            <p class="text-gray-500 mt-1">Overview of delivery rate, transit time, delays, and shipment status.</p>
        </div>
        <div class="flex items-center gap-3">
            <button id="refreshBtn" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-800">Refresh</button>
        </div>
    </div>

    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="kpiGrid">

        <!-- On-Time/Delivery Rate -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center justify-between">
            <div class="w-full">
                <div class="flex items-baseline justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">On-Time Delivery</h2>
                    <span id="kpiOnTimePct" class="text-2xl font-bold text-green-600">0%</span>
                </div>
                <p class="text-gray-500 text-sm">Delivered shipments as a percentage of all shipments</p>
                <div class="w-full bg-gray-200 h-2 rounded mt-3">
                    <div id="barOnTime" class="bg-green-600 h-2 rounded" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <!-- Average Transit Time (Open) -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center justify-between">
            <div class="w-full">
                <div class="flex items-baseline justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">Avg Transit Time (Open)</h2>
                    <span id="kpiAvgTransit" class="text-2xl font-bold text-blue-600">0 days</span>
                </div>
                <p class="text-gray-500 text-sm">Average days since dispatch for shipments still in transit</p>
                <div class="w-full bg-gray-200 h-2 rounded mt-3">
                    <div id="barAvgTransit" class="bg-blue-600 h-2 rounded" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <!-- Delayed Shipments (Open > 7 days) -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center justify-between">
            <div class="w-full">
                <div class="flex items-baseline justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">Delayed Shipments</h2>
                    <span id="kpiDelayedCount" class="text-2xl font-bold text-red-600">0</span>
                </div>
                <p class="text-gray-500 text-sm">In transit for more than 7 days</p>
                <div class="w-full bg-gray-200 h-2 rounded mt-3">
                    <div id="barDelayed" class="bg-red-600 h-2 rounded" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <!-- Top Destination -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center justify-between">
            <div class="w-full">
                <div class="flex items-baseline justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">Top Destination</h2>
                    <span id="kpiTopDestination" class="text-2xl font-bold text-yellow-600">N/A</span>
                </div>
                <p class="text-gray-500 text-sm">Most frequent destination by shipments</p>
            </div>
        </div>

        <!-- Shipments Completed -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center justify-between">
            <div class="w-full">
                <div class="flex items-baseline justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">Shipments Completed</h2>
                    <span id="kpiCompleted" class="text-2xl font-bold text-purple-600">0</span>
                </div>
                <p class="text-gray-500 text-sm">Total delivered shipments</p>
                <div class="w-full bg-gray-200 h-2 rounded mt-3">
                    <div id="barCompleted" class="bg-purple-600 h-2 rounded" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <!-- Pending Deliveries -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center justify-between">
            <div class="w-full">
                <div class="flex items-baseline justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">Pending Deliveries</h2>
                    <span id="kpiPending" class="text-2xl font-bold text-orange-600">0</span>
                </div>
                <p class="text-gray-500 text-sm">Shipments pending or in transit</p>
                <div class="w-full bg-gray-200 h-2 rounded mt-3">
                    <div id="barPending" class="bg-orange-600 h-2 rounded" style="width: 0%"></div>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
(function(){
    const API_URL = 'http://localhost/caplog1/api/shipments.php';
    const thresholdDaysDelayed = 7; // consider delayed if in transit more than this many days

    const refreshBtn = document.getElementById('refreshBtn');

    // KPI elements
    const onTimePctEl = document.getElementById('kpiOnTimePct');
    const barOnTime = document.getElementById('barOnTime');

    const avgTransitEl = document.getElementById('kpiAvgTransit');
    const barAvgTransit = document.getElementById('barAvgTransit');

    const delayedCountEl = document.getElementById('kpiDelayedCount');
    const barDelayed = document.getElementById('barDelayed');

    const topDestinationEl = document.getElementById('kpiTopDestination');

    const completedEl = document.getElementById('kpiCompleted');
    const barCompleted = document.getElementById('barCompleted');

    const pendingEl = document.getElementById('kpiPending');
    const barPending = document.getElementById('barPending');

    function parseDate(str){
        if(!str) return null;
        const d = new Date(str);
        return isNaN(d.getTime()) ? null : d;
    }

    function daysBetween(a, b){
        const msPerDay = 24*60*60*1000;
        const utc1 = Date.UTC(a.getFullYear(), a.getMonth(), a.getDate());
        const utc2 = Date.UTC(b.getFullYear(), b.getMonth(), b.getDate());
        return Math.max(0, Math.floor((utc2 - utc1) / msPerDay));
    }

    function pct(n, d){ return d > 0 ? Math.round((n/d)*100) : 0; }

    function widthPct(el, value){ el.style.width = Math.min(100, Math.max(0, value)) + '%'; }

    async function load(){
        try{
            const res = await fetch(API_URL, { credentials: 'include' });
            const data = await res.json();
            const shipments = Array.isArray(data) ? data : [];

            const total = shipments.length;
            const delivered = shipments.filter(s => (s.status || '').toLowerCase() === 'delivered').length;
            const inTransit = shipments.filter(s => (s.status || '').toLowerCase() === 'in transit').length;
            const pending = shipments.filter(s => (s.status || '').toLowerCase() === 'pending').length;
            const open = inTransit + pending;

            // Delivery rate (proxy for on-time delivery)
            const onTime = pct(delivered, total);
            onTimePctEl.textContent = onTime + '%';
            widthPct(barOnTime, onTime);

            // Avg days since dispatch for shipments still in transit
            const today = new Date();
            const inTransitDays = shipments
                .filter(s => (s.status || '').toLowerCase() === 'in transit')
                .map(s => parseDate(s.dispatch_date))
                .filter(Boolean)
                .map(d => daysBetween(d, today));
            const avgTransit = inTransitDays.length ? (inTransitDays.reduce((a,b)=>a+b,0)/inTransitDays.length) : 0;
            avgTransitEl.textContent = (Math.round(avgTransit * 10) / 10) + ' days';
            // Map avg days to a bar percentage, target 10 days = 100%
            widthPct(barAvgTransit, Math.min(100, (avgTransit/10)*100));

            // Delayed count (in transit > threshold days)
            const delayedCount = inTransitDays.filter(d => d > thresholdDaysDelayed).length;
            delayedCountEl.textContent = String(delayedCount);
            widthPct(barDelayed, pct(delayedCount, Math.max(1, total)));

            // Top destination
            const freq = {};
            for(const s of shipments){
                const dest = (s.destination || '').trim();
                if(!dest) continue;
                freq[dest] = (freq[dest] || 0) + 1;
            }
            let topDest = 'N/A', topCount = 0;
            for(const [k,v] of Object.entries(freq)){
                if(v > topCount){ topDest = k; topCount = v; }
            }
            topDestinationEl.textContent = topDest || 'N/A';

            // Completed and Pending KPI
            completedEl.textContent = String(delivered);
            widthPct(barCompleted, pct(delivered, Math.max(1, total)));

            pendingEl.textContent = String(open);
            widthPct(barPending, pct(open, Math.max(1, total)));

        } catch(err){
            console.error(err);
            if(window.Toastify){ Toastify({ text: 'Failed to load KPIs', duration: 3000, backgroundColor: '#dc2626' }).showToast(); }
        }
    }

    refreshBtn?.addEventListener('click', load);
    load();
})();
</script>
HTML;

adminLayout($children);
?>
