<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = 'About Us';
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section-inner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">About Serenity Spa</h1>
                <p class="lead mb-4">Your sanctuary for wellness, relaxation, and rejuvenation. Discover our story and commitment to your well-being.</p>
            </div>
        </div>
    </div>
</section>

<!-- Our Story Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2 class="fw-bold mb-4">Our Story</h2>
                <p class="mb-4">Founded in 2019, Serenity Spa was born from a passion for holistic wellness and the belief that everyone deserves a peaceful escape from life's daily stresses. What started as a small wellness center has grown into a premier destination for therapeutic treatments and relaxation.</p>
                <p class="mb-4">Our journey began when our founder, inspired by ancient healing traditions and modern wellness practices, envisioned a space where mind, body, and spirit could find perfect harmony. Today, we continue to honor that vision by providing exceptional spa services in a tranquil, nurturing environment.</p>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-primary fw-bold">500+</h4>
                            <p class="text-muted mb-0">Happy Clients</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-primary fw-bold">5+</h4>
                            <p class="text-muted mb-0">Years Experience</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="https://images.pexels.com/photos/3757942/pexels-photo-3757942.jpeg?auto=compress&cs=tinysrgb&w=600" 
                     class="img-fluid rounded-xl shadow-lg" alt="Spa Interior">
            </div>
        </div>
    </div>
</section>

<!-- Our Mission Section -->
<section class="py-5 services-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="text-center">
                    <div class="service-icon mb-4">
                        <i class="bi bi-heart"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Our Mission</h4>
                    <p class="text-muted">To provide a sanctuary where guests can escape, relax, and rejuvenate through personalized wellness experiences that nurture the mind, body, and spirit.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="text-center">
                    <div class="service-icon mb-4">
                        <i class="bi bi-eye"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Our Vision</h4>
                    <p class="text-muted">To be the leading wellness destination, recognized for our exceptional service, innovative treatments, and commitment to holistic health and well-being.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="text-center">
                    <div class="service-icon mb-4">
                        <i class="bi bi-star"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Our Values</h4>
                    <p class="text-muted">Excellence, integrity, compassion, and respect guide everything we do. We believe in creating meaningful connections and lasting positive impacts on our guests' lives.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Team Section -->
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="section-title display-5 fw-bold">Meet Our Expert Team</h2>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="team-card">
                    <img src="https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=300" 
                         class="team-image" alt="Team Member">
                    <div class="team-content">
                        <h5 class="fw-bold">Dr. Sarah Johnson</h5>
                        <p class="text-primary mb-2">Spa Director & Wellness Expert</p>
                        <p class="text-muted small">With over 15 years of experience in holistic wellness, Dr. Johnson leads our team with passion and expertise in therapeutic treatments.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="team-card">
                    <img src="https://images.pexels.com/photos/1239291/pexels-photo-1239291.jpeg?auto=compress&cs=tinysrgb&w=300" 
                         class="team-image" alt="Team Member">
                    <div class="team-content">
                        <h5 class="fw-bold">Michael Chen</h5>
                        <p class="text-primary mb-2">Senior Massage Therapist</p>
                        <p class="text-muted small">Certified in multiple massage techniques, Michael specializes in deep tissue therapy and sports massage with 10+ years of experience.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="team-card">
                    <img src="https://images.pexels.com/photos/1181686/pexels-photo-1181686.jpeg?auto=compress&cs=tinysrgb&w=300" 
                         class="team-image" alt="Team Member">
                    <div class="team-content">
                        <h5 class="fw-bold">Emma Davis</h5>
                        <p class="text-primary mb-2">Aromatherapy Specialist</p>
                        <p class="text-muted small">Emma brings expertise in essential oils and aromatherapy, creating personalized treatments that promote emotional and physical well-being.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5 services-section">
    <div class="container">
        <h2 class="section-title display-5 fw-bold">Why Choose Serenity Spa</h2>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-award"></i>
                    </div>
                    <h5>Certified Professionals</h5>
                    <p>All our therapists are certified and continuously trained in the latest wellness techniques.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5>Premium Quality</h5>
                    <p>We use only the finest organic products and state-of-the-art equipment for all treatments.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-clock"></i>
                    </div>
                    <h5>Flexible Scheduling</h5>
                    <p>Book appointments at your convenience with our flexible scheduling and extended hours.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-heart"></i>
                    </div>
                    <h5>Personalized Care</h5>
                    <p>Every treatment is customized to your unique needs and wellness goals.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="cta-section">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="fw-bold mb-3">Ready to Begin Your Wellness Journey?</h3>
                    <p class="lead mb-0">Experience the difference at Serenity Spa. Book your appointment today and discover true relaxation.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="models.php" class="btn btn-primary btn-lg me-3">
                        <i class="bi bi-calendar-check me-2"></i>Book Now
                    </a>
                    <a href="contact.php" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-envelope me-2"></i>Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>