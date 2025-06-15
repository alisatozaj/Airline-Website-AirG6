<?php
// booking_summary.php
if (!isset($booking)) {
    $booking = $_SESSION['booking'] ?? [];
}

if (!isset($conn)) {
    include '../includes/db.php';
}

// Get passenger count - use either:
// 1. Count from passengers_info array (if available)
// 2. The passengers number from search form (if available)
// 3. Default to 1
$passenger_count = isset($booking['passengers_info']) ? count($booking['passengers_info']) : 
                  (isset($booking['passengers']) ? (int)$booking['passengers'] : 1);

$total_price = 0;

if (isset($booking['flight_id'])) {
    $stmt = $conn->prepare("SELECT * FROM flights WHERE id = ?");
    $stmt->bind_param("i", $booking['flight_id']);
    $stmt->execute();
    $flight = $stmt->get_result()->fetch_assoc();
    $flight_price = $flight['price'] ?? 0;
    $total_price += $flight_price * $passenger_count;
}

if (isset($booking['trip_type']) && $booking['trip_type'] === 'return' && isset($booking['return_flight_id'])) {
    $stmt = $conn->prepare("SELECT * FROM flights WHERE id = ?");
    $stmt->bind_param("i", $booking['return_flight_id']);
    $stmt->execute();
    $return_flight = $stmt->get_result()->fetch_assoc();
    $return_flight_price = $return_flight['price'] ?? 0;
    $total_price += $return_flight_price * $passenger_count;
}

// Store the correctly calculated total price in session
$_SESSION['booking']['total_price'] = $total_price;
?>

<div class="card shadow-sm p-3 mb-4">
    <h5 class="fw-bold d-flex justify-content-between align-items-center">
        <span>FLIGHTS</span>
        <span>EUR <?= number_format($total_price, 2) ?></span>
    </h5>

    <?php if (isset($flight)): ?>
        <div class="flight-summary-box d-flex align-items-center mb-2">
            <div class="date-box text-center me-3">
                <div class="month"><?= strtoupper(date('M', strtotime($flight['departure_date']))) ?></div>
                <div class="day"><?= date('d', strtotime($flight['departure_date'])) ?></div>
            </div>
            <div class="flex-grow-1">
                <div class="route fw-bold"><?= $flight['departure'] ?> - <?= $flight['arrival'] ?></div>
                <div class="times small"><?= substr($flight['departure_time'], 0, 5) ?> - <?= substr($flight['arrival_time'], 0, 5) ?></div>
            </div>
        </div>
        <p><?= $passenger_count ?> × Flight Ticket <span class="float-end fw-bold">EUR <?= number_format(($flight['price'] ?? 0) * $passenger_count, 2) ?></span></p>
    <?php else: ?>
        <p class="text-muted">No departure flight selected.</p>
    <?php endif; ?>

    <?php if (isset($booking['trip_type']) && $booking['trip_type'] === 'return'): ?>
        <?php if (isset($return_flight)): ?>
            <div class="flight-summary-box d-flex align-items-center mb-2">
                <div class="date-box text-center me-3">
                    <div class="month"><?= strtoupper(date('M', strtotime($return_flight['departure_date']))) ?></div>
                    <div class="day"><?= date('d', strtotime($return_flight['departure_date'])) ?></div>
                </div>
                <div class="flex-grow-1">
                    <div class="route fw-bold"><?= $return_flight['departure'] ?> - <?= $return_flight['arrival'] ?></div>
                    <div class="times small"><?= substr($return_flight['departure_time'], 0, 5) ?> - <?= substr($return_flight['arrival_time'], 0, 5) ?></div>
                </div>
            </div>
            <p><?= $passenger_count ?> × Flight Ticket <span class="float-end fw-bold">EUR <?= number_format(($return_flight['price'] ?? 0) * $passenger_count, 2) ?></span></p>
        <?php else: ?>
            <p class="text-muted">No return flight selected.</p>
        <?php endif; ?>
    <?php endif; ?>

    <h5 class="fw-bold mt-4">PASSENGERS</h5>
<?php if (isset($booking['passengers_info']) && is_array($booking['passengers_info'])): ?>
    <div class="passenger-list">
        <?php foreach ($booking['passengers_info'] as $i => $p): ?>
            <div class="passenger-item d-flex align-items-center mb-2">
                <?php if ($p['gender'] === 'Male'): ?>
                    <i class="fas fa-mars text-primary me-2"></i>
                <?php else: ?>
                    <i class="fas fa-venus text-danger me-2"></i>
                <?php endif; ?>
                <span class="passenger-name"><?= htmlspecialchars($p['full_name']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
<?php elseif (isset($booking['passengers'])): ?>
    <p class="text-muted"><i class="fas fa-users me-2"></i><?= $booking['passengers'] ?> passenger(s) - details not filled yet</p>
<?php else: ?>
    <p class="text-muted"><i class="fas fa-info-circle me-2"></i>Not filled yet.</p>
<?php endif; ?>

<h5 class="fw-bold mt-4">BAGS</h5>
<?php if (isset($booking['bags'])): ?>
    <div class="baggage-summary bg-light rounded p-3">
        <div class="d-flex align-items-center mb-2">
            <i class="fas fa-briefcase text-primary me-3" style="width: 24px; text-align: center;"></i>
            <div>
                <span class="d-block">1 × Cabin Bag</span>
                <span class="text-muted small">Included</span>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <i class="fas fa-suitcase text-primary me-3" style="width: 24px; text-align: center;"></i>
            <div>
                <span class="d-block"><?= htmlspecialchars($booking['bags']) ?> × Checked Luggage</span>
                <span class="text-muted small">€<?= number_format(35 * $booking['bags'], 2) ?> total</span>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-light">No bags selected.</div>
<?php endif; ?>

<h5 class="fw-bold mt-4">SEATS</h5>
<?php if (isset($booking['seats']) && is_array($booking['seats'])): ?>
    <div class="seat-summary">
        <?php foreach ($booking['seats'] as $index => $seat): ?>
            <div class="d-flex align-items-center mb-2">
                <div class="seat-badge me-2">
                    <?= $seat ?>
                </div>
                <span>Passenger <?= $index + 1 ?>: <?= htmlspecialchars($booking['passengers_info'][$index]['full_name']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="text-muted"><i class="fas fa-info-circle me-2"></i>Seats not selected.</p>
<?php endif; ?>
</div>