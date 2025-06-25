<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

$filter = $_GET['filter'] ?? 'monthly';

$dateCondition = '';
switch ($filter) {
    case 'daily':
        $dateCondition = "DATE(created_at) = CURDATE()";
        break;
    case 'monthly':
        $dateCondition = "MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        break;
    case 'yearly':
        $dateCondition = "YEAR(created_at) = YEAR(CURRENT_DATE())";
        break;
}

try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT SUM(total_amount) as revenue, COUNT(*) as bookings 
        FROM bookings 
        WHERE $dateCondition 
        AND status IN ('confirmed', 'completed')
    ");
    $stmt->execute();
    $stats = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'revenue' => $stats['revenue'] ?? 0,
        'bookings' => $stats['bookings'] ?? 0
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching stats'
    ]);
}
?>