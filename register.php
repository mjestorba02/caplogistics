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

  <style>
    body {
      background-image: url('https://media.giphy.com/media/3oFzmkkwfOGlzZ0gog/giphy.gif');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
    }
  </style>
</head>
<body class="bg-cover bg-center bg-fixed">

  <div class="min-h-screen flex items-center justify-center p-4 bg-gray-900 bg-opacity-60">
    <div class="bg-indigo-950/80 backdrop-blur-sm rounded-2xl shadow-2xl w-full max-w-md p-8 border border-indigo-800/50">
      
      <div class="flex flex-col items-center mb-6">
        <img src="./images/logo.jpg" alt="iMarket Logo" class="h-12 w-auto drop-shadow-lg">
        <p class="text-gray-400 text-sm mt-1">E-Commerce HR Recruitment Portal</p>
      </div>

      <form id="register-form" class="space-y-5">
        <div>
          <label for="name" class="block text-gray-300 mb-1">Full Name</label>
          <input type="text" id="name" placeholder="Enter your full name" required
                 class="w-full px-4 py-2 rounded-lg bg-gray-900/70 text-white border border-gray-700 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400 outline-none">
        </div>

        <div>
          <label for="email" class="block text-gray-300 mb-1">Email</label>
          <input type="email" id="email" placeholder="Enter your email" required
                 class="w-full px-4 py-2 rounded-lg bg-gray-900/70 text-white border border-gray-700 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400 outline-none">
        </div>

        <div>
          <label for="password" class="block text-gray-300 mb-1">Password</label>
          <div class="relative">
            <input type="password" id="password" placeholder="Create a password" required
                   class="w-full px-4 py-2 rounded-lg bg-gray-900/70 text-white border border-gray-700 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400 outline-none pr-10">
            <span id="togglePassword" class="absolute top-1/2 right-3 transform -translate-y-1/2 text-gray-400 cursor-pointer">
              <i class="fa-solid fa-eye"></i>
            </span>
          </div>
        </div>

        <div>
          <label for="confirm-password" class="block text-gray-300 mb-1">Confirm Password</label>
          <div class="relative">
            <input type="password" id="confirm-password" placeholder="Confirm your password" required
                   class="w-full px-4 py-2 rounded-lg bg-gray-900/70 text-white border border-gray-700 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400 outline-none pr-10">
            <span id="toggleConfirmPassword" class="absolute top-1/2 right-3 transform -translate-y-1/2 text-gray-400 cursor-pointer">
              <i class="fa-solid fa-eye"></i>
            </span>
          </div>
        </div>

        <button type="submit" id="registerBtn" class="w-full py-2 bg-yellow-400 text-gray-900 font-bold rounded-lg hover:bg-yellow-300 transition flex justify-center items-center">
          <span id="registerBtnText">Register</span>
          <svg id="registerSpinner" class="w-5 h-5 ml-2 text-gray-900 animate-spin hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
        </button>
      </form>

      <div class="mt-5 text-center">
        <p class="mt-2 text-gray-400 text-sm">
          Already have an account?
          <a href="" class="text-yellow-400 hover:underline">Login</a>
        </p>
      </div>

    </div>
  </div>

  <script>
    // Toast helper
    function showToast(message, type) {
      Toastify({
        text: message,
        style: {
          background: type === 'success' ?
            "linear-gradient(to right, #00b09b, #96c93d)" :
            "linear-gradient(to right, #ff5f6d, #ffc371)"
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
        const response = await fetch('http://localhost/logistics1-ecommerce/api/users.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ name, email, password })
        });

        const data = await response.json();

        if (data.message && data.message.toLowerCase().includes('success')) {
          showToast(data.message, "success");
          setTimeout(() => window.location.href = 'http://localhost/logistics1-ecommerce', 2000);
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
