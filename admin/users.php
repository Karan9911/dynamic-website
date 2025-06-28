<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdminLogin();

$pageTitle = 'User Management';
$message = '';
$messageType = '';

// Handle user status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $userId = (int)$_POST['user_id'];
    
    if ($action === 'update_status') {
        $status = $_POST['status'];
        $validStatuses = ['active', 'inactive'];
        
        if (in_array($status, $validStatuses)) {
            $db = getDB();
            try {
                $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
                if ($stmt->execute([$status, $userId])) {
                    $message = 'User status updated successfully!';
                    $messageType = 'success';
                }
            } catch (Exception $e) {
                $message = 'Error updating user status.';
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'delete') {
        $db = getDB();
        try {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
            if ($stmt->execute([$userId])) {
                $message = 'User deleted successfully!';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = 'Error deleting user.';
            $messageType = 'danger';
        }
    }
}

// Get all users
$db = getDB();
$stmt = $db->prepare("SELECT * FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll();

// Get statistics
$stats = [
    'total' => count($users),
    'active' => 0,
    'inactive' => 0,
    'admins' => 0,
    'regular_users' => 0
];

foreach ($users as $user) {
    if ($user['status'] === 'active') $stats['active']++;
    if ($user['status'] === 'inactive') $stats['inactive']++;
    if ($user['role'] === 'admin') $stats['admins']++;
    if ($user['role'] === 'user') $stats['regular_users']++;
}
?>

<?php include 'includes/admin_header.php'; ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">User Management</h1>
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
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['active']; ?></div>
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
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Inactive</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['inactive']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-x-circle fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Admins</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['admins']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-shield-check fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Regular Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['regular_users']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-person fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Users List</h6>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <i class="bi bi-people display-4 text-gray-300"></i>
                <h5 class="text-gray-500 mt-3">No users found</h5>
                <p class="text-gray-400">Users will appear here when they register.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User Details</th>
                            <th>Contact</th>
                            <th>Location</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-light">#<?php echo $user['id']; ?></span>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($user['name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="text-muted"><?php echo htmlspecialchars($user['city'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo timeAgo($user['created_at']); ?></small>
                                </td>
                                <td>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="updateUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Protected</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update User Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="user_id" id="statusUserId">
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="userStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form style="display: inline;" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
$extraScripts = '<script>
    function updateUserStatus(id, currentStatus) {
        document.getElementById("statusUserId").value = id;
        document.getElementById("userStatus").value = currentStatus;
        new bootstrap.Modal(document.getElementById("statusModal")).show();
    }
    
    function deleteUser(id, name) {
        document.getElementById("deleteUserId").value = id;
        document.getElementById("deleteUserName").textContent = name;
        new bootstrap.Modal(document.getElementById("deleteModal")).show();
    }
</script>';

include 'includes/admin_footer.php'; 
?>