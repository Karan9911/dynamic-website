<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdminLogin();

$pageTitle = 'Manage Therapists';
$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $name = sanitizeInput($_POST['name'] ?? '');
        $price = (float)($_POST['price_per_session'] ?? 0);
        $height = sanitizeInput($_POST['height'] ?? '');
        $weight = sanitizeInput($_POST['weight'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $availability = sanitizeInput($_POST['availability_slots'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $services = $_POST['services'] ?? [];
        
        if (empty($name) || $price <= 0) {
            $message = 'Name and valid price are required';
            $messageType = 'danger';
        } else {
            $db = getDB();
            
            try {
                $db->beginTransaction();
                
                if ($action === 'add') {
                    // Add new therapist
                    $stmt = $db->prepare("
                        INSERT INTO therapists (name, price_per_session, height, weight, description, availability_slots, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$name, $price, $height, $weight, $description, $availability, $status]);
                    $therapistId = $db->lastInsertId();
                    
                } else {
                    // Edit existing therapist
                    $therapistId = (int)$_POST['therapist_id'];
                    $stmt = $db->prepare("
                        UPDATE therapists 
                        SET name = ?, price_per_session = ?, height = ?, weight = ?, description = ?, availability_slots = ?, status = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $price, $height, $weight, $description, $availability, $status, $therapistId]);
                }
                
                // Update services
                $db->prepare("DELETE FROM therapist_services WHERE therapist_id = ?")->execute([$therapistId]);
                
                foreach ($services as $serviceId) {
                    $stmt = $db->prepare("INSERT INTO therapist_services (therapist_id, service_id) VALUES (?, ?)");
                    $stmt->execute([$therapistId, $serviceId]);
                }
                
                // Handle image upload
                if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = uploadImage($_FILES['main_image'], 'therapists');
                    if ($uploadResult['success']) {
                        // Delete old main image
                        $oldImage = $db->prepare("SELECT main_image FROM therapists WHERE id = ?")->execute([$therapistId]);
                        $oldImage = $db->prepare("SELECT main_image FROM therapists WHERE id = ?")->fetch();
                        if ($oldImage && $oldImage['main_image']) {
                            deleteImage('therapists/' . $oldImage['main_image']);
                        }
                        
                        // Update main image
                        $stmt = $db->prepare("UPDATE therapists SET main_image = ? WHERE id = ?");
                        $stmt->execute([$uploadResult['filename'], $therapistId]);
                        
                        // Add to therapist_images
                        $stmt = $db->prepare("
                            INSERT INTO therapist_images (therapist_id, image_path, is_main) 
                            VALUES (?, ?, 1)
                            ON DUPLICATE KEY UPDATE image_path = VALUES(image_path)
                        ");
                        $stmt->execute([$therapistId, 'therapists/' . $uploadResult['filename']]);
                    }
                }
                
                // Handle gallery images
                if (isset($_FILES['gallery_images'])) {
                    foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmpName) {
                        if ($_FILES['gallery_images']['error'][$key] === UPLOAD_ERR_OK) {
                            $file = [
                                'name' => $_FILES['gallery_images']['name'][$key],
                                'tmp_name' => $tmpName,
                                'size' => $_FILES['gallery_images']['size'][$key],
                                'error' => $_FILES['gallery_images']['error'][$key]
                            ];
                            
                            $uploadResult = uploadImage($file, 'therapists');
                            if ($uploadResult['success']) {
                                $stmt = $db->prepare("
                                    INSERT INTO therapist_images (therapist_id, image_path, is_main) 
                                    VALUES (?, ?, 0)
                                ");
                                $stmt->execute([$therapistId, 'therapists/' . $uploadResult['filename']]);
                            }
                        }
                    }
                }
                
                $db->commit();
                $message = $action === 'add' ? 'Therapist added successfully!' : 'Therapist updated successfully!';
                $messageType = 'success';
                
            } catch (Exception $e) {
                $db->rollback();
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'delete') {
        $therapistId = (int)$_POST['therapist_id'];
        
        $db = getDB();
        try {
            // Get images to delete
            $images = getTherapistImages($therapistId);
            foreach ($images as $image) {
                deleteImage($image['image_path']);
            }
            
            // Delete therapist (cascades to images and services)
            $stmt = $db->prepare("DELETE FROM therapists WHERE id = ?");
            $stmt->execute([$therapistId]);
            
            $message = 'Therapist deleted successfully!';
            $messageType = 'success';
            
        } catch (Exception $e) {
            $message = 'Error deleting therapist: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Get all therapists
$therapists = getAllTherapists('all'); // Get both active and inactive
$services = getAllServices();
?>

<?php include 'includes/admin_header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Therapists</h1>
    <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#therapistModal">
        <i class="bi bi-plus text-white-50"></i> Add New Therapist
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Therapists List</h6>
    </div>
    <div class="card-body">
        <?php if (empty($therapists)): ?>
            <div class="text-center py-5">
                <i class="bi bi-person-plus display-4 text-gray-300"></i>
                <h5 class="text-gray-500 mt-3">No therapists found</h5>
                <p class="text-gray-400">Click "Add New Therapist" to get started.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price/Session</th>
                            <th>Services</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($therapists as $therapist): 
                            $therapistServices = getTherapistServices($therapist['id']);
                            $images = getTherapistImages($therapist['id']);
                            $mainImage = !empty($images) ? UPLOAD_URL . $images[0]['image_path'] : 'https://images.pexels.com/photos/3757942/pexels-photo-3757942.jpeg?auto=compress&cs=tinysrgb&w=100';
                        ?>
                            <tr>
                                <td><?php echo $therapist['id']; ?></td>
                                <td>
                                    <img src="<?php echo $mainImage; ?>" 
                                         class="rounded" width="50" height="50" style="object-fit: cover;"
                                         alt="<?php echo htmlspecialchars($therapist['name']); ?>">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($therapist['name']); ?></strong><br>
                                    <small class="text-muted">
                                        <?php echo $therapist['height'] ? 'H: ' . $therapist['height'] : ''; ?>
                                        <?php echo $therapist['weight'] ? ' W: ' . $therapist['weight'] : ''; ?>
                                    </small>
                                </td>
                                <td><?php echo formatPrice($therapist['price_per_session']); ?></td>
                                <td>
                                    <?php foreach (array_slice($therapistServices, 0, 2) as $service): ?>
                                        <span class="badge badge-light me-1"><?php echo htmlspecialchars($service['name']); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($therapistServices) > 2): ?>
                                        <span class="badge badge-secondary">+<?php echo count($therapistServices) - 2; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $therapist['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($therapist['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo timeAgo($therapist['created_at']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="editTherapist(<?php echo $therapist['id']; ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteTherapist(<?php echo $therapist['id']; ?>, '<?php echo htmlspecialchars($therapist['name']); ?>')">
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

<!-- Add/Edit Therapist Modal -->
<div class="modal fade" id="therapistModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="therapistModalTitle">Add New Therapist</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="therapistForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="therapist_id" id="therapistId">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name *</label>
                            <input type="text" class="form-control" name="name" id="therapistName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Price per Session (₹) *</label>
                            <input type="number" class="form-control" name="price_per_session" id="therapistPrice" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Height</label>
                            <input type="text" class="form-control" name="height" id="therapistHeight" placeholder="e.g., 5'6"">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Weight</label>
                            <input type="text" class="form-control" name="weight" id="therapistWeight" placeholder="e.g., 55kg">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Services</label>
                            <div class="row">
                                <?php foreach ($services as $service): ?>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="services[]" 
                                                   value="<?php echo $service['id']; ?>" id="service<?php echo $service['id']; ?>">
                                            <label class="form-check-label" for="service<?php echo $service['id']; ?>">
                                                <?php echo htmlspecialchars($service['name']); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="therapistDescription" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Availability</label>
                            <textarea class="form-control" name="availability_slots" id="therapistAvailability" rows="2" 
                                      placeholder="e.g., Mon-Fri: 9 AM - 6 PM, Sat: 10 AM - 4 PM"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="therapistStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Main Image</label>
                            <input type="file" class="form-control" name="main_image" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Gallery Images</label>
                            <input type="file" class="form-control" name="gallery_images[]" accept="image/*" multiple>
                            <small class="form-text text-muted">You can select multiple images for the gallery.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Therapist</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteTherapistName"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form style="display: inline;" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="therapist_id" id="deleteTherapistId">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
$extraScripts = '<script>
    function editTherapist(id) {
        // Fetch therapist data and populate form
        fetch("get_therapist_data.php?id=" + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("therapistModalTitle").textContent = "Edit Therapist";
                    document.getElementById("formAction").value = "edit";
                    document.getElementById("therapistId").value = id;
                    document.getElementById("therapistName").value = data.therapist.name;
                    document.getElementById("therapistPrice").value = data.therapist.price_per_session;
                    document.getElementById("therapistHeight").value = data.therapist.height || "";
                    document.getElementById("therapistWeight").value = data.therapist.weight || "";
                    document.getElementById("therapistDescription").value = data.therapist.description || "";
                    document.getElementById("therapistAvailability").value = data.therapist.availability_slots || "";
                    document.getElementById("therapistStatus").value = data.therapist.status;
                    
                    // Check services
                    const checkboxes = document.querySelectorAll("input[name=\"services[]\"]");
                    checkboxes.forEach(cb => cb.checked = false);
                    data.services.forEach(service => {
                        const checkbox = document.getElementById("service" + service.id);
                        if (checkbox) checkbox.checked = true;
                    });
                    
                    new bootstrap.Modal(document.getElementById("therapistModal")).show();
                }
            });
    }
    
    function deleteTherapist(id, name) {
        document.getElementById("deleteTherapistId").value = id;
        document.getElementById("deleteTherapistName").textContent = name;
        new bootstrap.Modal(document.getElementById("deleteModal")).show();
    }
    
    // Reset form when modal is closed
    document.getElementById("therapistModal").addEventListener("hidden.bs.modal", function() {
        document.getElementById("therapistForm").reset();
        document.getElementById("therapistModalTitle").textContent = "Add New Therapist";
        document.getElementById("formAction").value = "add";
        document.getElementById("therapistId").value = "";
    });
</script>';

include 'includes/admin_footer.php'; 
?>