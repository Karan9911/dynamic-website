<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdminLogin();

$pageTitle = 'Manage Bookings';
$message = '';
$messageType = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $bookingId = (int)$_POST['booking_id'];
    
    if ($action === 'update_status') {
        $status = $_POST['status'];
        $validStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        
        if (in_array($status, $validStatuses)) {
            $db = getDB();
            $stmt = $db->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            if ($stmt->execute([$status, $bookingId])) {
                $message = 'Booking status updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to update booking status.';
                $messageType = 'danger';
            }
        }
    }
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? 'all';
$paymentFilter = $_GET['payment'] ?? 'all';

// Build query with filters
$whereConditions = [];
$params = [];

if ($statusFilter !== 'all') {
    $whereConditions[] = "b.status = ?";
    $params[] = $statusFilter;
}

if ($paymentFilter !== 'all') {
    $whereConditions[] = "b.payment_status = ?";
    $params[] = $paymentFilter;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get all bookings with filters
$db = getDB();
$stmt = $db->prepare("
    SELECT b.*, t.name as therapist_name 
    FROM bookings b 
    LEFT JOIN therapists t ON b.therapist_id = t.id 
    $whereClause
    ORDER BY b.created_at DESC
");
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>

<?php include 'includes/admin_header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Bookings</h1>
    <div class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="bi bi-calendar3 text-white-50"></i> Total: <?php echo count($bookings); ?> bookings
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter Bookings</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Payment Status</label>
                <select class="form-select" name="payment">
                    <option value="all" <?php echo $paymentFilter === 'all' ? 'selected' : ''; ?>>All Payment Types</option>
                    <option value="completed" <?php echo $paymentFilter === 'completed' ? 'selected' : ''; ?>>Paid Online</option>
                    <option value="cash" <?php echo $paymentFilter === 'cash' ? 'selected' : ''; ?>>Pay at Spa</option>
                    <option value="pending" <?php echo $paymentFilter === 'pending' ? 'selected' : ''; ?>>Payment Pending</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-2"></i>Filter
                    </button>
                    <a href="bookings.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Bookings List</h6>
    </div>
    <div class="card-body">
        <?php if (empty($bookings)): ?>
            <div class="text-center py-5">
                <i class="bi bi-calendar-x display-4 text-gray-300"></i>
                <h5 class="text-gray-500 mt-3">No bookings found</h5>
                <p class="text-gray-400">Bookings will appear here once customers start making appointments.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-container">
                <table class="table table-bordered admin-table" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer Details</th>
                            <th>Therapist</th>
                            <th>Appointment</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-light">#<?php echo $booking['id']; ?></span>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($booking['full_name']); ?></strong><br>
                                        <small class="text-muted">
                                            <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($booking['email']); ?><br>
                                            <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($booking['phone']); ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-weight-medium"><?php echo htmlspecialchars($booking['therapist_name'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <div>
                                        <i class="bi bi-calendar me-1"></i><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?><br>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i><?php echo date('g:i A', strtotime($booking['booking_time'])); ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-success"><?php echo formatPrice($booking['total_amount']); ?></span>
                                </td>
                                <td>
                                    <span class="badge payment-status-<?php echo $booking['payment_status']; ?>">
                                        <?php 
                                        echo match($booking['payment_status']) {
                                            'completed' => 'Paid Online',
                                            'cash' => 'Pay at Spa',
                                            'pending' => 'Payment Pending',
                                            'failed' => 'Payment Failed',
                                            default => ucfirst($booking['payment_status'])
                                        };
                                        ?>
                                    </span>
                                </td>
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
                                <td>
                                    <small class="text-muted"><?php echo timeAgo($booking['created_at']); ?></small>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-gear"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><h6 class="dropdown-header">Update Status</h6></li>
                                            <li>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="status" value="confirmed">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-check-circle text-success me-2"></i>Confirm
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-check-all text-info me-2"></i>Complete
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-x-circle text-danger me-2"></i>Cancel
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>