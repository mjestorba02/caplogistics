<?php

session_start(); 

if (!isset($_SESSION['id'])) {
    header('Location:http://localhost/caplog1');
    exit();
}
include '../layout/adminLayout.php';

$children = '
<div class="p-6">

    <!-- Breadcrumb -->
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt; <span>Logistics Dashboard</span>
    </div>

    <!-- Dashboard Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Logistics Dashboard</h1>
    </div>

    <!-- Modules Overview Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Warehousing -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <i class="bx bx-buildings text-4xl text-indigo-600"></i>
                <span class="text-gray-500 text-sm">Inventory & Shipments</span>
            </div>
            <h2 class="text-xl font-semibold mb-2">Warehousing</h2>
            <p class="text-gray-600 text-sm">Monitor stock levels, manage incoming and outgoing shipments, and track warehouse performance.</p>
        </div>

        <!-- Procurement -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <i class="bx bx-shopping-bag text-4xl text-green-600"></i>
                <span class="text-gray-500 text-sm">Suppliers & Orders</span>
            </div>
            <h2 class="text-xl font-semibold mb-2">Procurement</h2>
            <p class="text-gray-600 text-sm">Handle purchase requests, supplier bidding, purchase orders, and contract management.</p>
        </div>

        <!-- Logistics Tracking -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <i class="bx bx-location-plus text-4xl text-yellow-500"></i>
                <span class="text-gray-500 text-sm">Shipments & Status</span>
            </div>
            <h2 class="text-xl font-semibold mb-2">Logistics Tracking</h2>
            <p class="text-gray-600 text-sm">Track deliveries, monitor milestones, and view logistics performance metrics.</p>
        </div>

        <!-- Asset Management -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <i class="bx bx-cube text-4xl text-red-600"></i>
                <span class="text-gray-500 text-sm">Assets & Maintenance</span>
            </div>
            <h2 class="text-xl font-semibold mb-2">Asset Management</h2>
            <p class="text-gray-600 text-sm">Register assets, schedule maintenance, track usage, and plan lifecycle replacements.</p>
        </div>

        <!-- Records & Compliance -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <i class="bx bx-folder-open text-4xl text-pink-500"></i>
                <span class="text-gray-500 text-sm">Documents & Audits</span>
            </div>
            <h2 class="text-xl font-semibold mb-2">Records & Compliance</h2>
            <p class="text-gray-600 text-sm">Manage documents, audits, compliance reports, and archives efficiently.</p>
        </div>

        <!-- Admin Settings -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <i class="bx bx-cog text-4xl text-gray-700"></i>
                <span class="text-gray-500 text-sm">System Settings</span>
            </div>
            <h2 class="text-xl font-semibold mb-2">Admin Settings</h2>
            <p class="text-gray-600 text-sm">Configure system settings, manage users, roles, and permissions.</p>
        </div>

    </div>

</div>
';

adminLayout($children);
?>
