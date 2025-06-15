<link rel="stylesheet" href="../css/styles.css">
<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();

include '../includes/navbar.php';

// Handle booking deletion
if (isset($_GET['delete'])) {
    $booking_id = intval($_GET['delete']);
    
    try {
        $conn->begin_transaction();
        
        // First delete seats (if any)
        $stmt = $conn->prepare("DELETE sb FROM seats_booked sb
                              JOIN passengers p ON sb.passenger_id = p.passenger_id
                              WHERE p.booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
        // Then delete passengers
        $stmt = $conn->prepare("DELETE FROM passengers WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
        // Finally delete the booking
        $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Booking deleted successfully";
            $conn->commit();
        } else {
            throw new Exception("Error deleting booking");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error deleting booking: " . $e->getMessage();
    }
    
    header("Location: bookings.php");
    exit();
}

// Fetch all bookings with related data
$query = "SELECT b.*, 
          f1.departure as dep_from, f1.arrival as dep_to, 
          f1.departure_date as dep_date, f1.departure_time as dep_time,
          f2.departure as ret_from, f2.arrival as ret_to,
          f2.departure_date as ret_date, f2.departure_time as ret_time,
          u.username
          FROM bookings b
          LEFT JOIN flights f1 ON b.departure_flight_id = f1.id
          LEFT JOIN flights f2 ON b.return_flight_id = f2.id
          LEFT JOIN users u ON b.user_id = u.id
          ORDER BY b.booking_date DESC";
$bookings = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Bookings</title>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Bookings</h2>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Booking ID</th>
                        <th>User</th>
                        <th>Departure Flight</th>
                        <th>Return Flight</th>
                        <th>Passengers</th>
                        <th>Total Price</th>
                        <th>Booking Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?= $booking['booking_id'] ?></td>
                        <td><?= $booking['username'] ?? 'Guest' ?></td>
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
                        <td>
                            <?php 
                            // Count passengers for this booking
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM passengers WHERE booking_id = ?");
                            $stmt->bind_param("i", $booking['booking_id']);
                            $stmt->execute();
                            $passenger_count = $stmt->get_result()->fetch_row()[0];
                            echo $passenger_count;
                            ?>
                        </td>
                        <td>€<?= number_format($booking['total_price'], 2) ?></td>
                        <td><?= date('M d, Y H:i', strtotime($booking['booking_date'])) ?></td>
                        <td>
                            <a href="booking_view.php?id=<?= $booking['booking_id'] ?>" class="btn btn-sm btn-primary">View</a>
                            <a href="bookings.php?delete=<?= $booking['booking_id'] ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure? This will delete all passenger data too!')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>