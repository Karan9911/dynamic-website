<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = 'Therapy Treatments';
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section-inner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">Therapeutic Treatments</h1>
                <p class="lead mb-4">Explore our specialized therapy treatments designed to heal, restore, and rejuvenate your body and mind.</p>
            </div>
        </div>
    </div>
</section>

<!-- Therapy Categories -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-4">
            <!-- Massage Therapies -->
            <div class="col-lg-6">
                <div class="therapy-category">
                    <div class="category-header">
                        <div class="category-icon">
                            <i class="bi bi-heart-pulse"></i>
                        </div>
                        <h3>Massage Therapies</h3>
                    </div>
                    <div class="therapy-list">
                        <div class="therapy-item">
                            <h5>Swedish Massage</h5>
                            <p>Gentle, relaxing massage using long strokes and kneading techniques.</p>
                            <div class="therapy-benefits">
                                <span class="benefit-tag">Stress Relief</span>
                                <span class="benefit-tag">Muscle Relaxation</span>
                                <span class="benefit-tag">Improved Circulation</span>
                            </div>
                        </div>
                        <div class="therapy-item">
                            <h5>Deep Tissue Massage</h5>
                            <p>Intensive massage targeting deep muscle layers and chronic tension.</p>
                            <div class="therapy-benefits">
                                <span class="benefit-tag">Pain Relief</span>
                                <span class="benefit-tag">Muscle Recovery</span>
                                <span class="benefit-tag">Injury Rehabilitation</span>
                            </div>
                        </div>
                        <div class="therapy-item">
                            <h5>Hot Stone Therapy</h5>
                            <p>Heated stones placed on body to promote deep muscle relaxation.</p>
                            <div class="therapy-benefits">
                                <span class="benefit-tag">Deep Relaxation</span>
                                <span class="benefit-tag">Improved Blood Flow</span>
                                <span class="benefit-tag">Stress Reduction</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Holistic Therapies -->
            <div class="col-lg-6">
                <div class="therapy-category">
                    <div class="category-header">
                        <div class="category-icon">
                            <i class="bi bi-flower1"></i>
                        </div>
                        <h3>Holistic Therapies</h3>
                    </div>
                    <div class="therapy-list">
                        <div class="therapy-item">
                            <h5>Aromatherapy</h5>
                            <p>Essential oils massage for emotional and physical well-being.</p>
                            <div class="therapy-benefits">
                                <span class="benefit-tag">Mood Enhancement</span>
                                <span class="benefit-tag">Anxiety Relief</span>
                                <span class="benefit-tag">Better Sleep</span>
                            </div>
                        </div>
                        <div class="therapy-item">
                            <h5>Reflexology</h5>
                            <p>Pressure point massage focusing on feet, hands, and ears.</p>
                            <div class="therapy-benefits">
                                <span class="benefit-tag">Energy Balance</span>
                                <span class="benefit-tag">Organ Function</span>
                                <span class="benefit-tag">Overall Wellness</span>
                            </div>
                        </div>
                        <div class="therapy-item">
                            <h5>Thai Massage</h5>
                            <p>Traditional stretching and pressure point therapy.</p>
                            <div class="therapy-benefits">
                                <span class="benefit-tag">Flexibility</span>
                                <span class="benefit-tag">Energy Flow</span>
                                <span class="benefit-tag">Joint Mobility</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Treatment Process -->
<section class="py-5 services-section">
    <div class="container">
        <h2 class="section-title display-5 fw-bold">Our Treatment Process</h2>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="process-step">
                    <div class="step-number">1</div>
                    <h5>Consultation</h5>
                    <p>Initial assessment of your needs and health conditions.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="process-step">
                    <div class="step-number">2</div>
                    <h5>Customization</h5>
                    <p>Personalized treatment plan based on your requirements.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="process-step">
                    <div class="step-number">3</div>
                    <h5>Treatment</h5>
                    <p>Professional therapy session in a relaxing environment.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="process-step">
                    <div class="step-number">4</div>
                    <h5>Follow-up</h5>
                    <p>Post-treatment care and recommendations for wellness.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Book Treatment CTA -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="cta-section">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="fw-bold mb-3">Ready to Experience Our Therapeutic Treatments?</h3>
                    <p class="lead mb-0">Book your personalized therapy session with our expert therapists today.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="models.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-calendar-check me-2"></i>Book Treatment
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>