<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location:http://localhost/caplog1');
    exit();
}
include '../layout/adminLayout.php';

$children = <<<'HTML'
<div class="p-6 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Execution & Real-Time Tracking</h1>
        <button id="addTrackingBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">+ Add Tracking</button>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <input type="text" id="searchInput" placeholder="Search tracking records..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left">Tracking ID</th>
                    <th class="px-6 py-3 text-left">Shipment ID</th>
                    <th class="px-6 py-3 text-left">Current Location</th>
                    <th class="px-6 py-3 text-left">GPS Coords</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody id="trackingTable">
                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div id="trackingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6">
        <h2 class="text-2xl font-bold mb-4">Add Tracking Record</h2>
        <form id="trackingForm" class="space-y-4">
            <input type="hidden" id="trackingId">
            <input type="text" id="shipmentId" placeholder="Shipment ID" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
            <input type="text" id="currentLocation" placeholder="Current Location" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
            <input type="text" id="gpsCoordinates" placeholder="GPS Coordinates (Lat,Long)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
            <input type="number" id="speedKmh" placeholder="Speed (km/h)" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
            <input type="number" id="temperatureReading" placeholder="Temperature Reading (°C)" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
            <select id="vehicleCondition" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="Good">Good</option>
                <option value="Satisfactory">Satisfactory</option>
                <option value="Needs Attention">Needs Attention</option>
                <option value="Critical">Critical</option>
            </select>
            <select id="trackingStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="In Transit">In Transit</option>
                <option value="On Schedule">On Schedule</option>
                <option value="Delayed">Delayed</option>
                <option value="Completed">Completed</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold">Save</button>
                <button type="button" onclick="closeTrackingModal()" class="flex-1 bg-gray-400 hover:bg-gray-500 text-white py-2 rounded-lg font-semibold">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="../scripts/execution_realtime_tracking.js"></script>
HTML;

adminLayout($children);
