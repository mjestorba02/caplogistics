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
        <span>Users Management</span>
    </div>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Users Management</h1>
        <button id="openModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
            <i class="bx bx-plus mr-1"></i> Add New User
        </button>
    </div>

    <!-- Users Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Name</th>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-700">Email</th>
                    <th class="py-3 px-6 text-center text-sm font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody id="usersTableBody" class="divide-y divide-gray-200">
                <!-- Users will be dynamically inserted here -->
            </tbody>
        </table>
    </div>

</div>

<!-- Modal for Adding/Editing User -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
        <h2 class="text-2xl font-bold mb-4">Add / Edit User</h2>
        <form id="userForm" class="space-y-4">
            <input type="hidden" name="id" value="">
            <div>
                <label class="block text-gray-700">Full Name</label>
                <input type="text" name="name" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Email</label>
                <input type="email" name="email" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="relative">
                <label class="block text-gray-700">Password (leave empty to keep current)</label>
                <input type="password" id="passwordInput" name="password" class="w-full border rounded px-3 py-2 pr-10">
              <i id="togglePassword" class="bx bx-show absolute right-3 top-1/2 translate-y-1 cursor-pointer text-gray-500"></i>

            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Boxicons CSS -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<!-- Toastify CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
// API URL
const apiUrl = "http://localhost/caplog1/api/users.php";
const usersTableBody = document.getElementById("usersTableBody");
const modal = document.getElementById("modal");
const openModalBtn = document.getElementById("openModal");
const closeModalBtn = document.getElementById("closeModal");
const userForm = document.getElementById("userForm");
const passwordInput = document.getElementById("passwordInput");
const togglePassword = document.getElementById("togglePassword");

// Toast helper
function showToast(message, type) {
    Toastify({
        text: message,
        style: {
            background: type === "success" ?
                "linear-gradient(to right, #00b09b, #96c93d)" :
                "linear-gradient(to right, #ff5f6d, #ffc371)"
        },
        duration: 3000,
        close: true
    }).showToast();
}

// Toggle password visibility
togglePassword.addEventListener("click", () => {
    if(passwordInput.type === "password") {
        passwordInput.type = "text";
        togglePassword.classList.replace("bx-show", "bx-hide");
    } else {
        passwordInput.type = "password";
        togglePassword.classList.replace("bx-hide", "bx-show");
    }
});

// Open modal
openModalBtn.addEventListener("click", () => {
    userForm.reset();
    userForm.id.value = "";
    modal.classList.remove("hidden");
    modal.classList.add("flex");
});

// Close modal
closeModalBtn.addEventListener("click", () => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
});

// Fetch users from API
async function fetchUsers() {
    try {
        const res = await fetch(apiUrl);
        const users = await res.json();
        usersTableBody.innerHTML = "";
        users.forEach(user => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td class="py-4 px-6 text-gray-800">${user.name}</td>
                <td class="py-4 px-6 text-gray-800">${user.email}</td>
                <td class="py-4 px-6 text-center">
                    <button class="text-blue-600 hover:text-blue-800 mr-2" title="Edit" onclick="editUser(${user.id})">
                        <i class="bx bx-edit-alt text-lg"></i>
                    </button>
                    <button class="text-red-600 hover:text-red-800" title="Delete" onclick="deleteUser(${user.id})">
                        <i class="bx bx-trash text-lg"></i>
                    </button>
                </td>
            `;
            usersTableBody.appendChild(tr);
        });
    } catch (err) {
        console.error("Error fetching users:", err);
        showToast("Failed to fetch users", "error");
    }
}

// Edit user
function editUser(id) {
    fetch(`${apiUrl}?id=${id}`)
        .then(res => res.json())
        .then(user => {
            userForm.id.value = user.id;
            userForm.name.value = user.name;
            userForm.email.value = user.email;
            passwordInput.value = ""; // Leave blank to keep current password
            modal.classList.remove("hidden");
            modal.classList.add("flex");
        });
}

// Delete user
async function deleteUser(id) {
    if(!confirm("Are you sure you want to delete this user?")) return;
    try {
        const res = await fetch(`${apiUrl}?id=${id}`, { method: "DELETE" });
        const result = await res.json();
        showToast(result.message, "success");
        fetchUsers();
    } catch(err) {
        console.error(err);
        showToast("Failed to delete user", "error");
    }
}

// Add / Update user
userForm.addEventListener("submit", async function(e){
    e.preventDefault();
    const id = this.id.value;
    const data = Object.fromEntries(new FormData(this).entries());
    let method = id ? "PUT" : "POST";
    let url = id ? `${apiUrl}?id=${id}` : apiUrl;

    // Keep current password if password field is empty
    if(id && !data.password) delete data.password;

    try {
        const res = await fetch(url, {
            method: method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        showToast(result.message, "success");
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        fetchUsers();
        this.reset();
    } catch(err) {
        console.error(err);
        showToast("Failed to save user", "error");
    }
});

// Initial fetch
fetchUsers();
</script>
';

adminLayout($children);
?>
