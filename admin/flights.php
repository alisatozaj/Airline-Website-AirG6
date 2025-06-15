<link rel="stylesheet" href="../css/styles.css">
<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();

include '../includes/navbar.php';

// Handle flight deletion
if (isset($_GET['delete'])) {
    $flight_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM flights WHERE id = ?");
    $stmt->bind_param("i", $flight_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Flight deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting flight";
    }
    header("Location: flights.php");
    exit();
}

// Fetch all flights
$stmt = $conn->prepare("SELECT * FROM flights ORDER BY departure_date, departure_time");
$stmt->execute();
$flights = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Flights</title>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Flights</h2>
            <a href="flight_create.php" class="btn btn-success">Add New Flight</a>
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
                        <th>ID</th>
                        <th>Route</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Airline</th>
                        <th>Seats</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($flight = $flights->fetch_assoc()): ?>
                    <tr>
                        <td><?= $flight['id'] ?></td>
                        <td><?= $flight['departure'] ?> → <?= $flight['arrival'] ?></td>
                        <td><?= date('M d, Y', strtotime($flight['departure_date'])) ?></td>
                        <td><?= substr($flight['departure_time'], 0, 5) ?> - <?= substr($flight['arrival_time'], 0, 5) ?></td>
                        <td><?= $flight['airline'] ?></td>
                        <td><?= $flight['seats_available'] ?></td>
                        <td>€<?= number_format($flight['price'], 2) ?></td>
                        <td>
                            <a href="flight_edit.php?id=<?= $flight['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="flights.php?delete=<?= $flight['id'] ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this flight?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="../main.js"></script>
</body>
</html>