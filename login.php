<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BW Gas Detector Sales Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <!-- Gradient Background -->
    <div class="bg-gradient"></div>

    <!-- Main Container -->
    <div class="login-container">
        <!-- Login Card -->
        <div class="login-card">
            <!-- Gradient Top Border -->
            <div class="card-border"></div>

            <!-- Login Form Content -->
            <div class="login-content">
                <h1 class="login-title">Get Started Now</h1>
                <p class="login-subtitle">Enter your credentials to login your account</p>

                <!-- Login Form -->
                <form class="login-form" id="loginForm">
                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-input" 
                                placeholder="jhon@example.com" 
                                required
                            >
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="Enter your password" 
                                required
                            >
                            <button type="button" class="password-toggle" id="passwordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember & Forgot Password -->
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" id="rememberMe" name="rememberMe">
                            <span>Remember Me</span>
                        </label>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn-login">Login</button>
                </form>

                <!-- Sign Up Link -->
                <div class="signup-link">
                    Don't have an account yet? <a href="signup.php">Sign up here</a>
                </div>

                <!-- Divider -->
                <div class="divider">
                    <span>OR SIGN IN WITH</span>
                </div>

                <!-- Social Login -->
                <div class="social-login">
                    <button class="social-btn google" title="Sign in with Google">
                        <i class="fab fa-google"></i>
                    </button>
                    <button class="social-btn facebook" title="Sign in with Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </button>
                    <button class="social-btn linkedin" title="Sign in with LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </button>
                    <button class="social-btn github" title="Sign in with GitHub">
                        <i class="fab fa-github"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Floating Elements for Background Design -->
        <div class="floating-element float-1"></div>
        <div class="floating-element float-2"></div>
        <div class="floating-element float-3"></div>
    </div>

    <script src="js/login.js"></script>
</body>
</html>
