<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdminLogin();

$pageTitle = 'Admin Dashboard';

// Get filter parameters for revenue block
$revenueFilter = $_GET['revenue_filter'] ?? 'monthly';

// Get statistics
$db = getDB();

try {
    $stats = [
        'total_therapists' => 0,
        'active_therapists' => 0,
        'total_bookings' => 0,
        'pending_bookings' => 0,
        'total_leads' => 0,
        'new_leads' => 0,
        'total_users' => 0,
        'revenue_filtered' => 0,
        'bookings_filtered' => 0
    ];
    
    // Get therapist counts
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM therapists");
    if ($stmt->execute()) {
        $result = $stmt->fetch();
        $stats['total_therapists'] = $result['count'];
    }
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM therapists WHERE status = 'active'");
    if ($stmt->execute()) {
        $result = $stmt->fetch();
        $stats['active_therapists'] = $result['count'];
    }
    
    // Get booking counts
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings");
    if ($stmt->execute()) {
        $result = $stmt->fetch();
        $stats['total_bookings'] = $result['count'];
    }
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
    if ($stmt->execute()) {
        $result = $stmt->fetch();
        $stats['pending_bookings'] = $result['count'];
    }
    
    // Get lead counts
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM leads");
    if ($stmt->execute()) {
        $result = $stmt->fetch();
        $stats['total_leads'] = $result['count'];
    }
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM leads WHERE status = 'new'");
    if ($stmt->execute()) {
        $result = $stmt->fetch();
        $stats['new_leads'] = $result['count'];
    }
    
    // Get user counts
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    if ($stmt->execute()) {
        $result = $stmt->fetch();
        $stats['total_users'] = $result['count'];
    }
    
    // Get filtered revenue and bookings
    $dateCondition = '';
    switch ($revenueFilter) {
        case 'daily':
            $dateCondition = "DATE(created_at) = CURDATE()";
            break;
        case 'monthly':
            $dateCondition = "MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
            break;
        case 'yearly':
            $dateCondition = "YEAR(created_at) = YEAR(CURRENT_DATE())";
            break;
    }
    
    $stmt = $db->prepare("
        SELECT SUM(total_amount) as revenue, COUNT(*) as bookings 
        FROM bookings 
        WHERE $dateCondition 
        AND status IN ('confirmed', 'completed')
    ");
    if ($stmt->execute()) {
        $result = $stmt->fetch();
        $stats['revenue_filtered'] = $result['revenue'] ?? 0;
        $stats['bookings_filtered'] = $result['bookings'] ?? 0;
    }
    
    // Get recent bookings
    $stmt = $db->prepare("
        SELECT b.*, t.name as therapist_name 
        FROM bookings b 
        LEFT JOIN therapists t ON b.therapist_id = t.id 
        ORDER BY b.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $recent_bookings = $stmt->fetchAll();
    
    // Get recent leads
    $stmt = $db->prepare("
        SELECT l.*, t.name as therapist_name 
        FROM leads l 
        LEFT JOIN therapists t ON l.therapist_id = t.id 
        ORDER BY l.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recent_leads = $stmt->fetchAll();
    
} catch (Exception $e) {
    $stats = [
        'total_therapists' => 0,
        'active_therapists' => 0,
        'total_bookings' => 0,
        'pending_bookings' => 0,
        'total_leads' => 0,
        'new_leads' => 0,
        'total_users' => 0,
        'revenue_filtered' => 0,
        'bookings_filtered' => 0
    ];
    $recent_bookings = [];
    $recent_leads = [];
}
?>

<?php include 'includes/admin_header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    <div class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="bi bi-calendar text-white-50"></i> <?php echo date('l, F j, Y'); ?>
    </div>
</div>

<!-- Revenue Filter -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0 font-weight-bold text-primary">Revenue Analytics</h5>
                <small class="text-muted">Filter revenue and booking data</small>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex gap-2">
                    <select name="revenue_filter" class="form-select" onchange="this.form.submit()">
                        <option value="daily" <?php echo $revenueFilter === 'daily' ? 'selected' : ''; ?>>Today</option>
                        <option value="monthly" <?php echo $revenueFilter === 'monthly' ? 'selected' : ''; ?>>This Month</option>
                        <option value="yearly" <?php echo $revenueFilter === 'yearly' ? 'selected' : ''; ?>>This Year</option>
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Total Therapists Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Therapists</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_therapists']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtered Bookings Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1" id="bookingsLabel"><?php echo ucfirst($revenueFilter); ?> Bookings</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="filteredBookings"><?php echo $stats['bookings_filtered']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Leads Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">New Leads</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['new_leads']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-person-lines-fill fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtered Revenue Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1" id="revenueLabel"><?php echo ucfirst($revenueFilter); ?> Revenue</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="filteredRevenue"><?php echo formatPrice($stats['revenue_filtered']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-currency-rupee fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Recent Bookings -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Bookings</h6>
                <a href="bookings.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="bi bi-eye text-white-50"></i> View All
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_bookings)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x display-4 text-gray-300"></i>
                        <h6 class="text-gray-500 mt-3">No bookings found</h6>
                        <p class="text-gray-400">Bookings will appear here once customers start making appointments.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Therapist</th>
                                    <th>Date & Time</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_bookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($booking['full_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($booking['therapist_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <div>
                                                <?php echo date('M j, Y', strtotime($booking['booking_date'])); ?><br>
                                                <small class="text-muted"><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></small>
                                            </div>
                                        </td>
                                        <td><span class="font-weight-bold text-success"><?php echo formatPrice($booking['total_amount']); ?></span></td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo match($booking['status']) {
                                                    'confirmed' => 'success',
                                                    'pending' => 'warning',
                                                    'cancelled' => 'danger',
                                                    'completed' => 'info',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Leads -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Leads</h6>
                <a href="leads.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="bi bi-eye text-white-50"></i> View All
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_leads)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-person-x display-4 text-gray-300"></i>
                        <h6 class="text-gray-500 mt-3">No leads found</h6>
                        <p class="text-gray-400">Leads will appear here when customers submit inquiries.</p>
                    </div>
                <?php else: ?>
                    <div class="lead-list">
                        <?php foreach ($recent_leads as $lead): ?>
                            <div class="lead-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($lead['full_name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($lead['email']); ?></small>
                                    </div>
                                    <span class="badge badge-<?php 
                                        echo match($lead['status']) {
                                            'new' => 'danger',
                                            'follow_up' => 'warning',
                                            'converted' => 'success',
                                            'closed' => 'secondary',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $lead['status'])); ?>
                                    </span>
                                </div>
                                <p class="mb-1 small"><?php echo htmlspecialchars(substr($lead['message'] ?? '', 0, 60)); ?>...</p>
                                <small class="text-muted"><?php echo timeAgo($lead['created_at']); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
$extraScripts = '<script>
    // Revenue filter functionality
    document.querySelectorAll("select[name=\"revenue_filter\"]").forEach(select => {
        select.addEventListener("change", function() {
            const filter = this.value;
            
            // Update labels
            document.getElementById("bookingsLabel").textContent = filter.charAt(0).toUpperCase() + filter.slice(1) + " Bookings";
            document.getElementById("revenueLabel").textContent = filter.charAt(0).toUpperCase() + filter.slice(1) + " Revenue";
            
            // Form will submit automatically due to onchange attribute
        });
    });
</script>';

include 'includes/admin_footer.php'; 
?>