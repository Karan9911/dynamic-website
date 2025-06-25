<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdminLogin();

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid service ID']);
    exit;
}

$serviceId = (int)$_GET['id'];

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();
    
    if (!$service) {
        echo json_encode(['success' => false, 'message' => 'Service not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'service' => $service
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>