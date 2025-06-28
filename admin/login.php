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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Serenity Spa Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Admin CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image" style="background: linear-gradient(rgba(78, 115, 223, 0.8), rgba(78, 115, 223, 0.8)), url('https://images.pexels.com/photos/3757942/pexels-photo-3757942.jpeg?auto=compress&cs=tinysrgb&w=600'); background-size: cover; background-position: center;"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                        <div class="mb-4">
                                            <i class="bi bi-flower1 display-4 text-primary"></i>
                                            <h5 class="text-primary mt-2">Serenity Spa Admin</h5>
                                        </div>
                                    </div>
                                    
                                    <?php if ($error): ?>
                                        <div class="alert alert-danger">
                                            <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <form class="user" method="POST">
                                        <div class="form-group mb-3">
                                            <input type="text" class="form-control form-control-user"
                                                id="exampleInputEmail" name="username" aria-describedby="emailHelp"
                                                placeholder="Enter Username or Email..." 
                                                value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <input type="password" class="form-control form-control-user"
                                                id="exampleInputPassword" name="password" placeholder="Password" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="customCheck">
                                                <label class="custom-control-label" for="customCheck">Remember Me</label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block w-100">
                                            Login
                                        </button>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="<?php echo SITE_URL; ?>" target="_blank">
                                            <i class="bi bi-globe me-1"></i>View Website
                                        </a>
                                    </div>
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
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Admin JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/admin.js"></script>
</body>
</html>