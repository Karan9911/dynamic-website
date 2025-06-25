<footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="mb-4">
                        <i class="bi bi-flower1 me-2"></i>Serenity Spa
                    </h5>
                    <p class="text-muted mb-4">Experience tranquility and rejuvenation with our professional spa services and expert therapists. Your wellness journey begins here.</p>
                    <div class="social-links">
                        <a href="#" class="me-2"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="me-2"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="me-2"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="me-2"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="#therapists" class="text-decoration-none">Therapists</a></li>
                        <li class="mb-2"><a href="#services" class="text-decoration-none">Services</a></li>
                        <li class="mb-2"><a href="#contact" class="text-decoration-none">Contact</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/admin/login.php" class="text-decoration-none">Admin</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h6 class="mb-3">Our Services</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-decoration-none">Swedish Massage</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none">Deep Tissue Therapy</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none">Hot Stone Treatment</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none">Aromatherapy</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none">Reflexology</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h6 class="mb-3">Contact Info</h6>
                    <div class="mb-3">
                        <i class="bi bi-geo-alt me-2 text-primary"></i>
                        <span class="text-muted">123 Wellness Street, Spa City, SC 12345</span>
                    </div>
                    <div class="mb-3">
                        <i class="bi bi-telephone me-2 text-primary"></i>
                        <span class="text-muted">+91 98765 43210</span>
                    </div>
                    <div class="mb-3">
                        <i class="bi bi-envelope me-2 text-primary"></i>
                        <span class="text-muted">info@serenityspa.com</span>
                    </div>
                    <div class="mb-3">
                        <i class="bi bi-clock me-2 text-primary"></i>
                        <span class="text-muted">Mon-Sun: 9 AM - 8 PM</span>
                    </div>
                </div>
            </div>
            <hr class="my-4 border-secondary">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> Serenity Spa. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        <a href="#" class="text-decoration-none me-3">Privacy Policy</a>
                        <a href="#" class="text-decoration-none me-3">Terms of Service</a>
                        <a href="#" class="text-decoration-none">Sitemap</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>