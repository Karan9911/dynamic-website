<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdminLogin();

$pageTitle = 'Manage Services';
$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $name = sanitizeInput($_POST['name'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        
        if (empty($name)) {
            $message = 'Service name is required';
            $messageType = 'danger';
        } else {
            $db = getDB();
            
            try {
                if ($action === 'add') {
                    $stmt = $db->prepare("INSERT INTO services (name, description) VALUES (?, ?)");
                    $stmt->execute([$name, $description]);
                    $message = 'Service added successfully!';
                } else {
                    $serviceId = (int)$_POST['service_id'];
                    $stmt = $db->prepare("UPDATE services SET name = ?, description = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $serviceId]);
                    $message = 'Service updated successfully!';
                }
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'delete') {
        $serviceId = (int)$_POST['service_id'];
        
        $db = getDB();
        try {
            $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$serviceId]);
            $message = 'Service deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error deleting service: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

$services = getAllServices();
?>

<?php include 'includes/admin_header.php'; ?>

<div class="admin-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Manage Services</h2>
            <p class="text-muted mb-0">Add, edit, and manage spa services</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#serviceModal">
            <i class="bi bi-plus-lg me-2"></i>Add New Service
        </button>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
            <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($services)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-gear display-4 text-muted"></i>
                    <h5 class="text-muted mt-3">No services found</h5>
                    <p class="text-muted">Click "Add New Service" to get started.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($services as $service): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($service['name']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($service['description']); ?></p>
                                    <small class="text-muted">Created: <?php echo timeAgo($service['created_at']); ?></small>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="btn-group w-100">
                                        <button class="btn btn-outline-primary btn-sm" onclick="editService(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['name']); ?>', '<?php echo htmlspecialchars($service['description']); ?>')">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="deleteService(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['name']); ?>')">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="serviceModalTitle">Add New Service</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="serviceForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="service_id" id="serviceId">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Service Name *</label>
                        <input type="text" class="form-control" name="name" id="serviceName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" name="description" id="serviceDescription" rows="4" placeholder="Describe the service..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Save Service
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="bi bi-exclamation-triangle display-4 text-danger mb-3"></i>
                    <p>Are you sure you want to delete <strong id="deleteServiceName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form style="display: inline;" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="service_id" id="deleteServiceId">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
$extraScripts = '<script>
    function editService(id, name, description) {
        document.getElementById("serviceModalTitle").textContent = "Edit Service";
        document.getElementById("formAction").value = "edit";
        document.getElementById("serviceId").value = id;
        document.getElementById("serviceName").value = name;
        document.getElementById("serviceDescription").value = description;
        new bootstrap.Modal(document.getElementById("serviceModal")).show();
    }
    
    function deleteService(id, name) {
        document.getElementById("deleteServiceId").value = id;
        document.getElementById("deleteServiceName").textContent = name;
        new bootstrap.Modal(document.getElementById("deleteModal")).show();
    }
    
    // Reset form when modal is closed
    document.getElementById("serviceModal").addEventListener("hidden.bs.modal", function() {
        document.getElementById("serviceForm").reset();
        document.getElementById("serviceModalTitle").textContent = "Add New Service";
        document.getElementById("formAction").value = "add";
        document.getElementById("serviceId").value = "";
    });
</script>';

include 'includes/admin_footer.php'; 
?>