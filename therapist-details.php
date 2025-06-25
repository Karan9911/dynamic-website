<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: models.php');
    exit;
}

$therapistId = (int)$_GET['id'];
$therapist = getTherapistById($therapistId);

if (!$therapist) {
    header('Location: models.php');
    exit;
}

$images = getTherapistImages($therapistId);
$services = getTherapistServices($therapistId);
$pageTitle = $therapist['name'] . ' - Therapist Details';
?>

<?php include 'includes/header.php'; ?>

<!-- Therapist Details -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Image Gallery -->
            <div class="col-lg-6">
                <div class="therapist-gallery">
                    <?php if (!empty($images)): ?>
                        <div class="main-image-container">
                            <img src="<?php echo UPLOAD_URL . $images[0]['image_path']; ?>" 
                                 id="mainImage" class="main-image" alt="<?php echo htmlspecialchars($therapist['name']); ?>">
                        </div>
                        
                        <?php if (count($images) > 1): ?>
                            <div class="thumbnail-gallery">
                                <?php foreach ($images as $index => $image): ?>
                                    <img src="<?php echo UPLOAD_URL . $image['image_path']; ?>" 
                                         class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                                         onclick="changeMainImage(this.src)"
                                         alt="<?php echo htmlspecialchars($therapist['name']); ?>">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="main-image-container">
                            <img src="https://images.pexels.com/photos/3757942/pexels-photo-3757942.jpeg?auto=compress&cs=tinysrgb&w=600" 
                                 class="main-image" alt="<?php echo htmlspecialchars($therapist['name']); ?>">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Therapist Info -->
            <div class="col-lg-6">
                <div class="therapist-info">
                    <h1 class="therapist-title"><?php echo htmlspecialchars($therapist['name']); ?></h1>
                    
                    <div class="price-section">
                        <span class="price-amount"><?php echo formatPrice($therapist['price_per_session']); ?></span>
                        <span class="price-label">per session</span>
                    </div>
                    
                    <div class="therapist-details">
                        <?php if ($therapist['height'] || $therapist['weight']): ?>
                            <div class="detail-row">
                                <?php if ($therapist['height']): ?>
                                    <div class="detail-item">
                                        <strong>Height:</strong> <?php echo htmlspecialchars($therapist['height']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($therapist['weight']): ?>
                                    <div class="detail-item">
                                        <strong>Weight:</strong> <?php echo htmlspecialchars($therapist['weight']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="specializations">
                            <h6>Specializations:</h6>
                            <div class="services-list">
                                <?php foreach ($services as $service): ?>
                                    <span class="service-badge"><?php echo htmlspecialchars($service['name']); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="description">
                            <h6>About:</h6>
                            <p><?php echo htmlspecialchars($therapist['description'] ?? 'Professional therapist dedicated to providing exceptional wellness and relaxation services.'); ?></p>
                        </div>
                        
                        <div class="availability">
                            <h6>Availability:</h6>
                            <p><?php echo htmlspecialchars($therapist['availability_slots'] ?? 'Mon-Fri: 9 AM - 6 PM, Sat: 10 AM - 4 PM'); ?></p>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button class="btn btn-primary btn-lg" onclick="openBookingModal(<?php echo $therapist['id']; ?>)">
                            <i class="bi bi-calendar-check me-2"></i>Book Appointment
                        </button>
                        <button class="btn btn-success" onclick="openWhatsAppChat('<?php echo htmlspecialchars($therapist['name']); ?>')">
                            <i class="bi bi-whatsapp me-2"></i>WhatsApp
                        </button>
                        <button class="btn btn-info" onclick="window.location.href='tel:+919876543210'">
                            <i class="bi bi-telephone me-2"></i>Call Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/booking_modal.php'; ?>
<?php include 'includes/footer.php'; ?>