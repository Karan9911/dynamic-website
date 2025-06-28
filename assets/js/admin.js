// Admin Panel JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('adminSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('show');
            }
        });
        
        // Close sidebar when clicking overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove('show');
                    }
                }
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                sidebar.classList.remove('show');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('show');
                }
            }
        });
    }
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm-delete') || 'Are you sure you want to delete this item?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Enhanced table responsiveness
    const tables = document.querySelectorAll('.table-responsive');
    tables.forEach(table => {
        // Add scroll indicators for mobile
        if (window.innerWidth <= 768) {
            table.style.position = 'relative';
            
            // Add scroll hint
            const scrollHint = document.createElement('div');
            scrollHint.className = 'text-muted small text-center mt-2';
            scrollHint.innerHTML = '<i class="bi bi-arrow-left-right"></i> Scroll horizontally to view more';
            table.parentNode.insertBefore(scrollHint, table.nextSibling);
            
            // Hide hint after first scroll
            table.addEventListener('scroll', function() {
                scrollHint.style.display = 'none';
            }, { once: true });
        }
    });
    
    // Auto-refresh for real-time updates (optional)
    if (document.querySelector('.auto-refresh')) {
        setInterval(() => {
            // Refresh specific sections without full page reload
            refreshDashboardStats();
        }, 30000); // Refresh every 30 seconds
    }
});

// Dashboard functions
function refreshDashboardStats() {
    fetch('ajax/get_dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatCards(data.stats);
            }
        })
        .catch(error => console.error('Error refreshing stats:', error));
}

function updateStatCards(stats) {
    Object.keys(stats).forEach(key => {
        const element = document.querySelector(`[data-stat="${key}"]`);
        if (element) {
            element.textContent = stats[key];
        }
    });
}

// Lead management functions
function viewLead(id) {
    showLoading();
    fetch(`get_lead_details.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showLeadModal(data.lead);
            } else {
                showAlert('error', 'Failed to load lead details');
            }
        })
        .catch(error => {
            hideLoading();
            showAlert('error', 'Error loading lead details');
        });
}

function showLeadModal(lead) {
    const modalBody = document.getElementById('leadModalBody');
    modalBody.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold">Customer Information</h6>
                <p><strong>Name:</strong> ${lead.full_name}</p>
                <p><strong>Email:</strong> ${lead.email}</p>
                <p><strong>Phone:</strong> ${lead.phone}</p>
                <p><strong>Type:</strong> <span class="badge bg-primary">${lead.type}</span></p>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold">Lead Details</h6>
                <p><strong>Status:</strong> <span class="badge bg-warning">${lead.status}</span></p>
                <p><strong>Created:</strong> ${new Date(lead.created_at).toLocaleString()}</p>
                <p><strong>Therapist:</strong> ${lead.therapist_name || 'N/A'}</p>
            </div>
            <div class="col-12 mt-3">
                <h6 class="fw-bold">Message</h6>
                <p class="bg-light p-3 rounded">${lead.message || 'No message provided'}</p>
            </div>
            ${lead.admin_notes ? `
                <div class="col-12 mt-3">
                    <h6 class="fw-bold">Admin Notes</h6>
                    <p class="bg-warning bg-opacity-10 p-3 rounded">${lead.admin_notes}</p>
                </div>
            ` : ''}
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('leadModal')).show();
}

// Utility functions
function showLoading() {
    const loader = document.createElement('div');
    loader.id = 'globalLoader';
    loader.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
    loader.style.backgroundColor = 'rgba(0,0,0,0.5)';
    loader.style.zIndex = '9999';
    loader.innerHTML = '<div class="spinner-border text-light" role="status"></div>';
    document.body.appendChild(loader);
}

function hideLoading() {
    const loader = document.getElementById('globalLoader');
    if (loader) {
        loader.remove();
    }
}

function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer') || createAlertContainer();
    const alertClass = type === 'error' ? 'danger' : type;
    const iconClass = type === 'error' ? 'exclamation-triangle' : 'check-circle';
    
    const alertHTML = `
        <div class="alert alert-${alertClass} alert-dismissible fade show" role="alert">
            <i class="bi bi-${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertContainer.innerHTML = alertHTML;
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alertContainer';
    container.className = 'position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9998';
    document.body.appendChild(container);
    return container;
}

// Export functions
function exportData(type, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const url = `export_${type}.php${queryString ? '?' + queryString : ''}`;
    window.open(url, '_blank');
}

// Real-time notifications (if WebSocket is implemented)
function initializeNotifications() {
    // Placeholder for WebSocket connection
    // This would connect to a WebSocket server for real-time updates
}

// Chart initialization (if charts are added)
function initializeCharts() {
    // Placeholder for chart initialization
    // This would initialize Chart.js or similar library
}

// Data table enhancements
function initializeDataTables() {
    // Placeholder for DataTables initialization
    // This would add sorting, pagination, and search to tables
}

// File upload handling
function handleFileUpload(input, callback) {
    const file = input.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('file', file);
    
    showLoading();
    
    fetch('upload_file.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            callback(data);
        } else {
            showAlert('error', data.message || 'Upload failed');
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('error', 'Upload error occurred');
    });
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + S to save forms
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        const activeForm = document.querySelector('form:focus-within');
        if (activeForm) {
            activeForm.submit();
        }
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            const modal = bootstrap.Modal.getInstance(openModal);
            if (modal) modal.hide();
        }
    }
});

// Print functionality
function printTable(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Print Table</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { padding: 20px; }
                    @media print { 
                        .no-print { display: none !important; }
                    }
                </style>
            </head>
            <body>
                ${table.outerHTML}
                <script>window.print(); window.close();</script>
            </body>
        </html>
    `);
    printWindow.document.close();
}

// Local storage helpers
function saveToLocalStorage(key, data) {
    try {
        localStorage.setItem(key, JSON.stringify(data));
    } catch (error) {
        console.error('Failed to save to localStorage:', error);
    }
}

function getFromLocalStorage(key) {
    try {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : null;
    } catch (error) {
        console.error('Failed to get from localStorage:', error);
        return null;
    }
}

// Session management
function checkSession() {
    fetch('check_session.php')
        .then(response => response.json())
        .then(data => {
            if (!data.valid) {
                showAlert('warning', 'Your session has expired. Please login again.');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Session check failed:', error);
        });
}

// Check session every 5 minutes
setInterval(checkSession, 5 * 60 * 1000);