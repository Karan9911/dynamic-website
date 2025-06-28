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

// Get all bookings
$bookings = getAllBookings();
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
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer Details</th>
                            <th>Therapist</th>
                            <th>Appointment</th>
                            <th>Amount</th>
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