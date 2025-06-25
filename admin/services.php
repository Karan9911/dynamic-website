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
        $iconType = $_POST['icon_type'] ?? 'bootstrap';
        $iconValue = sanitizeInput($_POST['icon_value'] ?? '');
        
        if (empty($name)) {
            $message = 'Service name is required';
            $messageType = 'danger';
        } else {
            $db = getDB();
            
            try {
                // Handle icon upload if image is selected
                $iconPath = null;
                if ($iconType === 'upload' && isset($_FILES['icon_image']) && $_FILES['icon_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = uploadImage($_FILES['icon_image'], 'services');
                    if ($uploadResult['success']) {
                        $iconPath = $uploadResult['path'];
                    }
                }
                
                if ($action === 'add') {
                    $stmt = $db->prepare("INSERT INTO services (name, description, icon_type, icon_value, icon_image) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $iconType, $iconValue, $iconPath]);
                    $message = 'Service added successfully!';
                } else {
                    $serviceId = (int)$_POST['service_id'];
                    
                    // If new image uploaded, delete old one
                    if ($iconPath) {
                        $oldService = $db->prepare("SELECT icon_image FROM services WHERE id = ?");
                        $oldService->execute([$serviceId]);
                        $old = $oldService->fetch();
                        if ($old && $old['icon_image']) {
                            deleteImage($old['icon_image']);
                        }
                    }
                    
                    $stmt = $db->prepare("UPDATE services SET name = ?, description = ?, icon_type = ?, icon_value = ?" . ($iconPath ? ", icon_image = ?" : "") . " WHERE id = ?");
                    $params = [$name, $description, $iconType, $iconValue];
                    if ($iconPath) $params[] = $iconPath;
                    $params[] = $serviceId;
                    $stmt->execute($params);
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
            // Get service to delete icon image
            $stmt = $db->prepare("SELECT icon_image FROM services WHERE id = ?");
            $stmt->execute([$serviceId]);
            $service = $stmt->fetch();
            
            if ($service && $service['icon_image']) {
                deleteImage($service['icon_image']);
            }
            
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

// Get all services with icon data
function getAllServicesWithIcons() {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM services ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

$services = getAllServicesWithIcons();
?>

<?php include 'includes/admin_header.php'; ?>

<div class="admin-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Manage Services</h2>
            <p class="text-muted mb-0">Add, edit, and manage spa services with icons</p>
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
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Icon</th>
                                <th>Service Name</th>
                                <th>Description</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td>
                                        <div class="service-icon-preview">
                                            <?php if ($service['icon_type'] === 'upload' && $service['icon_image']): ?>
                                                <img src="<?php echo UPLOAD_URL . $service['icon_image']; ?>" 
                                                     alt="<?php echo htmlspecialchars($service['name']); ?>" 
                                                     style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;">
                                            <?php elseif ($service['icon_type'] === 'bootstrap' && $service['icon_value']): ?>
                                                <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="bi <?php echo htmlspecialchars($service['icon_value']); ?>"></i>
                                                </div>
                                            <?php else: ?>
                                                <div class="d-flex align-items-center justify-content-center bg-secondary text-white rounded" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="bi bi-gear"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($service['name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo htmlspecialchars(substr($service['description'], 0, 100)); ?>
                                        <?php if (strlen($service['description']) > 100) echo '...'; ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo timeAgo($service['created_at']); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editService(<?php echo $service['id']; ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteService(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['name']); ?>')">
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
</div>

<!-- Add/Edit Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="serviceModalTitle">Add New Service</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="serviceForm" method="POST" enctype="multipart/form-data">
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
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Icon Type</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="icon_type" id="iconBootstrap" value="bootstrap" checked>
                                    <label class="form-check-label" for="iconBootstrap">
                                        Bootstrap Icon
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="icon_type" id="iconUpload" value="upload">
                                    <label class="form-check-label" for="iconUpload">
                                        Upload Image
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="bootstrapIconSection">
                        <label class="form-label fw-bold">Bootstrap Icon Class</label>
                        <select class="form-control" name="icon_value" id="serviceIcon">
                            <option value="">Select an icon</option>
                            <option value="bi-heart-pulse">Heart Pulse (bi-heart-pulse)</option>
                            <option value="bi-activity">Activity (bi-activity)</option>
                            <option value="bi-fire">Fire (bi-fire)</option>
                            <option value="bi-flower1">Flower (bi-flower1)</option>
                            <option value="bi-hand-thumbs-up">Hand Thumbs Up (bi-hand-thumbs-up)</option>
                            <option value="bi-person-arms-up">Person Arms Up (bi-person-arms-up)</option>
                            <option value="bi-spa">Spa (bi-spa)</option>
                            <option value="bi-droplet">Droplet (bi-droplet)</option>
                            <option value="bi-sun">Sun (bi-sun)</option>
                            <option value="bi-moon">Moon (bi-moon)</option>
                            <option value="bi-leaf">Leaf (bi-leaf)</option>
                            <option value="bi-gem">Gem (bi-gem)</option>
                        </select>
                        <small class="form-text text-muted">Choose from popular Bootstrap icons</small>
                    </div>
                    
                    <div class="mb-3" id="uploadIconSection" style="display: none;">
                        <label class="form-label fw-bold">Upload Icon Image</label>
                        <input type="file" class="form-control" name="icon_image" accept="image/*">
                        <small class="form-text text-muted">Upload a custom icon image (JPG, PNG, WebP)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Icon Preview</label>
                        <div id="iconPreview" class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 60px; height: 60px;">
                            <i class="bi bi-gear text-muted"></i>
                        </div>
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
    // Icon type toggle
    document.querySelectorAll("input[name=\"icon_type\"]").forEach(radio => {
        radio.addEventListener("change", function() {
            const bootstrapSection = document.getElementById("bootstrapIconSection");
            const uploadSection = document.getElementById("uploadIconSection");
            
            if (this.value === "bootstrap") {
                bootstrapSection.style.display = "block";
                uploadSection.style.display = "none";
            } else {
                bootstrapSection.style.display = "none";
                uploadSection.style.display = "block";
            }
            updateIconPreview();
        });
    });
    
    // Icon preview
    document.getElementById("serviceIcon").addEventListener("change", updateIconPreview);
    
    function updateIconPreview() {
        const iconType = document.querySelector("input[name=\"icon_type\"]:checked").value;
        const preview = document.getElementById("iconPreview");
        
        if (iconType === "bootstrap") {
            const iconClass = document.getElementById("serviceIcon").value;
            if (iconClass) {
                preview.innerHTML = `<i class="bi ${iconClass} text-primary"></i>`;
            } else {
                preview.innerHTML = `<i class="bi bi-gear text-muted"></i>`;
            }
        } else {
            preview.innerHTML = `<i class="bi bi-image text-muted"></i>`;
        }
    }
    
    function editService(id) {
        fetch("get_service_data.php?id=" + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("serviceModalTitle").textContent = "Edit Service";
                    document.getElementById("formAction").value = "edit";
                    document.getElementById("serviceId").value = id;
                    document.getElementById("serviceName").value = data.service.name;
                    document.getElementById("serviceDescription").value = data.service.description || "";
                    
                    // Set icon type
                    const iconType = data.service.icon_type || "bootstrap";
                    document.querySelector(`input[name="icon_type"][value="${iconType}"]`).checked = true;
                    
                    // Set icon value
                    if (iconType === "bootstrap") {
                        document.getElementById("serviceIcon").value = data.service.icon_value || "";
                        document.getElementById("bootstrapIconSection").style.display = "block";
                        document.getElementById("uploadIconSection").style.display = "none";
                    } else {
                        document.getElementById("bootstrapIconSection").style.display = "none";
                        document.getElementById("uploadIconSection").style.display = "block";
                    }
                    
                    updateIconPreview();
                    new bootstrap.Modal(document.getElementById("serviceModal")).show();
                } else {
                    alert("Error loading service data");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Error loading service data");
            });
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
        document.getElementById("bootstrapIconSection").style.display = "block";
        document.getElementById("uploadIconSection").style.display = "none";
        updateIconPreview();
    });
</script>';

include 'includes/admin_footer.php'; 
?>