<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireUserLogin();

$pageTitle = 'My Bookings';

// Get user's bookings
$db = getDB();
$stmt = $db->prepare("
    SELECT b.*, t.name as therapist_name 
    FROM bookings b 
    LEFT JOIN therapists t ON b.therapist_id = t.id 
    WHERE b.email = ? 
    ORDER BY b.created_at DESC
");
$stmt->execute([$_SESSION['user_email']]);
$bookings = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold">My Bookings</h2>
                        <p class="text-muted mb-0">View and manage your spa appointments</p>
                    </div>
                    <a href="models.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Book New Appointment
                    </a>
                </div>
                
                <?php if (empty($bookings)): ?>
                    <div class="text-center py-5">
                        <div class="auth-card">
                            <i class="bi bi-calendar-x display-4 text-muted mb-3"></i>
                            <h4 class="text-muted">No bookings found</h4>
                            <p class="text-muted mb-4">You haven't made any spa appointments yet.</p>
                            <a href="models.php" class="btn btn-primary">
                                <i class="bi bi-calendar-check me-2"></i>Book Your First Appointment
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold">Booking #<?php echo $booking['id']; ?></h6>
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
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <h5 class="text-primary"><?php echo htmlspecialchars($booking['therapist_name'] ?? 'N/A'); ?></h5>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-calendar me-2 text-muted"></i>
                                                    <div>
                                                        <small class="text-muted">Date</small><br>
                                                        <strong><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-clock me-2 text-muted"></i>
                                                    <div>
                                                        <small class="text-muted">Time</small><br>
                                                        <strong><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-currency-rupee me-2 text-muted"></i>
                                                    <div>
                                                        <small class="text-muted">Amount</small><br>
                                                        <strong class="text-success"><?php echo formatPrice($booking['total_amount']); ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-credit-card me-2 text-muted"></i>
                                                    <div>
                                                        <small class="text-muted">Payment</small><br>
                                                        <strong><?php echo $booking['payment_id'] ? 'Paid Online' : 'Pay at Spa'; ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <?php if ($booking['message']): ?>
                                                <div class="col-12">
                                                    <div class="bg-light p-3 rounded">
                                                        <small class="text-muted">Special Requests:</small><br>
                                                        <?php echo htmlspecialchars($booking['message']); ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="col-12">
                                                <small class="text-muted">
                                                    <i class="bi bi-clock-history me-1"></i>
                                                    Booked <?php echo timeAgo($booking['created_at']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <div class="d-flex gap-2">
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <button class="btn btn-outline-danger btn-sm" onclick="cancelBooking(<?php echo $booking['id']; ?>)">
                                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-outline-primary btn-sm" onclick="contactSupport(<?php echo $booking['id']; ?>)">
                                                <i class="bi bi-headset me-1"></i>Contact Support
                                            </button>
                                            
                                            <?php if ($booking['status'] === 'completed'): ?>
                                                <button class="btn btn-outline-success btn-sm" onclick="rebookAppointment(<?php echo $booking['therapist_id']; ?>)">
                                                    <i class="bi bi-arrow-repeat me-1"></i>Book Again
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php 
$extraScripts = '<script>
    function cancelBooking(bookingId) {
        if (confirm("Are you sure you want to cancel this booking?")) {
            // In a real application, you would send an AJAX request to cancel the booking
            alert("Booking cancellation request submitted. Our team will contact you shortly.");
        }
    }
    
    function contactSupport(bookingId) {
        const message = `Hi, I need help with my booking #${bookingId}. Please assist me.`;
        const whatsappUrl = `https://wa.me/919876543210?text=${encodeURIComponent(message)}`;
        window.open(whatsappUrl, "_blank");
    }
    
    function rebookAppointment(therapistId) {
        window.location.href = `therapist-details.php?id=${therapistId}`;
    }
</script>';

include 'includes/footer.php'; 
?>