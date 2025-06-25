<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid therapist ID']);
    exit;
}

$therapistId = (int)$_GET['id'];

try {
    $therapist = getTherapistById($therapistId);
    
    if (!$therapist) {
        echo json_encode(['success' => false, 'message' => 'Therapist not found']);
        exit;
    }
    
    $images = getTherapistImages($therapistId);
    $services = getTherapistServices($therapistId);
    
    // Format image paths
    foreach ($images as &$image) {
        $image['image_path'] = UPLOAD_URL . $image['image_path'];
    }
    
    echo json_encode([
        'success' => true,
        'therapist' => $therapist,
        'images' => $images,
        'services' => $services
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>