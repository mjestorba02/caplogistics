<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>iMarket - Login</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen px-4">

  <!-- Card -->
  <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8 border border-gray-200">
    
    <!-- Logo -->
    <div class="flex flex-col items-center mb-6">
      <img src="./images/logo.jpg" alt="iMarket Logo" class="h-14 w-auto drop-shadow-md rounded-full">
      <h1 class="text-gray-900 font-bold text-xl mt-3">Welcome Back</h1>
      <p class="text-gray-500 text-sm">E-Commerce Logistics Management Portal</p>
    </div>

    <!-- Success Alert -->
    <div class="hidden text-center mb-4 text-green-600 font-medium" id="signup-success-alert">
      Your account has been created successfully. Please log in.
    </div>

    <!-- Login Form -->
    <form id="login-form" class="space-y-5">
      <div>
        <label for="email" class="block text-gray-700 mb-1">Email</label>
        <input type="email" id="email" class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-300 outline-none" placeholder="Enter your email" required>
      </div>

      <div>
        <label for="password" class="block text-gray-700 mb-1">Password</label>
        <div class="relative">
          <input type="password" id="password" class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-300 outline-none pr-10" placeholder="Enter your password" required>
          <span id="togglePassword" class="absolute top-1/2 right-3 transform -translate-y-1/2 text-gray-500 cursor-pointer">
            <i class="fa-solid fa-eye"></i>
          </span>
        </div>
      </div>

      <button type="submit" id="loginBtn" class="w-full py-3 bg-indigo-600 text-white font-bold rounded-lg shadow-md hover:bg-indigo-500 transition flex justify-center items-center">
        <span id="loginBtnText">Login</span>
        <svg id="loginSpinner" class="w-5 h-5 ml-2 text-white animate-spin hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
      </button>
    </form>

    <!-- Extra Links -->
    <div class="mt-6 text-center">
      <a href="forgot_password" class="text-indigo-600 hover:underline">Forgot Password?</a>
      <p class="mt-2 text-gray-600 text-sm">
        Don’t have an account?
        <a href="register.php" class="text-indigo-600 hover:underline">Register</a>
      </p>
    </div>
  </div>

  <!-- OTP Modal -->
  <div class="fixed inset-0 bg-gray-900/70 flex justify-center items-center hidden" id="otpModal">
    <div class="bg-white rounded-xl shadow-lg w-[350px] p-6 relative">
      <div class="mb-4">
        <h5 class="text-xl font-semibold text-gray-900">Enter OTP</h5>
        <button type="button" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800" id="closeOtpModal">
          <i class="fa-solid fa-xmark fa-lg"></i>
        </button>
      </div>
      <div class="mb-3">
        <label for="otp" class="block mb-2 text-sm font-medium text-gray-700">OTP Code</label>
        <input type="text" id="otp" class="w-full p-3 border border-gray-300 rounded-md focus:ring focus:ring-indigo-300" placeholder="Enter OTP sent to your email">
      </div>
      <div class="flex justify-end gap-3 mt-4">
        <button type="button" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300" id="closeOtpModalBtn">Close</button>
        <button type="button" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700" id="verifyOtpBtn">Verify OTP</button>
      </div>
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

  const loginBtn = document.getElementById('loginBtn');
  const loginBtnText = document.getElementById('loginBtnText');
  const loginSpinner = document.getElementById('loginSpinner');

  // Toggle password visibility
  document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    icon.classList.toggle('fa-eye', !isPassword);
    icon.classList.toggle('fa-eye-slash', isPassword);
  });

  // Login form submission
  document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    if (!email || !password) {
      showToast('Please enter email and password.', 'error');
      return;
    }

    loginBtn.disabled = true;
    loginBtnText.textContent = 'Logging in...';
    loginSpinner.classList.remove('hidden');

    try {
      const response = await fetch('https://log1.imarketph.com/api/auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });

      const data = await response.json();

      if (data.status === 'success') {
        showToast(data.message, 'success');
        setTimeout(() => {
          window.location.href = 'pages/warehouse_analytics.php';
        }, 1500);
      } else {
        showToast(data.message, 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('Login failed. Please try again.', 'error');
    } finally {
      loginBtn.disabled = false;
      loginBtnText.textContent = 'Login';
      loginSpinner.classList.add('hidden');
    }
  });
</script>

</body>
</html>
