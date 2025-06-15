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

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = "User not found";
    header("Location: users.php");
    exit();
}

// Get user's bookings
$stmt = $conn->prepare("
    SELECT b.*, 
    f1.departure as dep_from, f1.arrival as dep_to, 
    f1.departure_date as dep_date, f1.departure_time as dep_time,
    f2.departure as ret_from, f2.arrival as ret_to,
    f2.departure_date as ret_date, f2.departure_time as ret_time
    FROM bookings b
    LEFT JOIN flights f1 ON b.departure_flight_id = f1.id
    LEFT JOIN flights f2 ON b.return_flight_id = f2.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Bookings</title>
</head>
<body>
    <div class="container mt-4">
        <a href="users.php" class="btn btn-secondary mb-3">← Back to Users</a>
        
        <h2>Bookings for <?= htmlspecialchars($user['username']) ?></h2>
        <p class="text-muted">Role: <?= ucfirst($user['role']) ?> | Registered: <?= date('M d, Y', strtotime($user['created_at'])) ?></p>
        
        <?php if ($bookings->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Booking ID</th>
                            <th>Departure Flight</th>
                            <th>Return Flight</th>
                            <th>Booking Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td><?= $booking['booking_id'] ?></td>
                            <td>
                                <?= $booking['dep_from'] ?> → <?= $booking['dep_to'] ?><br>
                                <?= date('M d, Y', strtotime($booking['dep_date'])) ?> 
                                <?= substr($booking['dep_time'], 0, 5) ?>
                            </td>
                            <td>
                                <?php if ($booking['return_flight_id']): ?>
                                    <?= $booking['ret_from'] ?> → <?= $booking['ret_to'] ?><br>
                                    <?= date('M d, Y', strtotime($booking['ret_date'])) ?> 
                                    <?= substr($booking['ret_time'], 0, 5) ?>
                                <?php else: ?>
                                    One-way
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y H:i', strtotime($booking['booking_date'])) ?></td>
                            <td>
                            <a href="../admin/booking_view.php?id=<?= $booking['booking_id'] ?>" class="btn btn-sm btn-primary">View Booking</a>

                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">This user has no bookings yet.</div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>