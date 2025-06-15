<link rel="stylesheet" href="../css/styles.css">
<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
include '../includes/navbar.php';

// Debug session
// error_log("Dashboard access - Session ID: " . session_id());
// error_log("Session data: " . print_r($_SESSION, true));

requireAdmin();
?>

<body>
    <div class="container mt-4">
        <h2>Admin Dashboard</h2>
        
        <div class="row mt-4">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Flights</h5>
                        <p class="card-text">Manage all flights in the system</p>
                        <a href="flights.php" class="btn btn-primary">Manage Flights</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Bookings</h5>
                        <p class="card-text">View and manage customer bookings</p>
                        <a href="bookings.php" class="btn btn-primary">Manage Bookings</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Users</h5>
                        <p class="card-text">Manage admin users</p>
                        <a href="users.php" class="btn btn-primary">Manage Users</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="../main.js"></script>
</body>