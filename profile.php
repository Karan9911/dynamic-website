<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireUserLogin();

$pageTitle = 'My Profile';
$message = '';
$messageType = '';

// Get user data
$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email)) {
        $message = 'Name and email are required';
        $messageType = 'danger';
    } elseif (!validateEmail($email)) {
        $message = 'Please enter a valid email address';
        $messageType = 'danger';
    } elseif ($phone && !validatePhone($phone)) {
        $message = 'Please enter a valid 10-digit phone number';
        $messageType = 'danger';
    } else {
        try {
            $db->beginTransaction();
            
            // Check if email is already taken by another user
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $message = 'Email is already taken by another user';
                $messageType = 'danger';
            } else {
                // Update basic info
                $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ?, city = ? WHERE id = ?");
                $stmt->execute([$name, $email, $phone, $city, $_SESSION['user_id']]);
                
                // Update password if provided
                if (!empty($newPassword)) {
                    if (empty($currentPassword)) {
                        $message = 'Current password is required to change password';
                        $messageType = 'danger';
                        $db->rollback();
                    } elseif (!password_verify($currentPassword, $user['password'])) {
                        $message = 'Current password is incorrect';
                        $messageType = 'danger';
                        $db->rollback();
                    } elseif (strlen($newPassword) < 6) {
                        $message = 'New password must be at least 6 characters long';
                        $messageType = 'danger';
                        $db->rollback();
                    } elseif ($newPassword !== $confirmPassword) {
                        $message = 'New passwords do not match';
                        $messageType = 'danger';
                        $db->rollback();
                    } else {
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                        
                        $db->commit();
                        $message = 'Profile and password updated successfully!';
                        $messageType = 'success';
                        
                        // Update session data
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_email'] = $email;
                        $_SESSION['user_phone'] = $phone;
                        $_SESSION['user_city'] = $city;
                        
                        // Refresh user data
                        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $user = $stmt->fetch();
                    }
                } else {
                    $db->commit();
                    $message = 'Profile updated successfully!';
                    $messageType = 'success';
                    
                    // Update session data
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_phone'] = $phone;
                    $_SESSION['user_city'] = $city;
                    
                    // Refresh user data
                    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch();
                }
            }
        } catch (Exception $e) {
            $db->rollback();
            $message = 'Error updating profile: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="auth-card">
                    <div class="auth-header">
                        <h2 class="text-center mb-4">My Profile</h2>
                        <p class="text-center text-muted">Manage your account information</p>
                    </div>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                            <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                <div class="invalid-feedback">Please enter your full name.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                       pattern="[0-9]{10}">
                                <div class="invalid-feedback">Please enter a valid 10-digit phone number.</div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" 
                                       value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-12">
                                <hr class="my-4">
                                <h5>Change Password</h5>
                                <p class="text-muted">Leave blank if you don't want to change your password</p>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password">
                                <div class="invalid-feedback">Password must be at least 6 characters.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password">
                                <div class="invalid-feedback">Please confirm your new password.</div>
                            </div>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-lg me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="my-bookings.php" class="btn btn-outline-primary me-2">
                            <i class="bi bi-calendar-check me-2"></i>My Bookings
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-house me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>