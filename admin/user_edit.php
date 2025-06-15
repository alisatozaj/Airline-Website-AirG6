<link rel="stylesheet" href="../css/styles.css">
<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();

include '../includes/navbar.php';

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = intval($_GET['id']);

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = "User not found";
    header("Location: users.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Basic validation
    if (empty($username)) {
        $_SESSION['error'] = "Username cannot be empty";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
    } else {
        try {
            $conn->begin_transaction();
            
            // Update username
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->bind_param("si", $username, $user_id);
            $stmt->execute();
            
            // Update password if provided
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();
            }
            
            $conn->commit();
            $_SESSION['message'] = "User updated successfully";
            header("Location: users.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Error updating user: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .profile-icon {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 10px;
        }
        .user-role-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .admin-badge {
            background-color: #dc3545;
            color: white;
        }
        .client-badge {
            background-color: #198754;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <a href="users.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-4">
                <!-- Mini Profile Card -->
                <div class="profile-card text-center">
                    <div class="profile-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h4><?= htmlspecialchars($user['username']) ?></h4>
                    <span class="user-role-badge <?= $user['role'] === 'admin' ? 'admin-badge' : 'client-badge' ?>">
                        <?= ucfirst($user['role']) ?>
                    </span>
                    <div class="mt-3 text-muted">
                        <small>
                            <i class="fas fa-id-card"></i> ID: <?= $user['id'] ?><br>
                            <i class="fas fa-calendar-alt"></i> Joined: <?= date('M d, Y', strtotime($user['created_at'])) ?>
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-edit"></i> Edit User Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?= htmlspecialchars($user['username']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                                
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="users.php?delete=<?= $user['id'] ?>" class="btn btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this user?')">
                                        <i class="fas fa-trash-alt"></i> Delete User
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>