<link rel="stylesheet" href="../css/styles.css">
<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();

include '../includes/navbar.php';

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

    // Calculate duration if not provided
    if (empty($duration)) {
        $start = new DateTime($departure_time);
        $end = new DateTime($arrival_time);
        $diff = $start->diff($end);
        $duration = $diff->format('%hh %im');
    }

    $stmt = $conn->prepare("INSERT INTO flights 
                      (departure, arrival, departure_date, departure_time, arrival_time, price, airline, seats_available, duration)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssis", $departure, $arrival, $departure_date, $departure_time, $arrival_time, $price, $airline, $seats, $duration);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Flight added successfully!";
        header("Location: flights.php");
        exit();
    } else {
        $error = "Error adding flight: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Flight</title>
</head>
<body>
    <div class="container mt-4">
        <h2>Add New Flight</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="departure" class="form-label">Departure City</label>
                        <input type="text" class="form-control" id="departure" name="departure" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="arrival" class="form-label">Arrival City</label>
                        <input type="text" class="form-control" id="arrival" name="arrival" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="departure_date" class="form-label">Departure Date</label>
                        <input type="date" class="form-control" id="departure_date" name="departure_date" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="departure_time" class="form-label">Departure Time</label>
                        <input type="time" class="form-control" id="departure_time" name="departure_time" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="arrival_time" class="form-label">Arrival Time</label>
                        <input type="time" class="form-control" id="arrival_time" name="arrival_time" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="price" class="form-label">Price (€)</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="airline" class="form-label">Airline</label>
                        <input type="text" class="form-control" id="airline" name="airline" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="seats" class="form-label">Available Seats</label>
                        <input type="number" class="form-control" id="seats" name="seats" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="duration" class="form-label">Duration (e.g., 2h 30m)</label>
                <input type="text" class="form-control" id="duration" name="duration">
                <small class="text-muted">Leave blank to calculate automatically</small>
            </div>

            <button type="submit" class="btn btn-primary">Add Flight</button>
            <a href="flights.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="../main.js"></script>
</body>
</html>