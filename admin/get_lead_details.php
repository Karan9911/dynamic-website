<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdminLogin();

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid lead ID']);
    exit;
}

$leadId = (int)$_GET['id'];

try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT l.*, t.name as therapist_name 
        FROM leads l 
        LEFT JOIN therapists t ON l.therapist_id = t.id 
        WHERE l.id = ?
    ");
    $stmt->execute([$leadId]);
    $lead = $stmt->fetch();
    
    if (!$lead) {
        echo json_encode(['success' => false, 'message' => 'Lead not found']);
        exit;
    }
    
    // Generate HTML for modal
    $html = '
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold">Customer Information</h6>
                <p><strong>Name:</strong> ' . htmlspecialchars($lead['full_name']) . '</p>
                <p><strong>Email:</strong> ' . htmlspecialchars($lead['email']) . '</p>
                <p><strong>Phone:</strong> ' . htmlspecialchars($lead['phone']) . '</p>
                <p><strong>Type:</strong> <span class="badge bg-primary">' . ucfirst($lead['type']) . '</span></p>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold">Lead Details</h6>
                <p><strong>Status:</strong> <span class="badge bg-' . 
                    match($lead['status']) {
                        'new' => 'danger',
                        'follow_up' => 'warning',
                        'converted' => 'success',
                        'closed' => 'secondary',
                        default => 'secondary'
                    } . '">' . ucfirst(str_replace('_', ' ', $lead['status'])) . '</span></p>
                <p><strong>Created:</strong> ' . date('M j, Y g:i A', strtotime($lead['created_at'])) . '</p>
                <p><strong>Therapist:</strong> ' . ($lead['therapist_name'] ?? 'N/A') . '</p>
            </div>
            <div class="col-12 mt-3">
                <h6 class="fw-bold">Message</h6>
                <p class="bg-light p-3 rounded">' . htmlspecialchars($lead['message'] ?? 'No message provided') . '</p>
            </div>';
    
    if ($lead['admin_notes']) {
        $html .= '
            <div class="col-12 mt-3">
                <h6 class="fw-bold">Admin Notes</h6>
                <p class="bg-warning bg-opacity-10 p-3 rounded">' . htmlspecialchars($lead['admin_notes']) . '</p>
            </div>';
    }
    
    $html .= '</div>';
    
    echo json_encode([
        'success' => true,
        'lead' => $lead,
        'html' => $html
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>