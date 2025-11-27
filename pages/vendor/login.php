<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TechShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(45deg, #0d6efd, #6f42c1);
            --primary-light: #4d8eff;
            --dark-bg: #2c3e50;
        }
        
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .logo-text {
            font-weight: 700;
            font-size: 1.8rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .auth-container {
            display: flex;
            flex-grow: 1;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .auth-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .auth-header {
            background: var(--primary-gradient);
            color: white;
            padding: 25px;
            text-align: center;
        }
        
        .auth-body {
            padding: 25px;
        }
        
        .form-control {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #e1e5eb;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        
        .input-group-text {
            background: white;
            border-radius: 8px 0 0 8px;
        }
        
        .auth-btn {
            background: var(--primary-gradient);
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .auth-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        
        .auth-footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #e1e5eb;
        }
        
        .auth-switch {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }
        
        .auth-switch:hover {
            text-decoration: underline;
        }
        
        /* Toggle between login and register */
        #registerForm {
            display: none;
        }
        
        /* Password strength meter */
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 5px;
            background: #e9ecef;
        }
        
        .password-strength-bar {
            height: 100%;
            border-radius: 5px;
            width: 0%;
            transition: width 0.3s;
        }
        
        /* Notification styles */
        .alert-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
        }
    </style>
</head>
<body>
    <!-- Auth Container -->
    <div class="auth-container">
        <div class="auth-card mt-5 mb-5"> 
            <!-- Login Form -->
            <div id="loginForm">
                <div class="auth-header">
                    <h4>Welcome Back</h4>
                    <p class="mb-0">Sign in to your TechShop account</p>
                </div>
                <div class="auth-body">
                    <form id="login">
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">Email address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="loginEmail" placeholder="name@example.com" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="loginPassword" placeholder="Enter your password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleLoginPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">
                                Remember me
                            </label>
                        </div>
                        <button type="submit" class="btn auth-btn w-100 text-white mb-3">Sign In</button>
                    </form>
                </div>
                <div class="auth-footer">
                    <p class="mb-0">Don't have an account? <a class="auth-switch" data-form="register">Sign up</a></p>
                </div>
            </div>
            
            <!-- Register Form -->
            <div id="registerForm">
                <div class="auth-header">
                    <h4>Create Account</h4>
                    <p class="mb-0">Join TechShop to explore amazing products</p>
                </div>
                <div class="auth-body">
                    <form id="register">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">First Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="firstName" placeholder="John" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Last Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="lastName" placeholder="Doe" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">Email address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="registerEmail" placeholder="name@example.com" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="companyName" class="form-label">Company Name (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <input type="text" class="form-control" id="companyName" placeholder="Your company name">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="registerPassword" placeholder="Create a password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleRegisterPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength mt-2">
                                <div class="password-strength-bar" id="passwordStrengthBar"></div>
                            </div>
                            <div class="form-text" id="passwordStrengthText">Password strength: None</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm your password" required>
                            </div>
                            <div class="form-text text-danger" id="passwordMatchText" style="display: none;">Passwords do not match</div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="termsAgree" required>
                            <label class="form-check-label" for="termsAgree">
                                I agree to the Terms of Service and Privacy Policy
                            </label>
                        </div>
                        <button type="submit" class="btn auth-btn w-100 text-white mb-3">Create Account</button>
                    </form>
                </div>
                <div class="auth-footer">
                    <p class="mb-0">Already have an account? <a class="auth-switch" data-form="login">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // API endpoint
        const API_URL = 'http://localhost/caplog1/api/ven_log.php';
        
        // Toggle between login and register forms
        document.querySelectorAll('.auth-switch').forEach(button => {
            button.addEventListener('click', function() {
                const formToShow = this.getAttribute('data-form');
                
                if (formToShow === 'register') {
                    document.getElementById('loginForm').style.display = 'none';
                    document.getElementById('registerForm').style.display = 'block';
                } else {
                    document.getElementById('registerForm').style.display = 'none';
                    document.getElementById('loginForm').style.display = 'block';
                }
            });
        });

        // Toggle password visibility
        function setupPasswordToggle(buttonId, inputId) {
            const toggleButton = document.getElementById(buttonId);
            const passwordInput = document.getElementById(inputId);
            
            toggleButton.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleButton.innerHTML = '<i class="bi bi-eye-slash"></i>';
                } else {
                    passwordInput.type = 'password';
                    toggleButton.innerHTML = '<i class="bi bi-eye"></i>';
                }
            });
        }

        setupPasswordToggle('toggleLoginPassword', 'loginPassword');
        setupPasswordToggle('toggleRegisterPassword', 'registerPassword');

        // Password strength meter
        const passwordInput = document.getElementById('registerPassword');
        const strengthBar = document.getElementById('passwordStrengthBar');
        const strengthText = document.getElementById('passwordStrengthText');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Check password length
            if (password.length >= 8) strength += 25;
            
            // Check for uppercase letters
            if (/[A-Z]/.test(password)) strength += 25;
            
            // Check for numbers
            if (/[0-9]/.test(password)) strength += 25;
            
            // Check for special characters
            if (/[^A-Za-z0-9]/.test(password)) strength += 25;
            
            // Update strength bar
            strengthBar.style.width = strength + '%';
            
            // Update strength text and color
            if (strength === 0) {
                strengthText.textContent = 'Password strength: None';
                strengthBar.style.backgroundColor = '#e9ecef';
            } else if (strength <= 50) {
                strengthText.textContent = 'Password strength: Weak';
                strengthBar.style.backgroundColor = '#dc3545';
            } else if (strength <= 75) {
                strengthText.textContent = 'Password strength: Medium';
                strengthBar.style.backgroundColor = '#ffc107';
            } else {
                strengthText.textContent = 'Password strength: Strong';
                strengthBar.style.backgroundColor = '#198754';
            }
        });

        // Confirm password validation
        const confirmPassword = document.getElementById('confirmPassword');
        const passwordMatchText = document.getElementById('passwordMatchText');
        
        confirmPassword.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                passwordMatchText.style.display = 'block';
            } else {
                passwordMatchText.style.display = 'none';
            }
        });

        // Show notification function
        function showNotification(message, type = 'success') {
            // Remove any existing notifications
            const existingNotifications = document.querySelectorAll('.alert-notification');
            existingNotifications.forEach(notification => notification.remove());
            
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show alert-notification`;
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Add to page
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        // Handle form submission - Login
        document.getElementById('login').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            // Basic validation
            if (!email || !password) {
                showNotification('Please fill in all fields', 'error');
                return;
            }
            
            try {
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.textContent = 'Signing in...';
                submitBtn.disabled = true;
                
                // Make API request
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'login',
                        email: email,
                        password: password
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Login successful! Redirecting...');
                    
                    // Store user data in localStorage
                    localStorage.setItem('vendor', JSON.stringify(data.vendor));
                    
                    // Redirect to dashboard after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 2000);
                } else {
                    showNotification(data.error || 'Login failed', 'error');
                }
            } catch (error) {
                console.error('Login error:', error);
                showNotification('Network error. Please try again.', 'error');
            } finally {
                // Restore button state
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.textContent = 'Sign In';
                submitBtn.disabled = false;
            }
        });
        
        // Handle form submission - Register
        document.getElementById('register').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const confirmPass = document.getElementById('confirmPassword').value;
            const companyName = document.getElementById('companyName').value;
            const termsAgreed = document.getElementById('termsAgree').checked;
            
            // Basic validation
            if (!firstName || !lastName || !email || !password || !confirmPass) {
                showNotification('Please fill in all required fields', 'error');
                return;
            }
            
            if (password !== confirmPass) {
                showNotification('Passwords do not match', 'error');
                return;
            }
            
            if (!termsAgreed) {
                showNotification('Please agree to the terms and conditions', 'error');
                return;
            }
            
            try {
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.textContent = 'Creating account...';
                submitBtn.disabled = true;
                
                // Make API request
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'register',
                        first_name: firstName,
                        last_name: lastName,
                        email: email,
                        password: password,
                        company_name: companyName
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Registration successful! You can now login.');
                    
                    // Switch to login form after 3 seconds
                    setTimeout(() => {
                        document.getElementById('registerForm').style.display = 'none';
                        document.getElementById('loginForm').style.display = 'block';
                        document.getElementById('loginEmail').value = email;
                    }, 3000);
                } else {
                    showNotification(data.error || 'Registration failed', 'error');
                }
            } catch (error) {
                console.error('Registration error:', error);
                showNotification('Network error. Please try again.', 'error');
            } finally {
                // Restore button state
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.textContent = 'Create Account';
                submitBtn.disabled = false;
            }
        });

        // Check if user is already logged in
        function checkAuthStatus() {
            const vendor = localStorage.getItem('vendor');
            
            if (vendor) {
                // User is logged in, redirect to dashboard
                window.location.href = 'index.php';
            }
        }



    </script>
</body>
</html>