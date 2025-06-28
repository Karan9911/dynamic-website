<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Initialize database
initializeDatabase();

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        if (loginAdmin($username, $password)) {
            // Successful admin login - redirect directly to admin dashboard
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}

$pageTitle = 'Admin Login';
$hideNavbar = true;
?>

<?php include '../includes/header.php'; ?>

<div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, var(--secondary-color) 0%, #e8f5e8 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg border-0 rounded-xl">
                    <div class="card-header bg-gradient-primary text-white text-center py-4">
                        <i class="bi bi-shield-lock display-4 mb-3"></i>
                        <h4 class="mb-0 fw-bold">Admin Login</h4>
                        <p class="mb-0 opacity-75">Serenity Spa Management</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Username or Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-person text-primary"></i>
                                    </span>
                                    <input type="text" class="form-control" name="username" 
                                           value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                                           placeholder="Enter username or email" required>
                                    <div class="invalid-feedback">Please enter your username or email.</div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-lock text-primary"></i>
                                    </span>
                                    <input type="password" class="form-control" name="password" 
                                           placeholder="Enter password" required>
                                    <div class="invalid-feedback">Please enter your password.</div>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Login to Dashboard
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <div class="alert alert-info">
                                <strong>Default Credentials:</strong><br>
                                Username: <code>admin</code><br>
                                Password: <code>admin123</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$extraScripts = '<script>
    document.addEventListener("DOMContentLoaded", function() {
        const forms = document.querySelectorAll(".needs-validation");
        Array.from(forms).forEach(form => {
            form.addEventListener("submit", event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add("was-validated");
            });
        });
    });
</script>';
include '../includes/footer.php'; 
?>