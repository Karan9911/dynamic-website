<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdminLogin();

// Get filter parameters
$statusFilter = $_GET['status'] ?? 'all';
$typeFilter = $_GET['type'] ?? 'all';
$dateFilter = $_GET['date'] ?? '';

// Build query
$whereConditions = [];
$params = [];

if ($statusFilter !== 'all') {
    $whereConditions[] = "status = ?";
    $params[] = $statusFilter;
}

if ($typeFilter !== 'all') {
    $whereConditions[] = "type = ?";
    $params[] = $typeFilter;
}

if ($dateFilter) {
    $whereConditions[] = "DATE(created_at) = ?";
    $params[] = $dateFilter;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT l.*, t.name as therapist_name 
        FROM leads l 
        LEFT JOIN therapists t ON l.therapist_id = t.id 
        $whereClause 
        ORDER BY l.created_at DESC
    ");
    $stmt->execute($params);
    $leads = $stmt->fetchAll();
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="leads_export_' . date('Y-m-d_H-i-s') . '.csv"');
    
    // Create file pointer
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, [
        'ID',
        'Type',
        'Full Name',
        'Email',
        'Phone',
        'Therapist',
        'Message',
        'Status',
        'Admin Notes',
        'Created At',
        'Updated At'
    ]);
    
    // Add data rows
    foreach ($leads as $lead) {
        fputcsv($output, [
            $lead['id'],
            ucfirst($lead['type']),
            $lead['full_name'],
            $lead['email'],
            $lead['phone'],
            $lead['therapist_name'] ?? 'N/A',
            $lead['message'] ?? '',
            ucfirst(str_replace('_', ' ', $lead['status'])),
            $lead['admin_notes'] ?? '',
            $lead['created_at'],
            $lead['updated_at']
        ]);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    header('Content-Type: text/html');
    echo 'Error exporting leads: ' . $e->getMessage();
}
?>