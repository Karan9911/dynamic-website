<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdminLogin();

$pageTitle = 'Lead Management';
$message = '';
$messageType = '';

// Handle lead status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $leadId = (int)$_POST['lead_id'];
    
    if ($action === 'update_status') {
        $status = $_POST['status'];
        $notes = sanitizeInput($_POST['notes'] ?? '');
        
        $db = getDB();
        try {
            $stmt = $db->prepare("UPDATE leads SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
            if ($stmt->execute([$status, $notes, $leadId])) {
                $message = 'Lead status updated successfully!';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = 'Error updating lead status.';
            $messageType = 'danger';
        }
    } elseif ($action === 'delete') {
        $db = getDB();
        try {
            $stmt = $db->prepare("DELETE FROM leads WHERE id = ?");
            if ($stmt->execute([$leadId])) {
                $message = 'Lead deleted successfully!';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = 'Error deleting lead.';
            $messageType = 'danger';
        }
    }
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? 'all';
$typeFilter = $_GET['type'] ?? 'all';
$dateFilter = $_GET['date'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

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
} elseif ($dateFrom && $dateTo) {
    $whereConditions[] = "DATE(created_at) BETWEEN ? AND ?";
    $params[] = $dateFrom;
    $params[] = $dateTo;
} elseif ($dateFrom) {
    $whereConditions[] = "DATE(created_at) >= ?";
    $params[] = $dateFrom;
} elseif ($dateTo) {
    $whereConditions[] = "DATE(created_at) <= ?";
    $params[] = $dateTo;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

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

// Get statistics
$stats = [
    'total' => 0,
    'new' => 0,
    'follow_up' => 0,
    'converted' => 0,
    'closed' => 0
];

$statsStmt = $db->prepare("
    SELECT status, COUNT(*) as count 
    FROM leads 
    GROUP BY status
");
$statsStmt->execute();
$statsResults = $statsStmt->fetchAll();

foreach ($statsResults as $stat) {
    $stats['total'] += $stat['count'];
    $stats[$stat['status']] = $stat['count'];
}
?>

<?php include 'includes/admin_header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Lead Management</h1>
    <div class="d-flex gap-2">
        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" onclick="exportLeads()">
            <i class="bi bi-download text-white-50"></i> Export
        </button>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-2 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Leads</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-person-lines-fill fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">New</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['new']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Follow-up</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['follow_up']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Converted</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['converted']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-6 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Closed</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['closed']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-x-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter Leads</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="new" <?php echo $statusFilter === 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="follow_up" <?php echo $statusFilter === 'follow_up' ? 'selected' : ''; ?>>Follow-up</option>
                    <option value="converted" <?php echo $statusFilter === 'converted' ? 'selected' : ''; ?>>Converted</option>
                    <option value="closed" <?php echo $statusFilter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select class="form-select" name="type">
                    <option value="all" <?php echo $typeFilter === 'all' ? 'selected' : ''; ?>>All Types</option>
                    <option value="inquiry" <?php echo $typeFilter === 'inquiry' ? 'selected' : ''; ?>>Inquiry</option>
                    <option value="booking" <?php echo $typeFilter === 'booking' ? 'selected' : ''; ?>>Booking</option>
                    <option value="whatsapp" <?php echo $typeFilter === 'whatsapp' ? 'selected' : ''; ?>>WhatsApp</option>
                    <option value="contact" <?php echo $typeFilter === 'contact' ? 'selected' : ''; ?>>Contact Form</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Single Date</label>
                <input type="date" class="form-control" name="date" value="<?php echo $dateFilter; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" name="date_from" value="<?php echo $dateFrom; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" name="date_to" value="<?php echo $dateTo; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-2"></i>Filter
                    </button>
                    <a href="leads.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Responsive Leads Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Leads List</h6>
    </div>
    <div class="card-body">
        <?php if (empty($leads)): ?>
            <div class="text-center py-5">
                <i class="bi bi-person-lines-fill display-4 text-gray-300"></i>
                <h5 class="text-gray-500 mt-3">No leads found</h5>
                <p class="text-gray-400">Leads will appear here when customers submit inquiries.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Therapist</th>
                            <th class="d-none d-md-table-cell">Message</th>
                            <th>Status</th>
                            <th class="d-none d-lg-table-cell">Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-light">#<?php echo $lead['id']; ?></span>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($lead['full_name']); ?></strong><br>
                                        <small class="text-muted">
                                            <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($lead['email']); ?><br>
                                            <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($lead['phone']); ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo match($lead['type']) {
                                            'booking' => 'primary',
                                            'inquiry' => 'info',
                                            'whatsapp' => 'success',
                                            'contact' => 'warning',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo ucfirst($lead['type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($lead['therapist_name'] ?? 'N/A'); ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <div class="message-preview">
                                        <?php echo htmlspecialchars(substr($lead['message'] ?? '', 0, 50)); ?>
                                        <?php if (strlen($lead['message'] ?? '') > 50) echo '...'; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo match($lead['status']) {
                                            'new' => 'danger',
                                            'follow_up' => 'warning',
                                            'converted' => 'success',
                                            'closed' => 'secondary',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $lead['status'])); ?>
                                    </span>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <small class="text-muted"><?php echo timeAgo($lead['created_at']); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewLead(<?php echo $lead['id']; ?>)" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success" onclick="updateLeadStatus(<?php echo $lead['id']; ?>)" title="Update Status">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteLead(<?php echo $lead['id']; ?>)" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Lead Details Modal -->
<div class="modal fade" id="leadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lead Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="leadModalBody">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Lead Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="lead_id" id="statusLeadId">
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="leadStatus">
                            <option value="new">New</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="converted">Converted</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Admin Notes</label>
                        <textarea class="form-control" name="notes" id="leadNotes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$extraScripts = '<script>
    function viewLead(id) {
        fetch("get_lead_details.php?id=" + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("leadModalBody").innerHTML = data.html;
                    new bootstrap.Modal(document.getElementById("leadModal")).show();
                } else {
                    alert("Error loading lead details: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Error loading lead details");
            });
    }
    
    function updateLeadStatus(id) {
        fetch("get_lead_details.php?id=" + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("statusLeadId").value = id;
                    document.getElementById("leadStatus").value = data.lead.status;
                    document.getElementById("leadNotes").value = data.lead.admin_notes || "";
                    new bootstrap.Modal(document.getElementById("statusModal")).show();
                } else {
                    alert("Error loading lead details: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Error loading lead details");
            });
    }
    
    function deleteLead(id) {
        if (confirm("Are you sure you want to delete this lead?")) {
            const form = document.createElement("form");
            form.method = "POST";
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="lead_id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    function exportLeads() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = "export_leads.php?" + params.toString();
    }
</script>';

include 'includes/admin_footer.php'; 
?>