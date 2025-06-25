<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate required fields
$required_fields = ['full_name', 'email', 'phone'];
$errors = [];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Sanitize and validate inputs
$therapist_id = !empty($_POST['therapist_id']) ? (int)$_POST['therapist_id'] : null;
$full_name = sanitizeInput($_POST['full_name']);
$email = sanitizeInput($_POST['email']);
$phone = sanitizeInput($_POST['phone']);
$message = sanitizeInput($_POST['message'] ?? '');
$preferred_date = sanitizeInput($_POST['preferred_date'] ?? '');

// Validate email
if (!validateEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate phone
if (!validatePhone($phone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
    exit;
}

// Add preferred date to message if provided
if ($preferred_date) {
    $message .= "\n\nPreferred Date: " . $preferred_date;
}

try {
    // Create inquiry
    $inquiryData = [
        'therapist_id' => $therapist_id,
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'message' => $message
    ];
    
    $result = createInquiry($inquiryData);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true, 
            'message' => 'Your inquiry has been sent successfully! We will contact you soon.',
            'inquiry_id' => $result['lead_id']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => $result['message']]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your inquiry']);
}
?>