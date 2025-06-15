<link rel="stylesheet" href="../css/styles.css">
<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();

include '../includes/navbar.php';

// Handle user deletion
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    // Prevent deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot delete your own account!";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "User deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting user";
        }
    }
    header("Location: users.php");
    exit();
}

// Handle role change
if (isset($_POST['change_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['new_role'];
    
    // Prevent changing your own role
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot change your own role!";
    } else {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "User role updated successfully";
        } else {
            $_SESSION['error'] = "Error updating user role";
        }
    }
    header("Location: users.php");
    exit();
}

// Search functionality
$search = $_GET['search'] ?? '';

// Fetch all users with search functionality
$user_query = "SELECT u.*, 
              (SELECT COUNT(*) FROM bookings WHERE user_id = u.id) as booking_count,
              (SELECT b.confirmation_code FROM bookings b WHERE b.user_id = u.id ORDER BY b.booking_date DESC LIMIT 1) as last_confirmation
              FROM users u";

if (!empty($search)) {
    $search_term = $conn->real_escape_string($search);
    $user_query .= " WHERE u.username LIKE '%$search_term%' 
                    OR u.id LIKE '%$search_term%'
                    OR EXISTS (
                        SELECT 1 FROM bookings b 
                        WHERE b.user_id = u.id 
                        AND b.confirmation_code LIKE '%$search_term%'
                    )";
}

$user_query .= " ORDER BY u.created_at DESC";
$users = $conn->query($user_query);

// Fetch guest bookings with search
$guest_query = "SELECT b.*, 
               GROUP_CONCAT(p.full_name SEPARATOR ', ') as passengers,
               COUNT(p.passenger_id) as passenger_count
               FROM bookings b
               JOIN passengers p ON b.booking_id = p.booking_id
               WHERE b.user_id IS NULL";

if (!empty($search)) {
    $search_term = $conn->real_escape_string($search);
    $guest_query .= " AND (b.confirmation_code LIKE '%$search_term%'
                     OR p.full_name LIKE '%$search_term%'
                     OR b.booking_id LIKE '%$search_term%')";
}

$guest_query .= " GROUP BY b.booking_id
                ORDER BY b.booking_date DESC";
$guest_bookings = $conn->query($guest_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <style>
        .badge {
            font-family: monospace;
            font-size: 0.9em;
        }
        .search-container {
            max-width: 500px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>User Management</h2>
            <form method="GET" class="search-container">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search users, bookings..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="users.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <ul class="nav nav-tabs mb-4" id="userTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#registeredUsers">
                    Registered Users (<?= $users->num_rows ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#guestBookings">
                    Guest Bookings (<?= $guest_bookings->num_rows ?>)
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Registered Users Tab -->
            <div class="tab-pane fade show active" id="registeredUsers">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Last Confirmation</th>
                                <th>Bookings</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <select name="new_role" class="form-select form-select-sm" 
                                                onchange="this.form.submit()" 
                                                <?= $user['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                            <option value="client" <?= $user['role'] == 'client' ? 'selected' : '' ?>>Client</option>
                                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                        <input type="hidden" name="change_role" value="1">
                                    </form>
                                </td>
                                <td>
                                    <?php if (!empty($user['last_confirmation'])): ?>
                                        <span class="badge bg-info"><?= $user['last_confirmation'] ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">None</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $user['booking_count'] ?></td>
                                <td>
    <a href="user_edit.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-info">
        <i class="bi bi-pencil"></i> Edit
    </a>
    <a href="user_bookings.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-primary">
        <i class="bi bi-list-ul"></i> Bookings
    </a>
    <?php if ($user['id'] != $_SESSION['user_id']): ?>
        <a href="users.php?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger"
           onclick="return confirm('Delete this user? All their bookings will remain but will become guest bookings.')">
            <i class="bi bi-trash"></i> Delete
        </a>
    <?php endif; ?>
</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Guest Bookings Tab -->
            <div class="tab-pane fade" id="guestBookings">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Confirmation</th>
                                <th>Passengers</th>
                                <th>Booking Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = $guest_bookings->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary"><?= $booking['confirmation_code'] ?></span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($booking['passengers']) ?> 
                                    <span class="badge bg-secondary ms-2"><?= $booking['passenger_count'] ?></span>
                                </td>
                                <td><?= date('M d, Y H:i', strtotime($booking['booking_date'])) ?></td>
                                <td>
                                <a href="guest_management.php?id=<?= $booking['booking_id'] ?>" class="btn btn-sm btn-primary">
    <i class="bi bi-gear"></i> View
</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script>
        // Activate Bootstrap tabs
        const tabElms = document.querySelectorAll('a[data-bs-toggle="tab"]');
        tabElms.forEach(tabEl => {
            tabEl.addEventListener('shown.bs.tab', function (event) {
                localStorage.setItem('activeUserTab', event.target.getAttribute('href'));
            })
        });
        
        // Remember active tab
        const activeTab = localStorage.getItem('activeUserTab');
        if (activeTab) {
            const tab = new bootstrap.Tab(document.querySelector(`[href="${activeTab}"]`));
            tab.show();
        }
    </script>
</body>
</html>