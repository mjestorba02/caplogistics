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
        <span>Procurement Reports</span>
    </div>

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Procurement Reports & Contracts</h1>
        <p class="text-gray-500 mt-1">Overview of active contracts, purchase orders, and supplier performance.</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-lg font-semibold text-gray-700 mb-2">Active Contracts</h2>
            <h3 class="text-2xl font-bold text-green-600">12</h3>
            <p class="text-gray-500 text-sm mt-1">Contracts currently valid and ongoing.</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-lg font-semibold text-gray-700 mb-2">Pending Approvals</h2>
            <h3 class="text-2xl font-bold text-yellow-600">5</h3>
            <p class="text-gray-500 text-sm mt-1">Purchase orders waiting for approval.</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-lg font-semibold text-gray-700 mb-2">Expired Contracts</h2>
            <h3 class="text-2xl font-bold text-red-600">3</h3>
            <p class="text-gray-500 text-sm mt-1">Contracts that have passed their end date.</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-lg font-semibold text-gray-700 mb-2">Suppliers On Time</h2>
            <h3 class="text-2xl font-bold text-blue-600">88%</h3>
            <p class="text-gray-500 text-sm mt-1">Percentage of deliveries completed on time.</p>
        </div>

    </div>

    <!-- Responsive Contracts Table -->
    <div class="flex flex-col gap-4">

        <!-- Table for large screens -->
        <div class="hidden md:block overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Contract / PO</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Supplier</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Status</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Start Date</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">End Date</th>
                        <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Total Value</th>
                        <th class="py-3 px-6 text-center text-sm font-medium text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr>
                        <td class="py-4 px-6 text-gray-800">PO-001 / Contract A</td>
                        <td class="py-4 px-6 text-gray-800">Supplier A</td>
                        <td class="py-4 px-6 text-gray-800">Active</td>
                        <td class="py-4 px-6 text-gray-800">2025-01-01</td>
                        <td class="py-4 px-6 text-gray-800">2025-12-31</td>
                        <td class="py-4 px-6 text-gray-800">$25,000</td>
                        <td class="py-4 px-6 text-center">
                            <button class="text-blue-600 hover:underline mr-2">View</button>
                            <button class="text-red-600 hover:underline">Terminate</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-4 px-6 text-gray-800">PO-002 / Contract B</td>
                        <td class="py-4 px-6 text-gray-800">Supplier B</td>
                        <td class="py-4 px-6 text-gray-800">Pending</td>
                        <td class="py-4 px-6 text-gray-800">2025-03-01</td>
                        <td class="py-4 px-6 text-gray-800">2025-09-30</td>
                        <td class="py-4 px-6 text-gray-800">$15,500</td>
                        <td class="py-4 px-6 text-center">
                            <button class="text-blue-600 hover:underline mr-2">View</button>
                            <button class="text-red-600 hover:underline">Cancel</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Card layout for small screens -->
        <div class="md:hidden flex flex-col gap-4">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex justify-between mb-2">
                    <h2 class="font-semibold">PO-001 / Contract A</h2>
                    <span class="text-green-600 font-semibold">Active</span>
                </div>
                <p class="text-gray-600"><span class="font-semibold">Supplier:</span> Supplier A</p>
                <p class="text-gray-600"><span class="font-semibold">Start:</span> 2025-01-01</p>
                <p class="text-gray-600"><span class="font-semibold">End:</span> 2025-12-31</p>
                <p class="text-gray-600"><span class="font-semibold">Total:</span> $25,000</p>
                <div class="flex justify-end gap-2 mt-2">
                    <button class="text-blue-600 hover:underline">View</button>
                    <button class="text-red-600 hover:underline">Terminate</button>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex justify-between mb-2">
                    <h2 class="font-semibold">PO-002 / Contract B</h2>
                    <span class="text-yellow-600 font-semibold">Pending</span>
                </div>
                <p class="text-gray-600"><span class="font-semibold">Supplier:</span> Supplier B</p>
                <p class="text-gray-600"><span class="font-semibold">Start:</span> 2025-03-01</p>
                <p class="text-gray-600"><span class="font-semibold">End:</span> 2025-09-30</p>
                <p class="text-gray-600"><span class="font-semibold">Total:</span> $15,500</p>
                <div class="flex justify-end gap-2 mt-2">
                    <button class="text-blue-600 hover:underline">View</button>
                    <button class="text-red-600 hover:underline">Cancel</button>
                </div>
            </div>
        </div>

    </div>

</div>
';

adminLayout($children);
?>
