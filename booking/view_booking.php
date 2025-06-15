<link rel="stylesheet" href="../css/styles.css">
<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
include '../includes/navbar.php';

// Check if confirmation code is provided
$confirmation_code = $_GET['code'] ?? '';
if (empty($confirmation_code)) {
    header("Location: ../pages/my_trips.php?error=no_code");
    exit();
}

// Handle cancel booking request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // 1. Get booking details
        $stmt = $conn->prepare("SELECT booking_id FROM bookings WHERE confirmation_code = ?");
        $stmt->bind_param("s", $confirmation_code);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        
        if (!$booking) {
            throw new Exception("Booking not found");
        }
        
        $booking_id = $booking['booking_id'];
        
        // 2. Delete seats (cascade should handle this, but explicit is better)
        $stmt = $conn->prepare("DELETE sb FROM seats_booked sb
                              JOIN passengers p ON sb.passenger_id = p.passenger_id
                              WHERE p.booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
        // 3. Delete passengers
        $stmt = $conn->prepare("DELETE FROM passengers WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
        // 4. Delete booking
        $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Redirect with success message
        header("Location: ../pages/my_trips.php?success=booking_cancelled");
        exit();
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        header("Location: view_booking.php?code=$confirmation_code&error=cancel_failed");
        exit();
    }
}

// Fetch booking details
$stmt = $conn->prepare("SELECT * FROM bookings WHERE confirmation_code = ?");
$stmt->bind_param("s", $confirmation_code);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header("Location: ../pages/my_trips.php?error=invalid_code");
    exit();
}

// Fetch flights
$departure_flight = $return_flight = null;
$stmt = $conn->prepare("SELECT * FROM flights WHERE id = ?");
$stmt->bind_param("i", $booking['departure_flight_id']);
$stmt->execute();
$departure_flight = $stmt->get_result()->fetch_assoc();

if ($booking['return_flight_id']) {
    $stmt->bind_param("i", $booking['return_flight_id']);
    $stmt->execute();
    $return_flight = $stmt->get_result()->fetch_assoc();
}

// Fetch passengers
$stmt = $conn->prepare("SELECT * FROM passengers WHERE booking_id = ?");
$stmt->bind_param("i", $booking['booking_id']);
$stmt->execute();
$passengers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate correct total price (price per passenger * number of passengers)
$passenger_count = count($passengers);
$correct_total_price = $departure_flight['price'] * $passenger_count;
if ($return_flight) {
    $correct_total_price += $return_flight['price'] * $passenger_count;
}

// Fetch seats (simplified to just get seat numbers)
$passenger_seats = [];
foreach ($passengers as $passenger) {
    $stmt = $conn->prepare("SELECT seat_number FROM seats_booked WHERE passenger_id = ?");
    $stmt->bind_param("i", $passenger['passenger_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $passenger_seats[$passenger['passenger_id']] = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-print {
            display: none; /* Hide print button since we're not implementing it */
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <?php if (isset($_GET['error']) && $_GET['error'] === 'cancel_failed'): ?>
        <div class="alert alert-danger">Failed to cancel booking. Please try again.</div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2>Booking Details</h2>
            <div class="d-flex justify-content-between">
                <span>Confirmation Code: <strong><?= htmlspecialchars($confirmation_code) ?></strong></span>
                <span>Booking Date: <?= date('M d, Y', strtotime($booking['booking_date'])) ?></span>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Departure Flight -->
            <div class="mb-4">
                <h4>Departure Flight</h4>
                <div class="card p-3">
                    <div class="row">
                        <div class="col-md-8">
                            <h5><?= htmlspecialchars($departure_flight['departure']) ?> to <?= htmlspecialchars($departure_flight['arrival']) ?></h5>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong><?= date('M d, Y', strtotime($departure_flight['departure_date'])) ?></strong>
                                    <div><?= substr($departure_flight['departure_time'], 0, 5) ?> - <?= substr($departure_flight['arrival_time'], 0, 5) ?></div>
                                </div>
                                <div>
                                    <div>Duration: <?= htmlspecialchars($departure_flight['duration']) ?></div>
                                    <div>Airline: <?= htmlspecialchars($departure_flight['airline']) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <h4>€<?= number_format($departure_flight['price'], 2) ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Return Flight (if exists) -->
            <?php if ($return_flight): ?>
                <div class="mb-4">
                    <h4>Return Flight</h4>
                    <div class="card p-3">
                        <div class="row">
                            <div class="col-md-8">
                                <h5><?= htmlspecialchars($return_flight['departure']) ?> to <?= htmlspecialchars($return_flight['arrival']) ?></h5>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?= date('M d, Y', strtotime($return_flight['departure_date'])) ?></strong>
                                        <div><?= substr($return_flight['departure_time'], 0, 5) ?> - <?= substr($return_flight['arrival_time'], 0, 5) ?></div>
                                    </div>
                                    <div>
                                        <div>Duration: <?= htmlspecialchars($return_flight['duration']) ?></div>
                                        <div>Airline: <?= htmlspecialchars($return_flight['airline']) ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <h4>€<?= number_format($return_flight['price'], 2) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Passengers and Seats -->
            <div class="mb-4">
                <h4>Passengers</h4>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Seat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($passengers as $passenger): ?>
                                <tr>
                                    <td><?= htmlspecialchars($passenger['full_name']) ?></td>
                                    <td><?= htmlspecialchars($passenger['gender']) ?></td>
                                    <td>
                                        <?php 
                                        // Display first seat found for the passenger
                                        echo !empty($passenger_seats[$passenger['passenger_id']]) 
                                            ? htmlspecialchars($passenger_seats[$passenger['passenger_id']][0]['seat_number'])
                                            : 'Not assigned';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Price Summary -->
            <div class="card p-3 bg-light">
                <h4>Price Summary</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Departure Flight (x<?= $passenger_count ?>):</span>
                            <span>€<?= number_format($departure_flight['price'] * $passenger_count, 2) ?></span>
                        </div>
                        <?php if ($return_flight): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Return Flight (x<?= $passenger_count ?>):</span>
                                <span>€<?= number_format($return_flight['price'] * $passenger_count, 2) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total:</span>
                            <span>€<?= number_format($correct_total_price, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-4 d-flex justify-content-between">
                <a href="../pages/my_trips.php" class="btn btn-secondary">Back to My Trips</a>
                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                    <button type="submit" name="cancel_booking" class="btn btn-danger">Cancel Booking</button>
                </form>
                <button class="btn btn-primary btn-print">Print Ticket</button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>