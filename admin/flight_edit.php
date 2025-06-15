<link rel="stylesheet" href="../css/styles.css">
<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();

include '../includes/navbar.php';

if (!isset($_GET['id'])) {
    header("Location: flights.php");
    exit();
}

$flight_id = intval($_GET['id']);

// Fetch flight data
$stmt = $conn->prepare("SELECT * FROM flights WHERE id = ?");
$stmt->bind_param("i", $flight_id);
$stmt->execute();
$flight = $stmt->get_result()->fetch_assoc();

if (!$flight) {
    $_SESSION['error'] = "Flight not found";
    header("Location: flights.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $departure = trim($_POST['departure']);
    $arrival = trim($_POST['arrival']);
    $departure_date = $_POST['departure_date'];
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $price = floatval($_POST['price']);
    $airline = trim($_POST['airline']);
    $seats = intval($_POST['seats']);
    $duration = trim($_POST['duration']);

    $stmt = $conn->prepare("UPDATE flights SET 
                          departure = ?, arrival = ?, departure_date = ?, departure_time = ?, 
                          arrival_time = ?, price = ?, airline = ?, seats_available = ?, duration = ?
                          WHERE id = ?");
    $stmt->bind_param("sssssssisi", $departure, $arrival, $departure_date, $departure_time, 
                     $arrival_time, $price, $airline, $seats, $duration, $flight_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Flight updated successfully!";
        header("Location: flights.php");
        exit();
    } else {
        $error = "Error updating flight: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Flight</title>
</head>
<body>
    <div class="container mt-4">
        <h2>Edit Flight</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="departure" class="form-label">Departure City</label>
                        <input type="text" class="form-control" id="departure" name="departure" 
                               value="<?= htmlspecialchars($flight['departure']) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="arrival" class="form-label">Arrival City</label>
                        <input type="text" class="form-control" id="arrival" name="arrival" 
                               value="<?= htmlspecialchars($flight['arrival']) ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="departure_date" class="form-label">Departure Date</label>
                        <input type="date" class="form-control" id="departure_date" name="departure_date" 
                               value="<?= $flight['departure_date'] ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="departure_time" class="form-label">Departure Time</label>
                        <input type="time" class="form-control" id="departure_time" name="departure_time" 
                               value="<?= substr($flight['departure_time'], 0, 5) ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="arrival_time" class="form-label">Arrival Time</label>
                        <input type="time" class="form-control" id="arrival_time" name="arrival_time" 
                               value="<?= substr($flight['arrival_time'], 0, 5) ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="price" class="form-label">Price (€)</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" 
                               value="<?= $flight['price'] ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="airline" class="form-label">Airline</label>
                        <input type="text" class="form-control" id="airline" name="airline" 
                               value="<?= htmlspecialchars($flight['airline']) ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="seats" class="form-label">Available Seats</label>
                        <input type="number" class="form-control" id="seats" name="seats" 
                               value="<?= $flight['seats_available'] ?>" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="duration" class="form-label">Duration</label>
                <input type="text" class="form-control" id="duration" name="duration" 
                       value="<?= htmlspecialchars($flight['duration']) ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Update Flight</button>
            <a href="flights.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>