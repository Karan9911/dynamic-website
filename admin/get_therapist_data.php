<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdminLogin();

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
    
    $services = getTherapistServices($therapistId);
    
    echo json_encode([
        'success' => true,
        'therapist' => $therapist,
        'services' => $services
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>