<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catnip</title>
    <link rel="icon" type="image/png" href="assets/molly.png">
    <link rel="stylesheet" href="style/styles.css"> <!-- Custom styles -->
    <link rel="stylesheet" href="style/auth.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> <!-- Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet"> <!-- Cursive font -->
</head>
<body>
    <div class="content-wrapper">
        <header class="bg-warning sticky-top">
            <div class="container d-flex justify-content-between align-items-center p-2">
                <h1 style="font-family: 'Pacifico', cursive; color: white;">CatNip</h1>
                <div class="header-buttons">
                    <button class="btn btn-light rounded-button" id="loginBtn">Login</button>
                    <button class="btn btn-light rounded-button" id="registerBtn">Register</button>
                </div>
            </div>
        </header>

        <main class="d-flex flex-column align-items-center">
            <div class="landing-content">
                <img src="assets/molly.png" alt="Cat Logo" class="cat-logo">
                <div class="text-content">
                    <h2>Purrfect Savings!</h2>
                    <p>Understand expenses better and save efficiently.</p>
                </div>
            </div>
        </main>
    </div>

    <footer class="bg-warning text-center">
        <p class="mb-0">&copy; 2025 CatNip. All rights reserved.</p>
    </footer>

    <!-- Login Modal -->
    <div id="loginModal" class="auth-modal">
        <div class="auth-modal-content">
            <div class="auth-modal-header">
                <h2 class="auth-modal-title">Login</h2>
                <button type="button" class="auth-close" onclick="closeLoginModal()">&times;</button>
            </div>
            <form id="loginForm" onsubmit="return handleLogin(event)">
                <div class="auth-form-group">
                    <label for="loginUsername">Username</label>
                    <input type="text" id="loginUsername" name="username" required>
                </div>
                <div class="auth-form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" required>
                </div>
                <div class="auth-error" id="loginError"></div>
                <button type="submit" class="auth-submit-btn">Login</button>
                <div class="auth-switch">
                    Don't have an account? <a href="#" onclick="showRegisterModal()">Register</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="auth-modal">
        <div class="auth-modal-content">
            <div class="auth-modal-header">
                <h2 class="auth-modal-title">Register</h2>
                <button type="button" class="auth-close" onclick="closeRegisterModal()">&times;</button>
            </div>
            <form id="registerForm" onsubmit="return handleRegister(event)">
                <div class="auth-form-group">
                    <label for="registerUsername">Username</label>
                    <input type="text" id="registerUsername" name="username" required>
                </div>
                <div class="auth-form-group">
                    <label for="registerEmail">Email</label>
                    <input type="email" id="registerEmail" name="email" required>
                </div>
                <div class="auth-form-group">
                    <label for="registerPassword">Password</label>
                    <input type="password" id="registerPassword" name="password" required>
                </div>
                <div class="auth-form-group">
                    <label for="registerConfirmPassword">Confirm Password</label>
                    <input type="password" id="registerConfirmPassword" name="confirm_password" required>
                </div>
                <div class="auth-form-group">
                    <label for="registerGender">Gender</label>
                    <select id="registerGender" name="gender" required>
                        <option value="">Select gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="auth-form-group">
                    <label for="profilePicture">Profile Picture (Optional)</label>
                    <input type="file" id="profilePicture" name="profile_picture" accept="image/*">
                    <small class="text-muted">Supported formats: JPG, PNG, GIF. Max size: 2MB</small>
                </div>
                <div class="auth-error" id="registerError"></div>
                <button type="submit" class="auth-submit-btn">Register</button>
                <div class="auth-switch">
                    Already have an account? <a href="#" onclick="showLoginModal()">Login</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Required JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <!-- Custom Scripts -->
    <script src="js/scripts.js?v=1"></script>

    <script>
    // Add event listeners when the document is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Login button click handler
        document.getElementById('loginBtn').addEventListener('click', showLoginModal);
        
        // Register button click handler
        document.getElementById('registerBtn').addEventListener('click', showRegisterModal);
    });

    function showLoginModal() {
        document.getElementById('loginModal').style.display = 'block';
        document.getElementById('registerModal').style.display = 'none';
    }

    function closeLoginModal() {
        document.getElementById('loginModal').style.display = 'none';
    }

    function showRegisterModal() {
        document.getElementById('registerModal').style.display = 'block';
        document.getElementById('loginModal').style.display = 'none';
    }

    function closeRegisterModal() {
        document.getElementById('registerModal').style.display = 'none';
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const loginModal = document.getElementById('loginModal');
        const registerModal = document.getElementById('registerModal');
        
        if (event.target === loginModal) {
            closeLoginModal();
        }
        if (event.target === registerModal) {
            closeRegisterModal();
        }
    }

    // Handle login form submission
    function handleLogin(event) {
        event.preventDefault();
        console.log('Login form submitted');
        
        const username = document.getElementById('loginUsername').value;
        const password = document.getElementById('loginPassword').value;
        const errorDiv = document.getElementById('loginError');

        // Clear previous errors
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';

        // Create URLSearchParams for form data
        const formData = new URLSearchParams();
        formData.append('username', username);
        formData.append('password', password);

        // Log the attempt and form data
        console.log('Login attempt:', {
            username,
            formData: formData.toString()
        });

        fetch('/catnip/auth/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData.toString()
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.text().then(text => {
                console.log('Raw response text:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed JSON response:', data);
                    return data;
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Raw text that failed to parse:', text);
                    throw new Error('Invalid JSON response from server');
                }
            });
        })
        .then(data => {
            console.log('Processing response data:', data);
            if (data.success) {
                console.log('Login successful, redirecting to:', data.redirect);
                window.location.href = data.redirect || 'dashboard.php';
            } else {
                console.log('Login failed:', data.message);
                errorDiv.textContent = data.message || 'Login failed';
                errorDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            errorDiv.textContent = error.message || 'An error occurred during login';
            errorDiv.style.display = 'block';
        });

        return false;
    }

    // Handle register form submission
    function handleRegister(event) {
        event.preventDefault();
        const username = document.getElementById('registerUsername').value;
        const email = document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;
        const confirmPassword = document.getElementById('registerConfirmPassword').value;
        const gender = document.getElementById('registerGender').value;
        const profilePicture = document.getElementById('profilePicture').files[0];
        const errorDiv = document.getElementById('registerError');

        // Clear previous errors
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';

        if (password !== confirmPassword) {
            errorDiv.textContent = 'Passwords do not match';
            errorDiv.style.display = 'block';
            return false;
        }

        // Validate file size if a file is selected
        if (profilePicture && profilePicture.size > 2 * 1024 * 1024) {
            errorDiv.textContent = 'Profile picture must be less than 2MB';
            errorDiv.style.display = 'block';
            return false;
        }

        const formData = new FormData();
        formData.append('username', username);
        formData.append('email', email);
        formData.append('password', password);
        formData.append('gender', gender);
        if (profilePicture) {
            formData.append('profile_picture', profilePicture);
        }

        fetch('/catnip/auth/register.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Server response:', text);
                    throw new Error('Invalid JSON response from server');
                }
            });
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                window.location.href = data.redirect || 'dashboard.php';
            } else {
                errorDiv.textContent = data.message || 'Registration failed';
                errorDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Registration error:', error);
            errorDiv.textContent = error.message || 'An error occurred during registration';
            errorDiv.style.display = 'block';
        });

        return false;
    }
    </script>
</body>
</html> 