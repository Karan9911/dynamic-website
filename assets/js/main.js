// Main JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize form validation
    initializeFormValidation();
    
    // Initialize image sliders
    initializeImageSliders();
    
    // Initialize booking forms
    initializeBookingForms();
    
    // Initialize smooth scrolling
    initializeSmoothScrolling();
    
    // Initialize animations
    initializeAnimations();
});

// Image slider functionality
function initializeImageSliders() {
    const sliders = document.querySelectorAll('.therapist-slider');
    
    sliders.forEach(slider => {
        const slides = slider.querySelectorAll('.slide');
        const dots = slider.querySelectorAll('.dot');
        let currentSlide = 0;
        
        // Auto-slide functionality
        if (slides.length > 1) {
            setInterval(() => {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(slider, currentSlide);
            }, 5000);
        }
    });
}

function changeSlide(therapistId, direction) {
    const slider = document.getElementById(`slider-${therapistId}`);
    const slides = slider.querySelectorAll('.slide');
    const dots = slider.querySelectorAll('.dot');
    
    let currentSlide = Array.from(slides).findIndex(slide => slide.classList.contains('active'));
    currentSlide += direction;
    
    if (currentSlide >= slides.length) currentSlide = 0;
    if (currentSlide < 0) currentSlide = slides.length - 1;
    
    showSlide(slider, currentSlide);
}

function currentSlide(therapistId, slideIndex) {
    const slider = document.getElementById(`slider-${therapistId}`);
    showSlide(slider, slideIndex - 1);
}

function showSlide(slider, slideIndex) {
    const slides = slider.querySelectorAll('.slide');
    const dots = slider.querySelectorAll('.dot');
    
    slides.forEach((slide, index) => {
        slide.classList.toggle('active', index === slideIndex);
    });
    
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === slideIndex);
    });
}

// Therapist details page gallery
function changeMainImage(src) {
    const mainImage = document.getElementById('mainImage');
    const thumbnails = document.querySelectorAll('.thumbnail');
    
    if (mainImage) {
        mainImage.style.opacity = '0.5';
        setTimeout(() => {
            mainImage.src = src;
            mainImage.style.opacity = '1';
        }, 150);
    }
    
    thumbnails.forEach(thumb => {
        thumb.classList.toggle('active', thumb.src === src);
    });
}

// Form validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

// Booking form functionality
function initializeBookingForms() {
    // Inquiry form
    const inquiryForm = document.getElementById('inquiryForm');
    if (inquiryForm) {
        inquiryForm.addEventListener('submit', handleInquirySubmission);
    }
    
    // Booking form
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', handleBookingSubmission);
    }
}

function handleInquirySubmission(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    // Show loading state
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('process_inquiry.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Your inquiry has been sent successfully! We will contact you soon.');
            form.reset();
            form.classList.remove('was-validated');
            
            // Close modal after 2 seconds
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('bookingModal'));
                if (modal) modal.hide();
            }, 2000);
        } else {
            showAlert('danger', data.message || 'Failed to send inquiry. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function handleBookingSubmission(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    // Show loading state
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    const amount = parseFloat(formData.get('total_amount'));
    
    // Process booking based on payment settings
    fetch('process_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.payment_required && window.PAYMENT_ENABLED) {
                // Payment is required - create Razorpay order
                return fetch('create_razorpay_order.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(paymentData => {
                    if (paymentData.success) {
                        // Initialize Razorpay payment
                        const options = {
                            key: paymentData.razorpay_key,
                            amount: paymentData.order.amount,
                            currency: paymentData.order.currency,
                            name: 'Serenity Spa',
                            description: 'Spa Session Booking',
                            order_id: paymentData.order.id,
                            handler: function(response) {
                                // Payment successful, verify and complete booking
                                verifyPaymentAndCompleteBooking(response, formData);
                            },
                            prefill: {
                                name: formData.get('full_name'),
                                email: formData.get('email'),
                                contact: formData.get('phone')
                            },
                            theme: {
                                color: '#4e73df'
                            }
                        };
                        
                        const rzp = new Razorpay(options);
                        rzp.open();
                    } else {
                        showAlert('danger', paymentData.message || 'Failed to initialize payment.');
                    }
                });
            } else {
                // No payment required - booking completed
                showAlert('success', data.message);
                form.reset();
                form.classList.remove('was-validated');
                
                // Close modal after 2 seconds
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('bookingModal'));
                    if (modal) modal.hide();
                }, 2000);
            }
        } else {
            showAlert('danger', data.message || 'Booking failed. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function verifyPaymentAndCompleteBooking(paymentResponse, formData) {
    // Add payment details to form data
    formData.append('payment_id', paymentResponse.razorpay_payment_id);
    formData.append('order_id', paymentResponse.razorpay_order_id);
    formData.append('signature', paymentResponse.razorpay_signature);
    
    fetch('verify_payment_and_book.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Payment successful! Your booking is confirmed.');
            document.getElementById('bookingForm').reset();
            
            // Close modal after 2 seconds
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('bookingModal'));
                if (modal) modal.hide();
            }, 2000);
        } else {
            showAlert('danger', 'Payment verification failed. Please contact support.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Payment verification failed. Please contact support.');
    });
}

// Booking modal functions
function openBookingModal(therapistId) {
    // Set therapist ID in all forms
    const inquiryTherapistId = document.getElementById('inquiryTherapistId');
    const bookingTherapistId = document.getElementById('bookingTherapistId');
    
    if (inquiryTherapistId) inquiryTherapistId.value = therapistId;
    if (bookingTherapistId) bookingTherapistId.value = therapistId;
    
    // Fetch therapist details for pricing
    fetch(`get_therapist_details.php?id=${therapistId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const bookingAmount = document.getElementById('bookingAmount');
                const displayAmount = document.getElementById('displayAmount');
                
                if (bookingAmount) {
                    bookingAmount.value = data.therapist.price_per_session;
                }
                
                if (displayAmount) {
                    displayAmount.textContent = `₹${data.therapist.price_per_session}`;
                }
                
                // Update WhatsApp buttons
                updateWhatsAppButtons(data.therapist);
            }
        })
        .catch(error => console.error('Error fetching therapist details:', error));
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
    modal.show();
}

function updateWhatsAppButtons(therapist) {
    const generalBtn = document.getElementById('whatsappGeneralBtn');
    const bookingBtn = document.getElementById('whatsappBookingBtn');
    
    if (generalBtn) {
        generalBtn.onclick = () => openWhatsAppChat(therapist.name, 'general');
    }
    
    if (bookingBtn) {
        bookingBtn.onclick = () => openWhatsAppChat(therapist.name, 'booking');
    }
}

function openWhatsAppChat(therapistName, type = 'general') {
    let message = '';
    
    if (type === 'booking') {
        message = `Hi! I'm interested in booking a session with ${therapistName}. Could you please provide more information about availability and pricing?`;
    } else {
        message = `Hi! I have some questions about ${therapistName}'s services. Could you please help me?`;
    }
    
    const whatsappUrl = `https://wa.me/919876543210?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
}

// Smooth scrolling for anchor links
function initializeSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Initialize animations
function initializeAnimations() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, observerOptions);
    
    // Observe elements with animation classes
    document.querySelectorAll('.slide-up, .fade-in, .scale-in').forEach(el => {
        observer.observe(el);
    });
}

// Utility functions
function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer') || createAlertContainer();
    
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertContainer.innerHTML = alertHTML;
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alertContainer';
    container.className = 'position-fixed top-0 end-0 p-3';
    container.style.zIndex = '10000';
    document.body.appendChild(container);
    return container;
}

// Export functions for global access
window.changeSlide = changeSlide;
window.currentSlide = currentSlide;
window.changeMainImage = changeMainImage;
window.openBookingModal = openBookingModal;
window.openWhatsAppChat = openWhatsAppChat;
window.showAlert = showAlert;