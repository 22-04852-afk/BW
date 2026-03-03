// ============================================
// USER PROFILE PAGE
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Profile Page Loaded');
    
    loadProfileData();
    initializeProfileEvents();
    initializeProfilePictureUpload();
});

// ============================================
// LOAD PROFILE DATA
// ============================================

function loadProfileData() {
    const currentUserProfile = localStorage.getItem('currentUserProfile');
    const userEmail = localStorage.getItem('userEmail');
    const loginTime = localStorage.getItem('loginTime');
    
    if (currentUserProfile) {
        const user = JSON.parse(currentUserProfile);
        displayProfile(user.firstName, user.lastName, user.email, loginTime);
    } else if (userEmail) {
        const name = userEmail.split('@')[0];
        displayProfile(name, '', userEmail, loginTime);
    } else {
        // User not logged in, redirect to login
        window.location.href = 'login.php';
    }
}

function displayProfile(firstName, lastName, email, loginTime) {
    // Update header
    const profileNameEl = document.getElementById('profileName');
    const profileEmailEl = document.getElementById('profileEmail');
    
    if (profileNameEl) {
        profileNameEl.textContent = `${firstName} ${lastName}`.trim() || 'User';
    }
    if (profileEmailEl) {
        profileEmailEl.textContent = email || '';
    }
    
    // Load and display profile picture
    loadProfilePicture();
    
    // Update personal information
    const firstNameEl = document.getElementById('firstName');
    const lastNameEl = document.getElementById('lastName');
    
    if (firstNameEl) firstNameEl.textContent = firstName || '—';
    if (lastNameEl) lastNameEl.textContent = lastName || '—';
    
    // Update account information
    const emailAddressEl = document.getElementById('emailAddress');
    if (emailAddressEl) emailAddressEl.textContent = email || '—';
    
    // Member since (account creation time)
    const memberSinceEl = document.getElementById('memberSince');
    if (memberSinceEl) {
        const memberSince = new Date().toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        memberSinceEl.textContent = memberSince;
    }
    
    // Last login
    const lastLoginEl = document.getElementById('lastLogin');
    if (lastLoginEl) {
        if (loginTime) {
            try {
                const lastLoginDate = new Date(loginTime);
                const lastLoginStr = lastLoginDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                lastLoginEl.textContent = lastLoginStr;
            } catch (e) {
                lastLoginEl.textContent = 'Today';
            }
        } else {
            lastLoginEl.textContent = 'Today';
        }
    }

    // Store for edit modal
    window.currentUser = {
        firstName: firstName,
        lastName: lastName,
        email: email
    };
}

// ============================================
// EVENT LISTENERS
// ============================================

function initializeProfileEvents() {
    const editBtn = document.getElementById('editBtn');
    const backBtn = document.getElementById('backBtn');
    const profileLogoutBtn = document.getElementById('profileLogoutBtn');
    const editModal = document.getElementById('editModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const editForm = document.getElementById('editForm');

    // Edit button
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            if (window.currentUser) {
                const editFirstName = document.getElementById('editFirstName');
                const editLastName = document.getElementById('editLastName');
                
                if (editFirstName) editFirstName.value = window.currentUser.firstName;
                if (editLastName) editLastName.value = window.currentUser.lastName;
            }
            if (editModal) editModal.classList.add('show');
        });
    }

    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            if (editModal) editModal.classList.remove('show');
        });
    }

    // Back button
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            window.location.href = 'index.php';
        });
    }

    // Logout button
    if (profileLogoutBtn) {
        profileLogoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });
    }

    // Edit form submission
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveProfile();
        });
    }

    // Close modal when clicking outside
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === editModal) {
                editModal.classList.remove('show');
            }
        });
    }
}

// ============================================
// SAVE PROFILE
// ============================================

function saveProfile() {
    const editFirstName = document.getElementById('editFirstName');
    const editLastName = document.getElementById('editLastName');
    
    const firstName = editFirstName ? editFirstName.value.trim() : '';
    const lastName = editLastName ? editLastName.value.trim() : '';

    if (!firstName || !lastName) {
        showNotification('Please fill in all fields', 'error');
        return;
    }

    // Update localStorage
    const currentProfile = JSON.parse(localStorage.getItem('currentUserProfile') || '{}');
    currentProfile.firstName = firstName;
    currentProfile.lastName = lastName;
    
    localStorage.setItem('currentUserProfile', JSON.stringify(currentProfile));
    localStorage.setItem('newUserAccount', JSON.stringify(currentProfile));

    // Update window reference
    if (window.currentUser) {
        window.currentUser.firstName = firstName;
        window.currentUser.lastName = lastName;
    }
    
    // Update display
    const profileNameEl = document.getElementById('profileName');
    const firstNameEl = document.getElementById('firstName');
    const lastNameEl = document.getElementById('lastName');
    
    if (profileNameEl) profileNameEl.textContent = `${firstName} ${lastName}`;
    if (firstNameEl) firstNameEl.textContent = firstName;
    if (lastNameEl) lastNameEl.textContent = lastName;

    // Show success message
    showNotification('Profile updated successfully!', 'success');

    // Close modal
    setTimeout(function() {
        const editModal = document.getElementById('editModal');
        if (editModal) editModal.classList.remove('show');
    }, 500);
}

// ============================================
// PROFILE PICTURE UPLOAD
// ============================================

function initializeProfilePictureUpload() {
    const uploadBtn = document.getElementById('avatarUploadBtn');
    const fileInput = document.getElementById('profilePictureInput');

    if (uploadBtn) {
        uploadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fileInput.click();
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Check file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showNotification('File size must be less than 2MB', 'error');
                    return;
                }

                // Check file type
                if (!file.type.startsWith('image/')) {
                    showNotification('Please select a valid image file', 'error');
                    return;
                }

                // Convert to base64 and save
                const reader = new FileReader();
                reader.onload = function(event) {
                    const base64Image = event.target.result;
                    localStorage.setItem('profilePicture', base64Image);
                    loadProfilePicture();
                    showNotification('Profile picture updated successfully!', 'success');
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

function loadProfilePicture() {
    const profilePicture = localStorage.getItem('profilePicture');
    const avatarDiv = document.getElementById('profileAvatar');

    if (profilePicture && avatarDiv) {
        // Clear existing content
        avatarDiv.innerHTML = '';
        
        // Create and display image
        const img = document.createElement('img');
        img.src = profilePicture;
        img.alt = 'Profile Picture';
        avatarDiv.appendChild(img);
    }
}

// ============================================
// LOGOUT
// ============================================

function logout() {
    // Clear user data
    localStorage.removeItem('userEmail');
    localStorage.removeItem('loginTime');
    localStorage.removeItem('newUserAccount');
    localStorage.removeItem('currentUserProfile');
    localStorage.removeItem('sidebarCollapsed');
    localStorage.removeItem('savedEmail');
    localStorage.removeItem('profilePicture');

    // Redirect to login
    window.location.href = 'login.php';
}

// ============================================
// NOTIFICATION SYSTEM
// ============================================

function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    if (notification) {
        notification.textContent = message;
        notification.className = `notification show ${type}`;

        setTimeout(function() {
            notification.classList.remove('show');
        }, 3000);
    }
}

console.log('Profile Features:');
console.log('✓ Load user profile data from localStorage');
console.log('✓ Display personal and account information');
console.log('✓ Edit profile functionality');
console.log('✓ Save profile changes');
console.log('✓ Logout with data clearing');
console.log('✓ Responsive design');
