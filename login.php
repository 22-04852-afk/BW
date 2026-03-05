<?php
session_start();
// If already authenticated server-side, go to profile
if (!empty($_SESSION['user_id'])) {
    header('Location: profile.php', true, 302);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BW Gas Detector Sales Dashboard</title>
    <meta name="description" content="Sign in to the BW Gas Detector Sales Dashboard">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <!-- Gradient Background -->
    <div class="bg-gradient" aria-hidden="true"></div>

    <!-- Main Container -->
    <div class="login-container">
        <!-- Login Card -->
        <div class="login-card" role="main">
            <!-- Gradient Top Border -->
            <div class="card-border" aria-hidden="true"></div>

            <!-- Login Form Content -->
            <div class="login-content">

                <!-- Brand / Logo -->
                <div class="brand-logo" aria-label="BW Dashboard">
                    <div class="brand-icon" aria-hidden="true">
                        <i class="fas fa-wind"></i>
                    </div>
                    <span class="brand-name">BW&nbsp;Dashboard</span>
                </div>

                <h1 class="login-title">Welcome Back</h1>
                <p class="login-subtitle">Enter your credentials to access your account</p>

                <!-- Login Form -->
                <form class="login-form" id="loginForm" novalidate aria-label="Login form">

                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input"
                                placeholder="john@example.com"
                                autocomplete="email"
                                required
                                aria-describedby="email-error"
                                aria-required="true"
                            >
                        </div>
                        <span class="field-error-msg" id="email-error" role="alert" aria-live="polite"></span>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock" aria-hidden="true"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                                aria-describedby="password-error"
                                aria-required="true"
                            >
                            <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <span class="field-error-msg" id="password-error" role="alert" aria-live="polite"></span>
                    </div>

                    <!-- Remember & Forgot Password -->
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" id="rememberMe" name="rememberMe" autocomplete="off">
                            <span>Remember Me</span>
                        </label>
                        <a href="#" class="forgot-password" id="forgotPasswordLink">Forgot Password?</a>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn-login" id="loginBtn">
                        <span class="btn-text">Login</span>
                    </button>
                </form>

                <!-- Sign Up Link -->
                <div class="signup-link">
                    Don't have an account? <a href="signup.php">Sign up here</a>
                </div>

                <!-- Divider -->
                <div class="divider">
                    <span>OR SIGN IN WITH</span>
                </div>

                <!-- Social Login -->
                <div class="social-login" role="group" aria-label="Social sign-in options">
                    <button class="social-btn google" title="Sign in with Google" aria-label="Sign in with Google">
                        <i class="fab fa-google" aria-hidden="true"></i>
                    </button>
                    <button class="social-btn facebook" title="Sign in with Facebook" aria-label="Sign in with Facebook">
                        <i class="fab fa-facebook-f" aria-hidden="true"></i>
                    </button>
                    <button class="social-btn linkedin" title="Sign in with LinkedIn" aria-label="Sign in with LinkedIn">
                        <i class="fab fa-linkedin-in" aria-hidden="true"></i>
                    </button>
                    <button class="social-btn github" title="Sign in with GitHub" aria-label="Sign in with GitHub">
                        <i class="fab fa-github" aria-hidden="true"></i>
                    </button>
                </div>

            </div>
        </div>

        <!-- Floating Elements for Background Design -->
        <div class="floating-element float-1" aria-hidden="true"></div>
        <div class="floating-element float-2" aria-hidden="true"></div>
        <div class="floating-element float-3" aria-hidden="true"></div>
    </div>

    <script src="js/login.js"></script>
</body>
</html>
