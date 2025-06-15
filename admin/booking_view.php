<link rel="stylesheet" href="../css/styles.css">
<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();

include '../includes/navbar.php';

if (!isset($_GET['id'])) {
    header("Location: bookings.php");
    exit();
}

$booking_id = intval($_GET['id']);

// Get booking details
$stmt = $conn->prepare("SELECT b.*, 
                       f1.departure as dep_from, f1.arrival as dep_to, 
                       f1.departure_date as dep_date, f1.departure_time as dep_time,
                       f1.price as dep_price, f1.airline as dep_airline,
                       f2.departure as ret_from, f2.arrival as ret_to,
                       f2.departure_date as ret_date, f2.departure_time as ret_time,
                       f2.price as ret_price, f2.airline as ret_airline,
                       u.username, u.id as user_id
                       FROM bookings b
                       LEFT JOIN flights f1 ON b.departure_flight_id = f1.id
                       LEFT JOIN flights f2 ON b.return_flight_id = f2.id
                       LEFT JOIN users u ON b.user_id = u.id
                       WHERE b.booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    $_SESSION['error'] = "Booking not found";
    header("Location: bookings.php");
    exit();
}

// Get passengers
$stmt = $conn->prepare("SELECT p.*, 
                       GROUP_CONCAT(CONCAT(sb.seat_number, ' (', 
                       (SELECT departure FROM flights WHERE id = sb.flight_id), '→',
                       (SELECT arrival FROM flights WHERE id = sb.flight_id), ')') 
                       SEPARATOR ', ') as seats
                       FROM passengers p
                       LEFT JOIN seats_booked sb ON p.passenger_id = sb.passenger_id
                       WHERE p.booking_id = ?
                       GROUP BY p.passenger_id");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$passengers = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking Details</title></head>
<body>
    <div class="container mt-4">
        <a href="bookings.php" class="btn btn-secondary mb-3">← Back to Bookings</a>
        
        <h2>Booking #<?= $booking['booking_id'] ?></h2>
        
        <div class="card mb-4">
            <div class="card-header">
                <h4>Flight Details</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Departure Flight</h5>
                        <p>
                            <strong>Route:</strong> <?= $booking['dep_from'] ?> → <?= $booking['dep_to'] ?><br>
                            <strong>Date:</strong> <?= date('M d, Y', strtotime($booking['dep_date'])) ?><br>
                            <strong>Time:</strong> <?= substr($booking['dep_time'], 0, 5) ?><br>
                            <strong>Airline:</strong> <?= $booking['dep_airline'] ?><br>
                            <strong>Price:</strong> €<?= number_format($booking['dep_price'], 2) ?>
                        </p>
                    </div>
                    
                    <?php if ($booking['return_flight_id']): ?>
                    <div class="col-md-6">
                        <h5>Return Flight</h5>
                        <p>
                            <strong>Route:</strong> <?= $booking['ret_from'] ?> → <?= $booking['ret_to'] ?><br>
                            <strong>Date:</strong> <?= date('M d, Y', strtotime($booking['ret_date'])) ?><br>
                            <strong>Time:</strong> <?= substr($booking['ret_time'], 0, 5) ?><br>
                            <strong>Airline:</strong> <?= $booking['ret_airline'] ?><br>
                            <strong>Price:</strong> €<?= number_format($booking['ret_price'], 2) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-3">
                    <strong>Total Price:</strong> €<?= number_format($booking['total_price'], 2) ?><br>
                    <strong>Booking Date:</strong> <?= date('M d, Y H:i', strtotime($booking['booking_date'])) ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h4>Passenger Details</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Seat(s)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($passenger = $passengers->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($passenger['full_name']) ?></td>
                                <td><?= $passenger['gender'] ?></td>
                                <td><?= $passenger['seats'] ?? 'Not assigned' ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>