// ============================================
// BW GAS DETECTOR SALES DASHBOARD
// JavaScript for Interactivity & Charts
// ============================================

// Initialize when DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard Initialized');
    
    // Initialize UI Elements
    initializeSidebarToggle();
    initializeSubmenuToggle();
    initializeProfileDropdown();
    initializeResponsive();
    loadUserProfile();
    
    // Initialize All Charts
    initializeCharts();
});

// ============================================
// SIDEBAR TOGGLE FUNCTIONALITY
// ============================================

function initializeSidebarToggle() {
    const hamburgerBtn = document.getElementById('hamburger') || document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');

    if (!hamburgerBtn) return;

    hamburgerBtn.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        if (mainContent) mainContent.classList.toggle('sidebar-collapsed');

        // Store preference in localStorage
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    });

    // Restore sidebar state from localStorage
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('sidebar-collapsed');
    }
}

// ============================================
// SUBMENU TOGGLE FUNCTIONALITY
// ============================================

function initializeSubmenuToggle() {
    const submenuToggles = document.querySelectorAll('.submenu-toggle');

    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const submenuId = this.getAttribute('data-submenu');
            const submenu = document.getElementById(submenuId);

            if (submenu) {
                submenu.classList.toggle('active');
                this.classList.toggle('active');
            }
        });
    });
}

// ============================================
// PROFILE DROPDOWN FUNCTIONALITY
// ============================================

function initializeProfileDropdown() {
    const profileBtn = document.getElementById('profileBtn');
    const profileMenu = document.getElementById('profileMenu');

    if (!profileBtn || !profileMenu) return;

    profileBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        profileMenu.classList.toggle('active');
    });

    document.addEventListener('click', function(e) {
        if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
            profileMenu.classList.remove('active');
        }
    });
}

// ============================================
// RESPONSIVE INITIALIZATION
// ============================================

function initializeResponsive() {
    // Handle window resize
    window.addEventListener('resize', function() {
        // Add any responsive adjustments here
    });
}

// ============================================
// USER PROFILE LOADING
// ============================================

function loadUserProfile() {
    const userEmail = localStorage.getItem('userEmail');
    const userData = localStorage.getItem('newUserAccount');
    
    if (!userData && userEmail) {
        const name = userEmail.split('@')[0].charAt(0).toUpperCase() + userEmail.split('@')[0].slice(1);
        updateProfileDisplay(name, null, userEmail);
    } else if (userData) {
        const user = JSON.parse(userData);
        updateProfileDisplay(user.firstName, user.lastName, user.email);
    }
}

function updateProfileDisplay(firstName, lastName, email) {
    const profileNameEl = document.querySelector('.profile-name');
    const welcomeNameEl = document.querySelector('.welcome-name');
    
    if (firstName && lastName) {
        const displayName = `${firstName} ${lastName}`;
        if (profileNameEl) profileNameEl.textContent = displayName;
        if (welcomeNameEl) welcomeNameEl.textContent = `Congratulations ${firstName}!`;
        // Store profile info for profile page
        localStorage.setItem('currentUserProfile', JSON.stringify({
            firstName: firstName,
            lastName: lastName,
            email: email
        }));
    } else if (firstName) {
        if (profileNameEl) profileNameEl.textContent = firstName;
        if (welcomeNameEl) welcomeNameEl.textContent = `Congratulations ${firstName}!`;
        localStorage.setItem('currentUserProfile', JSON.stringify({
            firstName: firstName,
            lastName: '',
            email: email
        }));
    }
}

// ============================================
// CHART INITIALIZATION
// ============================================

function initializeCharts() {
    initializeSparklineCharts();
    initializeDeliveredChart();
    initializeSoldChart();
    initializeMonthlyComparisonChart();
    initializeClientsChart();
    initializeTrendChart();
    initializeGroupAChart();
    initializeGroupBChart();
}

// ============================================
// SPARKLINE CHARTS
// ============================================

function initializeSparklineCharts() {
    const sparklineIds = ['sparkline1', 'sparkline2', 'sparkline3', 'sparkline4'];
    const sparklineData = [
        [15, 25, 18, 32, 28, 35, 40, 38],
        [10, 15, 12, 20, 18, 25, 30, 28],
        [8, 12, 10, 15, 14, 20, 25, 23],
        [5, 10, 8, 12, 11, 15, 18, 16]
    ];

    sparklineIds.forEach((id, index) => {
        const ctx = document.getElementById(id);
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7', 'Day 8'],
                    datasets: [{
                        data: sparklineData[index],
                        borderColor: '#f4d03f',
                        backgroundColor: 'rgba(244, 208, 63, 0.1)',
                        borderWidth: 1.5,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { display: false },
                        y: { display: false }
                    }
                }
            });
        }
    });
}

// ============================================
// DELIVERED CHART (Donut)
// ============================================

function initializeDeliveredChart() {
    const ctx = document.getElementById('deliveredChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Delivered', 'Pending'],
            datasets: [{
                data: [696, 104],
                backgroundColor: ['#f4d03f', '#2f5fa7'],
                borderColor: '#13172c',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#e0e0e0',
                        padding: 15,
                        font: { size: 12 }
                    }
                }
            }
        }
    });
}

// ============================================
// SOLD CHART (Donut)
// ============================================

function initializeSoldChart() {
    const ctx = document.getElementById('soldChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Sold', 'Available'],
            datasets: [{
                data: [311, 489],
                backgroundColor: ['#51cf66', '#2f5fa7'],
                borderColor: '#13172c',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#e0e0e0',
                        padding: 15,
                        font: { size: 12 }
                    }
                }
            }
        }
    });
}

// ============================================
// MONTHLY COMPARISON CHART (Bar)
// ============================================

function initializeMonthlyComparisonChart() {
    const ctx = document.getElementById('monthlyComparisonChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
            datasets: [
                {
                    label: 'Delivered',
                    data: [18, 14, 42, 46, 83, 25, 41, 39],
                    backgroundColor: '#f4d03f',
                    borderColor: '#d4b000',
                    borderWidth: 1
                },
                {
                    label: 'Sold',
                    data: [12, 15, 28, 35, 52, 20, 35, 42],
                    backgroundColor: '#2f5fa7',
                    borderColor: '#1e3c72',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: { color: '#e0e0e0', font: { size: 12 } }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: '#a0a0a0' },
                    grid: { color: 'rgba(255, 255, 255, 0.05)' }
                },
                x: {
                    ticks: { color: '#a0a0a0' },
                    grid: { display: false }
                }
            }
        }
    });
}

// ============================================
// CLIENTS CHART (Horizontal Bar)
// ============================================

function initializeClientsChart() {
    const ctx = document.getElementById('clientsChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Addison Zamora', 'Client 2', 'Client 3', 'Client 4', 'Client 5',
                     'Client 6', 'Client 7', 'Client 8', 'Client 9', 'Client 10',
                     'Client 11', 'Client 12', 'Client 13', 'Client 14', 'Client 15'],
            datasets: [{
                label: 'Units',
                data: [97, 85, 72, 68, 63, 58, 52, 48, 45, 42, 38, 35, 30, 28, 25],
                backgroundColor: '#2f5fa7',
                borderColor: '#1e3c72',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: {
                    labels: { color: '#e0e0e0' }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#a0a0a0' },
                    grid: { color: 'rgba(255, 255, 255, 0.05)' }
                },
                y: {
                    ticks: { color: '#a0a0a0' },
                    grid: { display: false }
                }
            }
        }
    });
}

// ============================================
// TREND CHART (Line)
// ============================================

function initializeTrendChart() {
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
            datasets: [
                {
                    label: 'Delivered',
                    data: [18, 14, 42, 46, 83, 25, 41, 39],
                    borderColor: '#f4d03f',
                    backgroundColor: 'rgba(244, 208, 63, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Sold',
                    data: [12, 15, 28, 35, 52, 20, 35, 42],
                    borderColor: '#51cf66',
                    backgroundColor: 'rgba(81, 207, 102, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: { color: '#e0e0e0' }
                }
            },
            scales: {
                y: {
                    ticks: { color: '#a0a0a0' },
                    grid: { color: 'rgba(255, 255, 255, 0.05)' }
                },
                x: {
                    ticks: { color: '#a0a0a0' },
                    grid: { display: false }
                }
            }
        }
    });
}

// ============================================
// GROUP A CHART (Bar)
// ============================================

function initializeGroupAChart() {
    const ctx = document.getElementById('groupAChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['A-100', 'A-200', 'A-300', 'A-400', 'A-500'],
            datasets: [
                {
                    label: 'Delivered',
                    data: [63, 58, 52, 45, 38],
                    backgroundColor: '#f4d03f'
                },
                {
                    label: 'Sold',
                    data: [45, 42, 38, 32, 28],
                    backgroundColor: '#2f5fa7'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: { color: '#e0e0e0' }
                }
            },
            scales: {
                y: {
                    ticks: { color: '#a0a0a0' },
                    grid: { color: 'rgba(255, 255, 255, 0.05)' }
                },
                x: {
                    ticks: { color: '#a0a0a0' },
                    grid: { display: false }
                }
            }
        }
    });
}

// ============================================
// GROUP B CHART (Bar)
// ============================================

function initializeGroupBChart() {
    const ctx = document.getElementById('groupBChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['B-100', 'B-200', 'B-300', 'B-400', 'B-500'],
            datasets: [
                {
                    label: 'Delivered',
                    data: [52, 48, 45, 40, 35],
                    backgroundColor: '#51cf66'
                },
                {
                    label: 'Sold',
                    data: [38, 35, 32, 28, 25],
                    backgroundColor: '#2f5fa7'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: { color: '#e0e0e0' }
                }
            },
            scales: {
                y: {
                    ticks: { color: '#a0a0a0' },
                    grid: { color: 'rgba(255, 255, 255, 0.05)' }
                },
                x: {
                    ticks: { color: '#a0a0a0' },
                    grid: { display: false }
                }
            }
        }
    });
}

// ============================================
// LOGOUT FUNCTIONALITY
// ============================================

function logoutHandler() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}

const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        logoutHandler();
    });
}

const profileLogoutBtn = document.getElementById('profileLogoutBtn');
if (profileLogoutBtn) {
    profileLogoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        logoutHandler();
    });
}

// ============================================
// CONSOLE LOG FOR DEVELOPMENT
// ============================================

console.log('Dashboard Features:');
console.log('✓ Responsive sidebar (toggle with hamburger button)');
console.log('✓ Submenu expansion (Models dropdown)');
console.log('✓ Profile dropdown menu');
console.log('✓ Interactive Chart.js visualizations');
console.log('✓ LocalStorage sidebar state persistence');
console.log('✓ Professional enterprise design');
