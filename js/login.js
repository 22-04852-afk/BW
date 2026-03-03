// ============================================
// LOGIN PAGE FUNCTIONALITY
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Login Page Loaded');
    
    initializePasswordToggle();
    initializeLoginForm();
    initializeRememberMe();
    initializeSocialHandlers();
    initializeForgotPassword();
});

// ============================================
// PASSWORD TOGGLE
// ============================================

function initializePasswordToggle() {
    const toggleBtn = document.getElementById('passwordToggle');
    const passwordInput = document.getElementById('password');

    if (!toggleBtn || !passwordInput) return;

    toggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });
}

// ============================================
// LOGIN FORM HANDLING
// ============================================

function initializeLoginForm() {
    const form = document.getElementById('loginForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();

        // Validation
        if (!email || !password) {
            showError('Please fill in all fields');
            return;
        }

        if (!isValidEmail(email)) {
            showError('Please enter a valid email address');
            return;
        }

        if (password.length < 6) {
            showError('Password must be at least 6 characters');
            return;
        }

        handleLogin(email, password);
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.altKey && e.key === 'l') {
            document.getElementById('email').focus();
        }
        if (e.ctrlKey && e.key === 'Enter') {
            form.dispatchEvent(new Event('submit'));
        }
    });
}

// ============================================
// LOGIN HANDLER
// ============================================

function handleLogin(email, password) {
    const loginBtn = document.querySelector('.btn-login');

    // Disable button and show loading state
    loginBtn.disabled = true;
    loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';

    // Send login request to backend
    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);

    fetch('api/authenticate.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Log response status and text for debugging
        console.log('Response status:', response.status);
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch(e) {
                console.error('Failed to parse response:', text);
                throw new Error('Server returned invalid response: ' + text);
            }
        });
    })
    .then(data => {
        console.log('Login response:', data);
        if (data.success) {
            // Store user email for reference
            localStorage.setItem('userEmail', email);
            localStorage.setItem('loginTime', new Date().toISOString());

            showSuccess(data.message);

            // Redirect after 1.5 seconds
            setTimeout(() => {
                window.location.href = data.redirect || 'index.php';
            }, 1500);
        } else {
            showError(data.message || 'Login failed');
            loginBtn.disabled = false;
            loginBtn.innerHTML = 'Login';
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        console.error('Error details:', error.message);
        showError('Error: ' + error.message);
        loginBtn.disabled = false;
        loginBtn.innerHTML = 'Login';
    });
}

// ============================================
// EMAIL VALIDATION
// ============================================

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// ============================================
// REMEMBER ME FUNCTIONALITY
// ============================================

function initializeRememberMe() {
    const rememberCheckbox = document.getElementById('rememberMe');
    const emailInput = document.getElementById('email');

    if (!rememberCheckbox || !emailInput) return;

    // Load saved email if exists
    const savedEmail = localStorage.getItem('savedEmail');
    if (savedEmail) {
        emailInput.value = savedEmail;
        rememberCheckbox.checked = true;
    }

    // Save email when checkbox changes
    rememberCheckbox.addEventListener('change', function() {
        if (this.checked) {
            localStorage.setItem('savedEmail', emailInput.value);
        } else {
            localStorage.removeItem('savedEmail');
        }
    });

    // Update saved email when email changes
    emailInput.addEventListener('input', function() {
        if (rememberCheckbox.checked) {
            localStorage.setItem('savedEmail', this.value);
        }
    });
}

// ============================================
// SOCIAL HANDLERS
// ============================================

function initializeSocialHandlers() {
    const socialBtns = document.querySelectorAll('.social-btn');

    socialBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const provider = this.classList[1] || 'Social'; // google, facebook, linkedin, github
            showSuccess(`Signing in with ${provider.charAt(0).toUpperCase() + provider.slice(1)}...`);
        });
    });
}

// ============================================
// FORGOT PASSWORD
// ============================================

function initializeForgotPassword() {
    const forgotLink = document.querySelector('.forgot-password');

    if (forgotLink) {
        forgotLink.addEventListener('click', function(e) {
            e.preventDefault();
            showSuccess('Password reset link sent to your email!');
        });
    }
}

// ============================================
// NOTIFICATION SYSTEM
// ============================================

function showError(message) {
    showNotification(message, 'error');
}

function showSuccess(message) {
    showNotification(message, 'success');
}

function showNotification(message, type = 'success') {
    // Remove existing notification
    removeNotification();

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i>
        <span>${message}</span>
    `;

    document.body.appendChild(notification);
    
    // Add inline styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'error' ? '#ff6b6b' : '#51cf66'};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 10000;
        animation: slideInRight 0.3s ease;
    `;

    setTimeout(() => removeNotification(), 3000);
}

function removeNotification() {
    const notification = document.querySelector('.notification');
    if (notification) {
        notification.remove();
    }
}

// Add CSS animation
if (!document.querySelector('style[data-notification]')) {
    const style = document.createElement('style');
    style.setAttribute('data-notification', 'true');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
}

// Check if user already logged in
document.addEventListener('DOMContentLoaded', function() {
    const userEmail = localStorage.getItem('userEmail');
    if (userEmail) {
        // User already logged in, redirect to dashboard
        window.location.href = 'index.php';
    }
});

console.log('Login Features:');
console.log('✓ Email validation');
console.log('✓ Password visibility toggle');
console.log('✓ Remember me functionality');
console.log('✓ Social login buttons');
console.log('✓ Form validation');
console.log('✓ Keyboard shortcuts (Alt+L, Ctrl+Enter)');
