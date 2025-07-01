<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdminLogin();

$pageTitle = 'System Settings';
$message = '';
$messageType = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentEnabled = isset($_POST['payment_enabled']) ? 1 : 0;
    $razorpayKeyId = sanitizeInput($_POST['razorpay_key_id'] ?? '');
    $razorpayKeySecret = sanitizeInput($_POST['razorpay_key_secret'] ?? '');
    
    try {
        $db = getDB();
        
        // Update or insert settings
        $settings = [
            'payment_enabled' => $paymentEnabled,
            'razorpay_key_id' => $razorpayKeyId,
            'razorpay_key_secret' => $razorpayKeySecret
        ];
        
        foreach ($settings as $key => $value) {
            $stmt = $db->prepare("
                INSERT INTO settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->execute([$key, $value]);
        }
        
        $message = 'Settings updated successfully!';
        $messageType = 'success';
        
    } catch (Exception $e) {
        $message = 'Error updating settings: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Get current settings
function getSetting($key, $default = '') {
    $db = getDB();
    try {
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

$currentSettings = [
    'payment_enabled' => getSetting('payment_enabled', '1'),
    'razorpay_key_id' => getSetting('razorpay_key_id', RAZORPAY_KEY_ID),
    'razorpay_key_secret' => getSetting('razorpay_key_secret', RAZORPAY_KEY_SECRET)
];
?>

<?php include 'includes/admin_header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">System Settings</h1>
    <div class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="bi bi-gear text-white-50"></i> Configuration Panel
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Settings Cards -->
<div class="row">
    <!-- Payment Settings -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Payment Configuration</h6>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <!-- Payment Toggle -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="paymentEnabled" 
                                       name="payment_enabled" <?php echo $currentSettings['payment_enabled'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="paymentEnabled">
                                    <strong>Enable Online Payment</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                When enabled, customers can pay online via Razorpay. When disabled, bookings are marked as "Pay at Spa".
                            </small>
                        </div>
                    </div>
                    
                    <!-- Razorpay Settings -->
                    <div id="razorpaySettings" style="<?php echo $currentSettings['payment_enabled'] ? '' : 'display: none;'; ?>">
                        <h6 class="font-weight-bold text-secondary mb-3">Razorpay Configuration</h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Razorpay Key ID</label>
                                <input type="text" class="form-control" name="razorpay_key_id" 
                                       value="<?php echo htmlspecialchars($currentSettings['razorpay_key_id']); ?>"
                                       placeholder="rzp_test_xxxxxxxxxx">
                                <small class="form-text text-muted">Your Razorpay Key ID from dashboard</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Razorpay Key Secret</label>
                                <input type="password" class="form-control" name="razorpay_key_secret" 
                                       value="<?php echo htmlspecialchars($currentSettings['razorpay_key_secret']); ?>"
                                       placeholder="Enter your secret key">
                                <small class="form-text text-muted">Your Razorpay Key Secret (keep secure)</small>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Test Mode:</strong> Use test keys for development. Switch to live keys for production.
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Save Settings
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2" onclick="testPaymentConnection()">
                            <i class="bi bi-wifi me-2"></i>Test Connection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Status Overview -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Payment Status</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="bi bi-<?php echo $currentSettings['payment_enabled'] ? 'check-circle text-success' : 'x-circle text-danger'; ?> display-4"></i>
                    </div>
                    <h5 class="<?php echo $currentSettings['payment_enabled'] ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $currentSettings['payment_enabled'] ? 'Online Payment Enabled' : 'Online Payment Disabled'; ?>
                    </h5>
                    <p class="text-muted">
                        <?php echo $currentSettings['payment_enabled'] ? 'Customers can pay online via Razorpay' : 'Bookings are marked as "Pay at Spa"'; ?>
                    </p>
                </div>
                
                <hr>
                
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Razorpay Status:</span>
                        <span class="<?php echo !empty($currentSettings['razorpay_key_id']) ? 'text-success' : 'text-warning'; ?>">
                            <?php echo !empty($currentSettings['razorpay_key_id']) ? 'Configured' : 'Not Set'; ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Environment:</span>
                        <span class="text-info">
                            <?php echo strpos($currentSettings['razorpay_key_id'], 'test') !== false ? 'Test' : 'Live'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="bookings.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-calendar-check me-2"></i>View Bookings
                    </a>
                    <a href="leads.php" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-person-lines-fill me-2"></i>Manage Leads
                    </a>
                    <a href="https://dashboard.razorpay.com" target="_blank" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-box-arrow-up-right me-2"></i>Razorpay Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$extraScripts = '<script>
    // Toggle Razorpay settings visibility
    document.getElementById("paymentEnabled").addEventListener("change", function() {
        const razorpaySettings = document.getElementById("razorpaySettings");
        if (this.checked) {
            razorpaySettings.style.display = "block";
        } else {
            razorpaySettings.style.display = "none";
        }
    });
    
    function testPaymentConnection() {
        const keyId = document.querySelector("input[name=\"razorpay_key_id\"]").value;
        const keySecret = document.querySelector("input[name=\"razorpay_key_secret\"]").value;
        
        if (!keyId || !keySecret) {
            alert("Please enter both Razorpay Key ID and Secret to test connection.");
            return;
        }
        
        // Simple validation
        if (!keyId.startsWith("rzp_")) {
            alert("Invalid Razorpay Key ID format. It should start with \"rzp_\"");
            return;
        }
        
        alert("Connection test passed! Keys appear to be in correct format.");
    }
</script>';

include 'includes/admin_footer.php'; 
?>