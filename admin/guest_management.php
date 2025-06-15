<link rel="stylesheet" href="../css/styles.css">
<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();

include '../includes/navbar.php';

// Handle guest info updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = intval($_POST['booking_id']);
    $passenger_id = intval($_POST['passenger_id']);
    $full_name = trim($_POST['full_name']);
    $gender = $_POST['gender'];
    
    $stmt = $conn->prepare("UPDATE passengers SET full_name = ?, gender = ? WHERE passenger_id = ? AND booking_id = ?");
    $stmt->bind_param("ssii", $full_name, $gender, $passenger_id, $booking_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Passenger updated successfully";
    } else {
        $_SESSION['error'] = "Error updating passenger";
    }
    header("Location: guest_management.php?id=".$booking_id);
    exit();
}

// Get booking ID
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Fetch guest booking details
if ($booking_id) {
    $stmt = $conn->prepare("SELECT b.*, 
                           f1.departure as dep_from, f1.arrival as dep_to,
                           f1.departure_date as dep_date, f1.departure_time as dep_time,
                           f2.departure as ret_from, f2.arrival as ret_to,
                           f2.departure_date as ret_date, f2.departure_time as ret_time
                           FROM bookings b
                           LEFT JOIN flights f1 ON b.departure_flight_id = f1.id
                           LEFT JOIN flights f2 ON b.return_flight_id = f2.id
                           WHERE b.booking_id = ? AND b.user_id IS NULL");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    
    if (!$booking) {
        $_SESSION['error'] = "Guest booking not found";
        header("Location: guest_management.php");
        exit();
    }
    
    // Get passengers with seat info
    $passengers = $conn->query("
        SELECT p.*, 
        GROUP_CONCAT(sb.seat_number SEPARATOR ', ') as seat_numbers
        FROM passengers p
        LEFT JOIN seats_booked sb ON p.passenger_id = sb.passenger_id
        WHERE p.booking_id = $booking_id
        GROUP BY p.passenger_id
    ");
}

// Fetch all guest bookings for listing
$guest_bookings = $conn->query("
    SELECT b.booking_id, b.booking_date, b.confirmation_code,
    COUNT(p.passenger_id) as passenger_count,
    GROUP_CONCAT(p.full_name SEPARATOR ', ') as passenger_names
    FROM bookings b
    JOIN passengers p ON b.booking_id = p.booking_id
    WHERE b.user_id IS NULL
    GROUP BY b.booking_id
    ORDER BY b.booking_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Guest Management</title>
</head>
<body>
    <div class="container mt-4">
        <h2>Guest Management</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if ($booking_id && $booking): ?>
            <!-- Guest Booking Detail View -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Booking #<?= $booking['booking_id'] ?> (Guest)</h4>
                    <span class="badge bg-primary"><?= $booking['confirmation_code'] ?></span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Departure Flight</h5>
                            <p>
                                <?= $booking['dep_from'] ?> → <?= $booking['dep_to'] ?><br>
                                <?= date('M d, Y', strtotime($booking['dep_date'])) ?> at <?= substr($booking['dep_time'], 0, 5) ?>
                            </p>
                        </div>
                        <?php if ($booking['return_flight_id']): ?>
                        <div class="col-md-6">
                            <h5>Return Flight</h5>
                            <p>
                                <?= $booking['ret_from'] ?> → <?= $booking['ret_to'] ?><br>
                                <?= date('M d, Y', strtotime($booking['ret_date'])) ?> at <?= substr($booking['ret_time'], 0, 5) ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <h5>Passenger Information</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Gender</th>
                                    <th>Seat(s)</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($passenger = $passengers->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($passenger['full_name']) ?></td>
                                    <td><?= $passenger['gender'] ?></td>
                                    <td><?= $passenger['seat_numbers'] ?: 'Not assigned' ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary edit-passenger-btn"
                                                data-bs-toggle="modal" data-bs-target="#editPassengerModal"
                                                data-passenger-id="<?= $passenger['passenger_id'] ?>"
                                                data-full-name="<?= htmlspecialchars($passenger['full_name']) ?>"
                                                data-gender="<?= $passenger['gender'] ?>">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <a href="guest_management.php" class="btn btn-secondary">Back to All Guests</a>
            
            <!-- Edit Passenger Modal -->
            <div class="modal fade" id="editPassengerModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Passenger</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
                                <input type="hidden" name="passenger_id" id="modalPassengerId" value="">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="modalFullName" name="full_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-select" id="modalGender" name="gender" required>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Guest Bookings List -->
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
                        <?php while ($guest = $guest_bookings->fetch_assoc()): ?>
                        <tr>
                            <td><span class="badge bg-primary"><?= $guest['confirmation_code'] ?></span></td>
                            <td>
                                <?= htmlspecialchars($guest['passenger_names']) ?>
                                <span class="badge bg-secondary"><?= $guest['passenger_count'] ?></span>
                            </td>
                            <td><?= date('M d, Y H:i', strtotime($guest['booking_date'])) ?></td>
                            <td>
                                <a href="guest_management.php?id=<?= $guest['booking_id'] ?>" class="btn btn-sm btn-primary">
                                    Manage
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script>
        // Handle edit passenger modal
        document.querySelectorAll('.edit-passenger-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('modalPassengerId').value = this.dataset.passengerId;
                document.getElementById('modalFullName').value = this.dataset.fullName;
                document.getElementById('modalGender').value = this.dataset.gender;
            });
        });
    </script>
</body>
</html>