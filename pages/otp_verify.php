<?php
session_start();

// Check if user is in OTP verification stage
if (!isset($_SESSION['temp_user_id'])) {
    header('Location: ../index.php');
    exit;
}

$email = $_SESSION['temp_user_email'] ?? 'your email';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify OTP - iMarket</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen px-4">

  <!-- Verification Card -->
  <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8 border border-gray-200">
    
    <!-- Shield Icon -->
    <div class="text-center mb-6">
      <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fa-solid fa-shield-halved text-indigo-600 text-2xl"></i>
      </div>
      <h1 class="text-2xl font-bold text-gray-900">Verify Your Identity</h1>
      <p class="text-gray-600 text-sm mt-2">An OTP has been sent to <strong><?php echo htmlspecialchars($email); ?></strong></p>
    </div>

    <!-- OTP Input Form -->
    <form id="otp-form" class="space-y-5">
      <div>
        <label for="otp" class="block mb-3 text-sm font-medium text-gray-700">Enter OTP Code</label>
        <input 
          type="text" 
          id="otp" 
          maxlength="6" 
          class="w-full p-4 text-center text-3xl border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-2 focus:ring-indigo-300 outline-none tracking-widest font-mono" 
          placeholder="000000" 
          required 
          autofocus
        >
        <p class="text-gray-500 text-xs mt-2">Check your email inbox for the 6-digit code</p>
      </div>

      <!-- Info Box -->
      <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
        <p class="text-xs text-blue-800"><strong>Note:</strong> OTP expires in 10 minutes</p>
      </div>

      <!-- Buttons -->
      <div class="flex gap-3">
        <button type="button" class="flex-1 bg-gray-200 text-gray-800 px-4 py-3 rounded-lg hover:bg-gray-300 font-medium transition" onclick="goBack()">Back to Login</button>
        <button type="submit" id="verifyBtn" class="flex-1 bg-indigo-600 text-white px-4 py-3 rounded-lg hover:bg-indigo-700 font-medium transition flex justify-center items-center">
          <span id="verifyBtnText">Verify</span>
          <svg id="verifySpinner" class="w-4 h-4 ml-2 text-white animate-spin hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
        </button>
      </div>
    </form>

    <!-- Resend OTP Link -->
    <div class="mt-4 text-center">
      <button type="button" class="text-indigo-600 hover:underline text-sm" onclick="resendOtp()">Didn't receive the code? Resend</button>
    </div>
  </div>

<script>
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

  const otpInput = document.getElementById('otp');
  const verifyBtn = document.getElementById('verifyBtn');
  const verifyBtnText = document.getElementById('verifyBtnText');
  const verifySpinner = document.getElementById('verifySpinner');
  const otpForm = document.getElementById('otp-form');

  // Allow only numbers in OTP input
  otpInput.addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
  });

  // Submit on Enter key
  otpInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      otpForm.dispatchEvent(new Event('submit'));
    }
  });

  // Form submission
  otpForm.addEventListener('submit', async function(e) {
    e.preventDefault();

    const otp = otpInput.value.trim();

    if (!otp || otp.length !== 6) {
      showToast('Please enter a valid 6-digit OTP', 'error');
      return;
    }

    verifyBtn.disabled = true;
    verifyBtnText.textContent = 'Verifying...';
    verifySpinner.classList.remove('hidden');

    try {
      const response = await fetch('../api/auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'verify_otp', otp_code: otp })
      });

      const data = await response.json();
      console.log('OTP verification response:', data);

      if (data.status === 'success') {
        showToast('OTP verified! Redirecting...', 'success');
        setTimeout(() => {
          window.location.href = 'warehouse_analytics.php';
        }, 1500);
      } else {
        showToast(data.message || 'OTP verification failed', 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('OTP verification failed. Please try again.', 'error');
    } finally {
      verifyBtn.disabled = false;
      verifyBtnText.textContent = 'Verify';
      verifySpinner.classList.add('hidden');
    }
  });

  function goBack() {
    if (confirm('Are you sure? You will need to log in again.')) {
      window.location.href = '../index.php';
    }
  }

  function resendOtp() {
    showToast('OTP will be resent to your email shortly.', 'info');
    // In future, implement actual OTP resend via API
  }
</script>

</body>
</html>
