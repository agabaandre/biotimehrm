<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HRM Attend - iHRIS Attendance Tracking System | Login</title>
  <meta name="description" content="HRM Attend is an iHRIS Employee Attendance Tracking System used by Ministry of Health Uganda to monitor the presence of the health work force in their duty stations and other official assignments using biometrics and location based data">
  <meta name="keywords" content="Ministry of Health, Health Attendance Uganda, Ministry of Health Uganda Attendance Tracking, Uganda Attenance, Agaba Andrew Attendance, Biometric Attendance, HRM Attend, iHRIS Attendance, Ismail Wadembere iHRIS, Agaba Andrew iHRIS, Patrick Lubwama iHRIS, iHRIS Uganda, iHRIS, IntraHealth iHRIS, Health Attendance, Attendance Tracking System Uganda">
  <meta name="author" content="Agaba Andrew +256702787688">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
  <style>
        :root {
            --info-color: #005662;
            --secondary-color: #20c198;
            --accent-color: #ff6b35;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --bg-light: #f8f9fa;
            --border-color: #e9ecef;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
      box-sizing: border-box;
    }

    body {
            font-family: 'Inter', sans-serif;
            background: url('<?php echo base_url("assets/img/bg.jpg"); ?>') no-repeat center center fixed;
      background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            position: relative;
        }

        .login-header {
            background: var(--info-color);
            color: white;
            padding: 40px 30px;
      text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .logo-container {
            margin-bottom: 20px;
        }

        .logo-container img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            padding: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .login-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .login-subtitle {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 400;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: var(--bg-light);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--info-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 86, 98, 0.1);
        }

        .form-control.error {
            border-color: #e74c3c;
            background: #fdf2f2;
        }

        .form-control.success {
            border-color: var(--secondary-color);
            background: #f0fdf4;
        }

        .input-icon {
      position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            transition: color 0.3s ease;
        }

        .form-control:focus + .input-icon {
            color: var(--info-color);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: var(--info-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
        }

        .btn-login:hover {
            background: #004a54;
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            background: var(--text-light);
            cursor: not-allowed;
            transform: none;
        }

        .btn-login .spinner {
            display: none;
            margin-right: 10px;
        }

        .btn-login.loading .spinner {
            display: inline-block;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
      cursor: pointer;
    }

        .remember-me input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            accent-color: var(--info-color);
        }

        .remember-me label {
            font-size: 14px;
            color: var(--text-dark);
            cursor: pointer;
            user-select: none;
        }

        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }

        .forgot-password a {
            color: var(--info-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #004a54;
            text-decoration: underline;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .welcome-back {
            background: var(--bg-light);
            border: 2px solid var(--secondary-color);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
            display: none;
        }

        .welcome-back.show {
            display: block;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .welcome-back .user-info {
            margin-bottom: 15px;
        }

        .welcome-back .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--secondary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 600;
            margin: 0 auto 10px;
        }

        .welcome-back .user-name {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .welcome-back .user-email {
            font-size: 14px;
            color: var(--text-light);
        }

        .welcome-back .message {
            font-size: 14px;
            color: var(--text-dark);
            margin-top: 10px;
        }

        .footer {
      text-align: center;
            padding: 20px;
            color: var(--text-light);
            font-size: 12px;
            border-top: 1px solid var(--border-color);
        }

        .footer a {
            color: var(--info-color);
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .login-header,
            .login-body {
                padding: 30px 20px;
            }
            
            .login-title {
                font-size: 20px;
            }
        }

        /* Loading Animation */
        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Form Transitions */
        .form-group {
            animation: fadeInUp 0.6s ease;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
    }
  </style>
</head>

<body>
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <div class="logo-container">
                <img src="<?php echo base_url(); ?>assets/img/MOH.png" alt="MOH Logo">
            </div>
            <h1 class="login-title">HRM iHRIS Attend</h1>
            <p class="login-subtitle">Attendance to Duty System</p>
        </div>

        <!-- Body -->
        <div class="login-body">
            <!-- Welcome Back Message (Hidden by default) -->
            <div class="welcome-back" id="welcomeBack">
                <div class="user-info">
                    <div class="user-avatar" id="userAvatar">U</div>
                    <div class="user-name" id="userName">User</div>
                    <div class="user-email" id="userEmail">user@example.com</div>
                </div>
                <div class="message">
                    <i class="fas fa-clock text-warning"></i>
                    Welcome back! You've been idle for a while. Please enter your password to continue.
                </div>
            </div>

            <!-- Alert Messages -->
            <div class="alert alert-danger" id="errorAlert"></div>
            <div class="alert alert-success" id="successAlert"></div>
            <div class="alert alert-warning" id="warningAlert"></div>

            <!-- Login Form -->
          
                <?php echo form_open('auth/login', array('class' => 'form-horizontal', 'style' => 'padding-bottom: 2em;')); ?>
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                <!-- Username/Email Field -->
                <div class="form-group" id="usernameGroup">
                    <label for="username" class="form-label">
                        <i class="fas fa-user text-info"></i> Username or Email
                    </label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           placeholder="Enter your username or email"
                           required>
                    <i class="fas fa-user input-icon"></i>
                </div>

                <!-- Password Field -->
                <div class="form-group" id="passwordGroup">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock text-info"></i> Password
          </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="Enter your password"
                           required>
                    <i class="fas fa-lock input-icon"></i>
                </div>

                <!-- Remember Me -->
                <div class="remember-me">
                    <input type="checkbox" id="rememberMe" name="rememberMe" value="true">
                    <label for="rememberMe">Remember my username/email</label>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-spinner spinner"></i>
                    <span class="btn-text">Sign In</span>
                </button>
        </form>

            <!-- Forgot Password -->
            <div class="forgot-password">
                <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                    <i class="fas fa-key"></i> Forgot your password?
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> Ministry of Health Uganda. All Rights Reserved.</p>
            <p><a href="http://health.go.ug" target="_blank">health.go.ug</a></p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">
                        <i class="fas fa-key text-info"></i> Reset Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="forgotPasswordAlert" class="alert" style="display: none;"></div>
                    
                    <form id="forgotPasswordForm">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <div class="form-group">
                            <label for="resetEmail" class="form-label">
                                <i class="fas fa-envelope text-info"></i> Email Address
                            </label>
                            <input type="email" 
                                   id="resetEmail" 
                                   name="email" 
                                   class="form-control" 
                                   placeholder="Enter your registered email address"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="resetUsername" class="form-label">
                                <i class="fas fa-user text-info"></i> Username
                            </label>
                            <input type="text" 
                                   id="resetUsername" 
                                   name="username" 
                                   class="form-control" 
                                   placeholder="Enter your username"
                                   required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-info btn-lg" id="resetPasswordBtn">
                                <span class="btn-text">Send Reset Link</span>
                                <span class="btn-loading" style="display: none;">
                                    <i class="fas fa-spinner fa-spin"></i> Sending...
                                </span>
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            We'll send a password reset link to your email address.
                        </small>
                    </div>
                </div>
      </div>
    </div>
  </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const rememberMeCheckbox = document.getElementById('rememberMe');
            const loginBtn = document.getElementById('loginBtn');
            const welcomeBack = document.getElementById('welcomeBack');
            const errorAlert = document.getElementById('errorAlert');
            const successAlert = document.getElementById('successAlert');
            const warningAlert = document.getElementById('warningAlert');

            // Check for remembered username/email
            const rememberedUsername = localStorage.getItem('rememberedUsername');
            if (rememberedUsername) {
                usernameInput.value = rememberedUsername;
                rememberMeCheckbox.checked = true;
            }

            // Check for recent login (within 2 hours)
            const lastLoginTime = localStorage.getItem('lastLoginTime');
            const lastUsername = localStorage.getItem('lastUsername');
            const lastUserEmail = localStorage.getItem('lastUserEmail');
            const lastUserName = localStorage.getItem('lastUserName');
            
            if (lastLoginTime && lastUsername) {
                const timeDiff = Date.now() - parseInt(lastLoginTime);
                const twoHours = 2 * 60 * 60 * 1000; // 2 hours in milliseconds
                
                if (timeDiff < twoHours) {
                    // Show welcome back message
                    showWelcomeBack(lastUsername, lastUserEmail, lastUserName);
                    
                    // Hide username field and show only password
                    document.getElementById('usernameGroup').style.display = 'none';
                    usernameInput.value = lastUsername;
                    
                    // Focus on password field
                    passwordInput.focus();
                }
            }

            // Handle form submission
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const username = usernameInput.value.trim();
                const password = passwordInput.value.trim();
                
                if (!username || !password) {
                    showAlert('Please fill in all fields.', 'error');
                    return;
                }

                // Show loading state
                setLoadingState(true);
                
                // Submit form via AJAX
                submitLogin(username, password);
            });

            // Handle remember me checkbox
            rememberMeCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    localStorage.setItem('rememberedUsername', usernameInput.value);
                } else {
                    localStorage.removeItem('rememberedUsername');
                }
            });

            // Update remembered username when input changes
            usernameInput.addEventListener('input', function() {
                if (rememberMeCheckbox.checked) {
                    localStorage.setItem('rememberedUsername', this.value);
                }
            });

            // Forgot Password Form Handler
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            const resetPasswordBtn = document.getElementById('resetPasswordBtn');
            const forgotPasswordAlert = document.getElementById('forgotPasswordAlert');

            forgotPasswordForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const email = document.getElementById('resetEmail').value.trim();
                const username = document.getElementById('resetUsername').value.trim();
                
                if (!email || !username) {
                    showForgotPasswordAlert('Please fill in all fields.', 'danger');
                    return;
                }

                // Show loading state
                setForgotPasswordLoadingState(true);
                
                // Submit forgot password request
                submitForgotPasswordRequest(email, username);
            });

            function showWelcomeBack(username, email, name) {
                document.getElementById('userAvatar').textContent = name ? name.charAt(0).toUpperCase() : username.charAt(0).toUpperCase();
                document.getElementById('userName').textContent = name || username;
                document.getElementById('userEmail').textContent = email || username;
                welcomeBack.classList.add('show');
            }

            function submitLogin(username, password) {
                const formData = new FormData();
                formData.append('username', username);
                formData.append('password', password);

                fetch('<?php echo base_url(); ?>auth/login', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    // Check if response contains redirect or error
                    if (data.includes('dashboard') || data.includes('redirect')) {
                        // Success - store login info
                        storeLoginInfo(username);
                        
                        // Redirect to dashboard
                        window.location.href = '<?php echo base_url(); ?>dashboard';
                    } else if (data.includes('Login Failed') || data.includes('Wrong credentials')) {
                        showAlert('Invalid username or password. Please try again.', 'error');
                        setLoadingState(false);
                    } else if (data.includes('First time access')) {
                        showAlert('First time access detected. Please contact the administrator for activation.', 'warning');
                        setLoadingState(false);
                    } else {
                        showAlert('An error occurred. Please try again.', 'error');
                        setLoadingState(false);
                    }
                })
                .catch(error => {
                    console.error('Login error:', error);
                    showAlert('Network error. Please check your connection and try again.', 'error');
                    setLoadingState(false);
                });
            }

            function storeLoginInfo(username) {
                const now = Date.now();
                localStorage.setItem('lastLoginTime', now);
                localStorage.setItem('lastUsername', username);
                
                // Store additional user info if available
                if (username.includes('@')) {
                    localStorage.setItem('lastUserEmail', username);
                }
                
                // Try to extract name from username (remove numbers, special chars)
                const name = username.replace(/[0-9!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/g, '');
                if (name.length > 2) {
                    localStorage.setItem('lastUserName', name);
                }
            }

            function setLoadingState(loading) {
                if (loading) {
                    loginBtn.disabled = true;
                    loginBtn.classList.add('loading');
                    loginBtn.querySelector('.btn-text').textContent = 'Signing In...';
                } else {
                    loginBtn.disabled = false;
                    loginBtn.classList.remove('loading');
                    loginBtn.querySelector('.btn-text').textContent = 'Sign In';
                }
            }

            function setForgotPasswordLoadingState(loading) {
                if (loading) {
                    resetPasswordBtn.disabled = true;
                    resetPasswordBtn.querySelector('.btn-text').style.display = 'none';
                    resetPasswordBtn.querySelector('.btn-loading').style.display = 'inline-block';
                } else {
                    resetPasswordBtn.disabled = false;
                    resetPasswordBtn.querySelector('.btn-text').style.display = 'inline-block';
                    resetPasswordBtn.querySelector('.btn-loading').style.display = 'none';
                }
            }

            function showForgotPasswordAlert(message, type) {
                forgotPasswordAlert.className = `alert alert-${type}`;
                forgotPasswordAlert.textContent = message;
                forgotPasswordAlert.style.display = 'block';
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    forgotPasswordAlert.style.display = 'none';
                }, 5000);
            }

            function submitForgotPasswordRequest(email, username) {
                fetch('<?php echo base_url("auth/forgotPassword"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        email: email,
                        username: username
                    })
                })
                .then(response => response.json())
                .then(data => {
                    setForgotPasswordLoadingState(false);
                    
                    if (data.status === 'success') {
                        showForgotPasswordAlert(data.message, 'success');
                        // Clear form
                        forgotPasswordForm.reset();
                        // Close modal after 2 seconds
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
                            modal.hide();
                        }, 2000);
                    } else {
                        showForgotPasswordAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    setForgotPasswordLoadingState(false);
                    showForgotPasswordAlert('An error occurred. Please try again.', 'danger');
                    console.error('Forgot password error:', error);
                });
            }

            function showAlert(message, type) {
                // Hide all alerts first
                errorAlert.style.display = 'none';
                successAlert.style.display = 'none';
                warningAlert.style.display = 'none';

                // Show appropriate alert
                const alertElement = document.getElementById(type + 'Alert');
                alertElement.textContent = message;
                alertElement.style.display = 'block';

                // Auto-hide after 5 seconds
                setTimeout(() => {
                    alertElement.style.display = 'none';
                }, 5000);
            }

            // Show flash message if exists
            <?php if ($this->session->flashdata('msg')): ?>
            showAlert('<?php echo addslashes($this->session->flashdata('msg')); ?>', 'error');
            <?php endif; ?>

            // Add input validation and styling
            [usernameInput, passwordInput].forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value.trim()) {
                        this.classList.add('success');
                        this.classList.remove('error');
                    } else {
                        this.classList.remove('success');
                        this.classList.add('error');
                    }
                });

                input.addEventListener('input', function() {
                    this.classList.remove('error', 'success');
                });
            });
        });
    </script>
</body>
</html>