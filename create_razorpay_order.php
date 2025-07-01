<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isPaymentEnabled()) {
    echo json_encode(['success' => false, 'message' => 'Payment gateway is disabled']);
    exit;
}

// Validate required fields
$required_fields = ['therapist_id', 'full_name', 'email', 'phone', 'booking_date', 'booking_time', 'total_amount'];
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

$amount = (float)$_POST['total_amount'];

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid amount']);
    exit;
}

try {
    $result = createRazorpayOrder($amount);
    
    if ($result['success']) {
        $keyId = getSetting('razorpay_key_id', RAZORPAY_KEY_ID);
        
        echo json_encode([
            'success' => true,
            'order' => $result['order'],
            'razorpay_key' => $keyId
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => $result['message']]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to create payment order']);
}
?>