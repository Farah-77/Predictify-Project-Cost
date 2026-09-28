<?php
/**
 * Manage Users
 * CRUD operations for user accounts
 */

require_once '../config/settings.php';
require_once '../config/db_config.php';
$requireAdmin = true;
require_once '../includes/auth_check.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        // Add new user
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'] === 'admin' ? 'admin' : 'user';

        // Validate inputs
        if (empty($name) || empty($email) || empty($password)) {
            setFlashMessage('All fields are required', 'danger');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlashMessage('Invalid email address', 'danger');
        } elseif (strlen($password) < 6) {
            setFlashMessage('Password must be at least 6 characters', 'danger');
        } else {
            // Check if email already exists
            $sql = "SELECT id FROM users WHERE email = ?";
            $result = executePreparedQuery($conn, $sql, 's', [$email]);

            if ($result['result']->num_rows > 0) {
                setFlashMessage('Email already exists', 'danger');
            } else {
                // Hash password and insert user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
                $result = executePreparedQuery($conn, $sql, 'ssss', [$name, $email, $hashedPassword, $role]);

                if ($result['success']) {
                    setFlashMessage('User added successfully', 'success');
                } else {
                    setFlashMessage('Failed to add user', 'danger');
                }
            }
        }
        redirect('/admin/manage_users.php');
    } elseif ($action === 'edit') {
        // Edit existing user
        $userId = intval($_POST['user_id']);
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] === 'admin' ? 'admin' : 'user';

        // Validate inputs
        if (empty($name) || empty($email)) {
            setFlashMessage('Name and email are required', 'danger');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlashMessage('Invalid email address', 'danger');
        } elseif (!empty($password) && strlen($password) < 6) {
            setFlashMessage('Password must be at least 6 characters', 'danger');
        } else {
            // Check if email exists for another user
            $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
            $result = executePreparedQuery($conn, $sql, 'si', [$email, $userId]);

            if ($result['result']->num_rows > 0) {
                setFlashMessage('Email already exists', 'danger');
            } else {
                // Update user
                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?";
                    $result = executePreparedQuery($conn, $sql, 'ssssi', [$name, $email, $hashedPassword, $role, $userId]);
                } else {
                    $sql = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
                    $result = executePreparedQuery($conn, $sql, 'sssi', [$name, $email, $role, $userId]);
                }

                if ($result['success']) {
                    setFlashMessage('User updated successfully', 'success');
                } else {
                    setFlashMessage('Failed to update user', 'danger');
                }
            }
        }
        redirect('/admin/manage_users.php');
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    // Prevent deleting self
    if ($userId === $_SESSION['user_id']) {
        setFlashMessage('Cannot delete your own account', 'danger');
    } else {
        $sql = "DELETE FROM users WHERE id = ? AND role != 'admin'";
        $result = executePreparedQuery($conn, $sql, 'i', [$userId]);

        if ($result['success'] && $result['affected_rows'] > 0) {
            setFlashMessage('User deleted successfully', 'success');
        } else {
            setFlashMessage('Failed to delete user', 'danger');
        }
    }

    redirect('/admin/manage_users.php');
}

// Get all users
$sql = "SELECT u.*, COUNT(p.id) as project_count
        FROM users u
        LEFT JOIN projects p ON u.id = p.user_id
        GROUP BY u.id
        ORDER BY u.created_at DESC";
$users = $conn->query($sql);

$pageTitle = 'Manage Users';
include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4" style="color: var(--primary-color);">
                <i class="bi bi-people"></i> Manage Users
            </h2>
        </div>
    </div>

    <?php
    $flash = getFlashMessage();
    if ($flash):
    ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header  d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="color: var(--primary-color);">
                All Users
            </h5>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-primary"><?php echo $users->num_rows; ?> total users</span>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openAddModal()">
                    <i class="bi bi-plus-circle"></i> Add New User
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Projects</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users->num_rows > 0): ?>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'info'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $user['project_count']; ?></td>
                                    <td><?php echo formatDate($user['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal" data-bs-target="#userModal"
                                                        onclick='openEditModal(<?php echo json_encode($user); ?>)'>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <?php if ($user['role'] !== 'admin'): ?>
                                                    <a href="?action=delete&id=<?php echo $user['id']; ?>"
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Are you sure you want to delete this user?');">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted small">Current User</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- User Add/Edit Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST" id="userForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="user_id" id="userId" value="">

                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel" style="color: var(--primary-color);">
                        <i class="bi bi-person-plus"></i> <span id="modalTitle">Add New User</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="userName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="userName" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="userEmail" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="userEmail" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="userPassword" class="form-label">
                            Password <span class="text-danger" id="passwordRequired">*</span>
                            <span class="text-muted small" id="passwordOptional" style="display: none;">(Leave blank to keep current password)</span>
                        </label>
                        <input type="password" class="form-control" id="userPassword" name="password" minlength="6">
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="userRole" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="userRole" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background-color: var(--primary-color); border: none;">
                        <i class="bi bi-save"></i> <span id="submitButtonText">Add User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    // Reset form
    document.getElementById('userForm').reset();
    document.getElementById('formAction').value = 'add';
    document.getElementById('userId').value = '';
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('submitButtonText').textContent = 'Add User';
    document.getElementById('userPassword').required = true;
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('passwordOptional').style.display = 'none';
}

function openEditModal(user) {
    // Populate form with user data
    document.getElementById('formAction').value = 'edit';
    document.getElementById('userId').value = user.id;
    document.getElementById('userName').value = user.name;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userRole').value = user.role;
    document.getElementById('userPassword').value = '';
    document.getElementById('userPassword').required = false;
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('submitButtonText').textContent = 'Update User';
    document.getElementById('passwordRequired').style.display = 'none';
    document.getElementById('passwordOptional').style.display = 'inline';
}
</script>

<?php include '../includes/footer.php'; ?>
