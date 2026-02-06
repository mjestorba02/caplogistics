<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>iMarket - Register</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Toastify -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.js"></script>
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen px-4">

  <!-- Card -->
  <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8 border border-gray-200">
    
    <!-- Logo -->
    <div class="flex flex-col items-center mb-6">
      <img src="./images/logo.jpg" alt="iMarket Logo" class="h-14 w-auto drop-shadow-md rounded-full">
      <h1 class="text-gray-900 font-bold text-xl mt-3">Create Account</h1>
      <p class="text-gray-500 text-sm">E-Commerce Logistics Management Portal</p>
    </div>

    <!-- Register Form -->
    <form id="register-form" class="space-y-5">
      <div>
        <label for="name" class="block text-gray-700 mb-1">Full Name</label>
        <input type="text" id="name" placeholder="Enter your full name" required
          class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-300 outline-none">
      </div>

      <div>
        <label for="email" class="block text-gray-700 mb-1">Email</label>
        <input type="email" id="email" placeholder="Enter your email" required
          class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-300 outline-none">
      </div>

      <div>
        <label for="password" class="block text-gray-700 mb-1">Password</label>
        <div class="relative">
          <input type="password" id="password" placeholder="Create a password" required
            class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-300 outline-none pr-10">
          <span id="togglePassword" class="absolute top-1/2 right-3 transform -translate-y-1/2 text-gray-500 cursor-pointer">
            <i class="fa-solid fa-eye"></i>
          </span>
        </div>
      </div>

      <div>
        <label for="confirm-password" class="block text-gray-700 mb-1">Confirm Password</label>
        <div class="relative">
          <input type="password" id="confirm-password" placeholder="Confirm your password" required
            class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-300 outline-none pr-10">
          <span id="toggleConfirmPassword" class="absolute top-1/2 right-3 transform -translate-y-1/2 text-gray-500 cursor-pointer">
            <i class="fa-solid fa-eye"></i>
          </span>
        </div>
      </div>

      <button type="submit" id="registerBtn" 
        class="w-full py-3 bg-indigo-600 text-white font-bold rounded-lg shadow-md hover:bg-indigo-500 transition flex justify-center items-center">
        <span id="registerBtnText">Register</span>
        <svg id="registerSpinner" class="w-5 h-5 ml-2 text-white animate-spin hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
      </button>
    </form>

    <!-- Footer -->
    <div class="mt-6 text-center">
      <p class="text-gray-600 text-sm">
        Already have an account? 
        <a href="https://log1.imarketph.com" class="text-indigo-600 hover:underline">Login</a>
      </p>
    </div>
  </div>

  <script>
    // Toast helper
    function showToast(message, type) {
      Toastify({
        text: message,
        style: {
          background: type === 'success'
            ? "linear-gradient(to right, #00b09b, #96c93d)"
            : "linear-gradient(to right, #ff5f6d, #ffc371)"
        },
        duration: 3000,
        close: true
      }).showToast();
    }

    // Toggle password visibility
    function togglePasswordVisibility(inputId, toggleId) {
      document.getElementById(toggleId).addEventListener('click', function() {
        const input = document.getElementById(inputId);
        const icon = this.querySelector('i');
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
      });
    }

    togglePasswordVisibility('password', 'togglePassword');
    togglePasswordVisibility('confirm-password', 'toggleConfirmPassword');

    const registerBtn = document.getElementById('registerBtn');
    const registerBtnText = document.getElementById('registerBtnText');
    const registerSpinner = document.getElementById('registerSpinner');

    // Handle form submission
    document.getElementById('register-form').addEventListener('submit', async function(e) {
      e.preventDefault();

      const name = document.getElementById('name').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm-password').value;

      if (!name || !email || !password || !confirmPassword) {
        showToast("All fields are required!", "error");
        return;
      }

      if (password !== confirmPassword) {
        showToast("Passwords do not match!", "error");
        return;
      }

      // Show spinner
      registerBtn.disabled = true;
      registerBtnText.textContent = 'Registering...';
      registerSpinner.classList.remove('hidden');

      try {
        const response = await fetch('https://log1.imarketph.com/api/users.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ name, email, password })
        });

        const data = await response.json();

        if (data.message && data.message.toLowerCase().includes('success')) {
          showToast(data.message, "success");
          setTimeout(() => window.location.href = 'https://log1.imarketph.com', 2000);
        } else {
          showToast(data.message || "Registration failed", "error");
        }

      } catch (err) {
        console.error(err);
        showToast('Registration failed. Please try again.', 'error');
      } finally {
        registerBtn.disabled = false;
        registerBtnText.textContent = 'Register';
        registerSpinner.classList.add('hidden');
      }
    });
  </script>
</body>
</html>
