<?php
session_start(); 

if (!isset($_SESSION['id'])) {
    header('Location:https://log1.imarketph.com/');
    exit();
}
include '../layout/adminLayout.php';

$children = '
<div class="p-6">

    <!-- Breadcrumb -->
    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Records & Compliance</a> &gt;
        <span>Project Archives</span>
    </div>

    <!-- Page Header & Search -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Project Archives & Search</h1>
        <input type="text" id="searchInput" placeholder="Search archives..." 
            class="border rounded px-3 py-2 w-full md:w-64">
    </div>

    <!-- Archives Grid -->
    <div id="archivesGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Archive Card Example -->
        <div class="archive-card bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <h2 class="text-xl font-semibold mb-2">Warehouse Optimization Project</h2>
            <p class="text-gray-600 mb-1">Category: Logistics</p>
            <p class="text-gray-600 mb-1">Completed By: Team A</p>
            <p class="text-gray-600 mb-2">Completed On: 2025-06-15</p>
            <div class="flex justify-end gap-2">
                <button class="text-blue-600 hover:underline">View</button>
                <button class="text-red-600 hover:underline">Download</button>
            </div>
        </div>

        <div class="archive-card bg-white p-6 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
            <h2 class="text-xl font-semibold mb-2">Supplier Compliance Review</h2>
            <p class="text-gray-600 mb-1">Category: Procurement</p>
            <p class="text-gray-600 mb-1">Completed By: Team B</p>
            <p class="text-gray-600 mb-2">Completed On: 2025-05-30</p>
            <div class="flex justify-end gap-2">
                <button class="text-blue-600 hover:underline">View</button>
                <button class="text-red-600 hover:underline">Download</button>
            </div>
        </div>

        <!-- More archives dynamically from DB -->

    </div>

</div>

<script>
// Search/filter archives
const searchInput = document.getElementById("searchInput");
const archivesGrid = document.getElementById("archivesGrid");
const archiveCards = archivesGrid.getElementsByClassName("archive-card");

searchInput.addEventListener("input", function() {
    const filter = searchInput.value.toLowerCase();
    Array.from(archiveCards).forEach(card => {
        const title = card.querySelector("h2").innerText.toLowerCase();
        const category = card.querySelector("p").innerText.toLowerCase();
        if (title.includes(filter) || category.includes(filter)) {
            card.style.display = "";
        } else {
            card.style.display = "none";
        }
    });
});
</script>
';

adminLayout($children);
?>
