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
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Admin Settings</a> &gt;
        <span>System Configuration</span>
    </div>

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">System Configuration</h1>
        <p class="text-gray-500 mt-1">Manage core system settings and preferences.</p>
    </div>

    <!-- Settings Cards/Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- General Settings -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <h2 class="text-xl font-semibold mb-4">General Settings</h2>
            <p class="text-gray-600 mb-4">Update system name, timezone, and language preferences.</p>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Edit</button>
        </div>

        <!-- Email & Notifications -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <h2 class="text-xl font-semibold mb-4">Email & Notifications</h2>
            <p class="text-gray-600 mb-4">Configure SMTP, email templates, and notification preferences.</p>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Edit</button>
        </div>

        <!-- Security Settings -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <h2 class="text-xl font-semibold mb-4">Security Settings</h2>
            <p class="text-gray-600 mb-4">Manage password policies, 2FA, and session timeouts.</p>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Edit</button>
        </div>

        <!-- Backup & Restore -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <h2 class="text-xl font-semibold mb-4">Backup & Restore</h2>
            <p class="text-gray-600 mb-4">Create system backups and restore previous configurations.</p>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Manage</button>
        </div>

        <!-- Integration Settings -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <h2 class="text-xl font-semibold mb-4">Integrations</h2>
            <p class="text-gray-600 mb-4">Configure third-party integrations and API connections.</p>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Configure</button>
        </div>

        <!-- System Logs -->
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <h2 class="text-xl font-semibold mb-4">System Logs</h2>
            <p class="text-gray-600 mb-4">View system activity logs, errors, and audit trails.</p>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">View Logs</button>
        </div>

    </div>

</div>
';

adminLayout($children);
?>
