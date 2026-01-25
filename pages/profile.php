<?php
session_start(); 

if (!isset($_SESSION['id'])) {
    header('Location:http://localhost/caplog1');
    exit();
}

include '../layout/adminLayout.php';

$userId = $_SESSION['id'];
$userName = $_SESSION['name'];
$userEmail = $_SESSION['email'];

$children = '
<div class="p-6">

    <div class="text-sm text-gray-600 mb-6">
        <a href="dashboard.php" class="text-indigo-600 hover:underline">Home</a> &gt;
        <span>Profile</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">My Profile</h1>
        <button id="editProfileBtn" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
            Edit Profile
        </button>
    </div>

    <div class="bg-white p-6 rounded-lg shadow max-w-lg mx-auto">
        <div class="flex items-center gap-4 mb-4">
            <div class="h-16 w-16 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-2xl" id="profileInitials">
                '.strtoupper(substr(preg_replace("/\s+/", "", $userName), 0, 2)).'
            </div>
            <div>
                <h2 class="text-xl font-semibold" id="profileName">'.$userName.'</h2>
                <p class="text-gray-500" id="profileEmail">'.$userEmail.'</p>
            </div>
        </div>
        <div class="mt-4 text-gray-700">
            <p><strong>Name:</strong> <span id="profileNameText">'.$userName.'</span></p>
            <p><strong>Email:</strong> <span id="profileEmailText">'.$userEmail.'</span></p>
        </div>
    </div>
</div>

<div id="profileModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>

    <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative z-10">
        <h2 class="text-2xl font-bold mb-4">Edit Profile</h2>
        <form id="editProfileForm" class="space-y-4">
            <div>
                <label class="block text-gray-700">Name</label>
                <input type="text" name="name" value="'.$userName.'" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Email</label>
                <input type="email" name="email" value="'.$userEmail.'" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700">Password <small class="text-gray-400">(leave blank to keep current)</small></label>
                <div class="relative">
                    <input type="password" name="password" id="passwordInput" placeholder="Enter new password" class="w-full border rounded px-3 py-2 pr-10">
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500">
                        <i class="bx bx-show"></i>
                    </button>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="closeProfileModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>


';

adminLayout($children);
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const profileModal = document.getElementById("profileModal");
    const editProfileBtn = document.getElementById("editProfileBtn");
    const closeProfileModal = document.getElementById("closeProfileModal");
    const editProfileForm = document.getElementById("editProfileForm");

    // Open modal
    editProfileBtn.addEventListener("click", () => {
        profileModal.classList.remove("hidden");
    });

    // Close modal
    closeProfileModal.addEventListener("click", () => {
        profileModal.classList.add("hidden");
    });

    // Close modal when clicking outside content
    profileModal.addEventListener("click", (e) => {
        if (e.target === profileModal) {
            profileModal.classList.add("hidden");
        }
    });

    // Toast helper
    function showToast(message, type) {
        Toastify({
            text: message,
            style: {
                background: type === "success" ? "linear-gradient(to right, #00b09b, #96c93d)" : "linear-gradient(to right, #ff5f6d, #ffc371)"
            },
            duration: 3000,
            close: true
        }).showToast();
    }

    // Handle profile form submission
    editProfileForm.addEventListener("submit", async function(e) {
        e.preventDefault();
        const formData = new FormData(editProfileForm);
        const name = formData.get("name").trim();
        const email = formData.get("email").trim();
        const password = formData.get("password").trim();

        if (!name || !email) {
            showToast("Name and email are required!", "error");
            return;
        }

        const userId = <?php echo $userId; ?>;
        const apiUrl = `http://localhost/caplog1/api/users.php?id=${userId}`;

        try {
            const response = await fetch(apiUrl, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    name,
                    email,
                    ...(password ? { password } : {})
                })
            });
            const data = await response.json();
            if (data.message && data.message.includes("successfully")) {
                showToast("Profile updated successfully!", "success");

                document.getElementById("profileName").textContent = name;
                document.getElementById("profileEmail").textContent = email;
                document.getElementById("profileNameText").textContent = name;
                document.getElementById("profileEmailText").textContent = email;
                document.getElementById("profileInitials").textContent = name.split(" ").map(n=>n[0]).join("").substring(0,2).toUpperCase();

                profileModal.classList.add("hidden");
            } else {
                showToast(data.message || "Failed to update profile", "error");
            }
        } catch (err) {
            console.error(err);
            showToast("Error updating profile. Try again.", "error");
        }
    });
});

document.addEventListener("DOMContentLoaded", function() {
    const passwordInput = document.getElementById("passwordInput");
    const togglePassword = document.getElementById("togglePassword");
    const icon = togglePassword.querySelector("i");

    togglePassword.addEventListener("click", () => {
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            icon.classList.remove("bx-show");
            icon.classList.add("bx-hide");
        } else {
            passwordInput.type = "password";
            icon.classList.remove("bx-hide");
            icon.classList.add("bx-show");
        }
    });
});
</script>