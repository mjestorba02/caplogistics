<?php
session_start(); 

if (!isset($_SESSION['id'])) {
    header('Location:http://localhost/hr1-ecommerce');
    exit();
}
include '../layout/adminLayout.php';

$children = '
<div class="p-6">

    <!-- Breadcrumb -->
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Logistics Dashboard</a> &gt;
        <span>Logistics Performance</span>
    </div>

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Logistics Performance</h1>
        <p class="text-gray-500 mt-1">Detailed overview of shipments, carrier efficiency, and delivery KPIs.</p>
    </div>

    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- On-Time Delivery Rate -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">On-Time Delivery</h2>
                <p class="text-gray-500 text-sm">Percentage of shipments delivered on schedule</p>
                <div class="w-full bg-gray-200 h-2 rounded mt-2">
                    <div class="bg-green-600 h-2 rounded" style="width: 94%"></div>
                </div>
            </div>
            <span class="text-2xl font-bold text-green-600">94%</span>
        </div>

        <!-- Average Transit Time -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Avg Transit Time</h2>
                <p class="text-gray-500 text-sm">Average days shipments take to arrive</p>
                <div class="w-full bg-gray-200 h-2 rounded mt-2">
                    <div class="bg-blue-600 h-2 rounded" style="width: 37%"></div>
                </div>
            </div>
            <span class="text-2xl font-bold text-blue-600">3.7 days</span>
        </div>

        <!-- Delayed Shipments -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Delayed Shipments</h2>
                <p class="text-gray-500 text-sm">Shipments delayed beyond expected delivery date</p>
                <div class="w-full bg-gray-200 h-2 rounded mt-2">
                    <div class="bg-red-600 h-2 rounded" style="width: 7%"></div>
                </div>
            </div>
            <span class="text-2xl font-bold text-red-600">7</span>
        </div>

        <!-- Top Carrier -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Top Carrier</h2>
                <p class="text-gray-500 text-sm">Carrier with best on-time performance</p>
            </div>
            <span class="text-2xl font-bold text-yellow-600">FastShip Co.</span>
        </div>

        <!-- Shipments Completed -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Shipments Completed</h2>
                <p class="text-gray-500 text-sm">Total successfully delivered shipments</p>
                <div class="w-full bg-gray-200 h-2 rounded mt-2">
                    <div class="bg-purple-600 h-2 rounded" style="width: 152px"></div>
                </div>
            </div>
            <span class="text-2xl font-bold text-purple-600">152</span>
        </div>

        <!-- Pending Deliveries -->
        <div class="bg-white p-6 rounded-lg shadow flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Pending Deliveries</h2>
                <p class="text-gray-500 text-sm">Shipments still in transit or awaiting dispatch</p>
                <div class="w-full bg-gray-200 h-2 rounded mt-2">
                    <div class="bg-orange-600 h-2 rounded" style="width: 14%"></div>
                </div>
            </div>
            <span class="text-2xl font-bold text-orange-600">14</span>
        </div>

    </div>

</div>
';

adminLayout($children);
?>
