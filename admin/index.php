<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdminLogin();

$pageTitle = 'Admin Dashboard';

// Get filter parameters
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

<div class="admin-content">
    <!-- Welcome Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h2>
            <p class="text-muted mb-0">Here's what's happening with your spa today.</p>
        </div>
        <div class="text-end">
            <small class="text-muted">
                <i class="bi bi-calendar me-1"></i>
                <?php echo date('l, F j, Y'); ?>
            </small>
        </div>
    </div>
    
    <!-- Revenue Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Revenue Analytics</h5>
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
    
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-number text-primary"><?php echo $stats['total_therapists']; ?>+</div>
                        <div class="stat-label">Total Therapists</div>
                    </div>
                    <i class="bi bi-people display-4 text-primary opacity-25"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-number text-success"><?php echo $stats['bookings_filtered']; ?></div>
                        <div class="stat-label"><?php echo ucfirst($revenueFilter); ?> Bookings</div>
                    </div>
                    <i class="bi bi-calendar-check display-4 text-success opacity-25"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card stat-new">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-number text-warning"><?php echo $stats['new_leads']; ?></div>
                        <div class="stat-label">New Leads</div>
                    </div>
                    <i class="bi bi-person-lines-fill display-4 text-warning opacity-25"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-number text-info"><?php echo formatPrice($stats['revenue_filtered']); ?></div>
                        <div class="stat-label"><?php echo ucfirst($revenueFilter); ?> Revenue</div>
                    </div>
                    <i class="bi bi-currency-rupee display-4 text-info opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <a href="therapists.php" class="quick-action-card">
                <i class="bi bi-person-plus"></i>
                <span>Add Therapist</span>
            </a>
        </div>
        <div class="col-lg-3 col-md-6">
            <a href="bookings.php" class="quick-action-card">
                <i class="bi bi-calendar-check"></i>
                <span>View Bookings</span>
            </a>
        </div>
        <div class="col-lg-3 col-md-6">
            <a href="leads.php" class="quick-action-card">
                <i class="bi bi-person-lines-fill"></i>
                <span>Manage Leads</span>
            </a>
        </div>
        <div class="col-lg-3 col-md-6">
            <a href="services.php" class="quick-action-card">
                <i class="bi bi-gear"></i>
                <span>Manage Services</span>
            </a>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Recent Bookings -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-clock-history me-2"></i>Recent Bookings
                    </h5>
                    <a href="bookings.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-eye me-1"></i>View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_bookings)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x display-4 text-muted"></i>
                            <h6 class="text-muted mt-3">No bookings found</h6>
                            <p class="text-muted">Bookings will appear here once customers start making appointments.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
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
                                            <td><span class="fw-bold text-success"><?php echo formatPrice($booking['total_amount']); ?></span></td>
                                            <td>
                                                <span class="badge bg-<?php 
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
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-person-lines-fill me-2"></i>Recent Leads
                    </h5>
                    <a href="leads.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-eye me-1"></i>View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_leads)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-person-x display-4 text-muted"></i>
                            <h6 class="text-muted mt-3">No leads found</h6>
                            <p class="text-muted">Leads will appear here when customers submit inquiries.</p>
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
                                        <span class="badge bg-<?php 
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
</div>

<?php include 'includes/admin_footer.php'; ?>