<link rel="stylesheet" href="../css/styles.css">
<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
include '../includes/navbar.php';

$step = $_POST['step'] ?? 'flights';

// Save search input into session
if ($step === 'flights' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $_SESSION['booking'] = [
    'from' => $_POST['from'] ?? '',
    'to' => $_POST['to'] ?? '',
    'departure_date' => $_POST['departure_date'] ?? '',
    'return_date' => $_POST['return_date'] ?? '',
    'passengers' => $_POST['passengers'] ?? '',
    'trip_type' => $_POST['trip_type'] ?? 'one-way'
  ];
}

if ($step === 'passengers') {
  $_SESSION['booking']['flight_id'] = $_POST['flight_id'] ?? null;
  $_SESSION['booking']['return_flight_id'] = $_POST['return_flight_id'] ?? null;
}

if ($step === 'bags' && isset($_POST['full_name']) && is_array($_POST['full_name'])) {
  $passengers = [];
  foreach ($_POST['full_name'] as $index => $name) {
    $gender = $_POST['gender'][$index] ?? '';
    $passengers[] = [
      'full_name' => $name,
      'gender' => $gender
    ];
  }
  $_SESSION['booking']['passengers_info'] = $passengers;
}

if ($step === 'seats' && isset($_POST['bags'])) {
  $_SESSION['booking']['bags'] = $_POST['bags'];
}

if ($step === 'confirmation' && isset($_POST['seat']) && is_array($_POST['seat'])) {
  $_SESSION['booking']['seats'] = $_POST['seat'];

  $booking = $_SESSION['booking'] ?? [];
  $user_id = $_SESSION['user_id'] ?? null;
  $departure_flight_id = $booking['flight_id'] ?? null;
  $return_flight_id = $booking['return_flight_id'] ?? null;
  $trip_type = $booking['trip_type'] ?? 'one-way';
  $passengers = $booking['passengers_info'] ?? [];
  $seats = $booking['seats'] ?? [];
  
  // Get passenger count
  $passenger_count = count($passengers);
  
  // Get flight prices and calculate total
  $total_price = 0;
  
  // Get departure flight price
  if ($departure_flight_id) {
      $stmt = $conn->prepare("SELECT price FROM flights WHERE id = ?");
      $stmt->bind_param("i", $departure_flight_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $departure_flight = $result->fetch_assoc();
      $stmt->close();
      
      if ($departure_flight) {
          $total_price += $departure_flight['price'] * $passenger_count;
      }
  }
  
  // Get return flight price if exists
  if ($trip_type === 'return' && $return_flight_id) {
      $stmt = $conn->prepare("SELECT price FROM flights WHERE id = ?");
      $stmt->bind_param("i", $return_flight_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $return_flight = $result->fetch_assoc();
      $stmt->close();
      
      if ($return_flight) {
          $total_price += $return_flight['price'] * $passenger_count;
      }
  }

  // Generate unique confirmation code
  $confirmation_code = generate_confirmation_code($conn);
  
  // 1. Create booking with confirmation code and passenger count
  $stmt = $conn->prepare("INSERT INTO bookings 
                        (user_id, departure_flight_id, return_flight_id, total_price, passenger_count, booking_date, confirmation_code) 
                        VALUES (?, ?, ?, ?, ?, NOW(), ?)");
  $stmt->bind_param("iiiids", $user_id, $departure_flight_id, $return_flight_id, $total_price, $passenger_count, $confirmation_code);
  if (!$departure_flight_id) {
    header("Location: booking/booking_process.php");
    exit;
}
  $stmt->execute();
  $booking_id = $stmt->insert_id;
  $stmt->close();

  // 2. Save passengers + get their IDs
  $passenger_ids = [];
  foreach ($passengers as $i => $p) {
      $full_name = $p['full_name'];
      $gender = $p['gender'];
      $stmt = $conn->prepare("INSERT INTO passengers (booking_id, full_name, gender) VALUES (?, ?, ?)");
      $stmt->bind_param("iss", $booking_id, $full_name, $gender);
      $stmt->execute();
      $passenger_ids[] = $stmt->insert_id;
      $stmt->close();
  }

  // 3. Save seats for each flight
  $flight_ids = [$departure_flight_id];
  if ($trip_type === 'return' && $return_flight_id) {
      $flight_ids[] = $return_flight_id;
  }

  foreach ($flight_ids as $flight_index => $flight_id) {
      foreach ($passenger_ids as $i => $passenger_id) {
          $seat_number = $seats[$i + ($flight_index * count($passenger_ids))] ?? null;
          if ($seat_number) {
              $stmt = $conn->prepare("INSERT INTO seats_booked (flight_id, passenger_id, seat_number) VALUES (?, ?, ?)");
              $stmt->bind_param("iis", $flight_id, $passenger_id, $seat_number);
              $stmt->execute();
              $stmt->close();
          }
      }
  }
  
  // Show confirmation message with the code
  echo "<div class='container mt-5'>";
  echo "<div class='alert alert-success text-center'>";
  echo "<h4>Booking Confirmed!</h4>";
  echo "<p>Your Booking ID: <strong>$booking_id</strong></p>";
  echo "<p>Confirmation Code: <strong class='text-primary'>$confirmation_code</strong></p>";
  echo "<p>Please save this code to manage your booking later.</p>";
  echo "<a href='../pages/my_trips.php' class='btn btn-primary mt-3'>Go to My Trips</a>";
  echo "</div>";
  echo "</div>";

  // Clear the booking session after confirmation
  unset($_SESSION['booking']);
  exit(); // Stop further execution
}

$booking = $_SESSION['booking'] ?? [];

function render_progress($current_step) {
  $steps = ['flights', 'passengers', 'bags', 'seats', 'confirmation']; // Changed to go straight to confirmation
  echo '<div class="progress-bar-wrapper mb-4 text-center">';
  foreach ($steps as $index => $s) {
    $class = $s === $current_step ? 'fw-bold text-primary' : 'text-muted';
    echo "<span class='$class'>" . ucfirst($s) . "</span>";
    if ($index < count($steps) - 1) {
      echo " &rarr; ";
    }
  }
  echo '</div>';
}
?>
<div class="container mt-5">
  <?php render_progress($step); ?>

  <div class="row">
    <!-- Booking Summary (LEFT panel) -->
    <div class="col-md-4">    
      <?php include 'booking_summary.php';?>
    </div>

    <!-- Form Content (RIGHT panel) -->
    <div class="col-md-8">
      <?php if ($step === 'flights'): ?>
        <?php
          $from = $booking['from'] ?? '';
          $to = $booking['to'] ?? '';
          $departure = $booking['departure_date'] ?? '';
          $return = $booking['return_date'] ?? '';
          $passengers = $booking['passengers'] ?? 1;
          $trip_type = $booking['trip_type'] ?? 'one-way';
        ?>

        <form method="POST">
          <input type="hidden" name="step" value="passengers">

          <h4>Departure Flights</h4>
          <?php
            $sql = "SELECT * FROM flights WHERE departure = ? AND arrival = ? AND departure_date = ? AND seats_available >= ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $from, $to, $departure, $passengers);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0):
              while ($row = $result->fetch_assoc()): ?>
                <div class='card p-3 mb-3'>
                  <div class='form-check'>
                    <input class='form-check-input' type='checkbox' name='flight_id' value='<?= $row['id'] ?>' <?= isset($booking['flight_id']) && $booking['flight_id'] == $row['id'] ? 'checked' : '' ?> required>
                    <label class='form-check-label w-100'>
                      <div class="d-flex justify-content-between">
                        <strong><?= $row['departure_date'] ?></strong>
                        <strong>EUR <?= $row['price'] ?></strong>
                      </div>
                      <div class="d-flex justify-content-between fs-5 fw-bold">
                        <span><?= substr($row['departure_time'], 0, 5) ?></span>
                        <span>→</span>
                        <span><?= substr($row['arrival_time'], 0, 5) ?></span>
                      </div>
                      <div class="text-muted small">From <?= $row['departure'] ?> to <?= $row['arrival'] ?> via <?= $row['airline'] ?></div>
                    </label>
                  </div>
                </div>
              <?php endwhile;
            else:
              echo "<p>No departure flights found.</p>";
            endif;
          ?>

          <?php if ($trip_type === 'return'): ?>
            <h4 class="mt-4">Return Flights</h4>
            <?php
              $sql = "SELECT * FROM flights WHERE departure = ? AND arrival = ? AND departure_date = ? AND seats_available >= ?";
              $stmt = $conn->prepare($sql);
              $stmt->bind_param("sssi", $to, $from, $return, $passengers);
              $stmt->execute();
              $result = $stmt->get_result();
              if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()): ?>
                  <div class='card p-3 mb-3'>
                    <div class='form-check'>
                      <input class='form-check-input' type='checkbox' name='return_flight_id' value='<?= $row['id'] ?>' <?= isset($booking['return_flight_id']) && $booking['return_flight_id'] == $row['id'] ? 'checked' : '' ?> required>
                      <label class='form-check-label w-100'>
                        <div class="d-flex justify-content-between">
                        <span class="flight-date-box"><?= $row['departure_date'] ?></span>
                          <strong>EUR <?= $row['price'] ?></strong>
                        </div>
                        <div class="d-flex justify-content-between fs-5 fw-bold">
                          <span><?= substr($row['departure_time'], 0, 5) ?></span>
                          <span>→</span>
                          <span><?= substr($row['arrival_time'], 0, 5) ?></span>
                        </div>
                        <div class="text-muted small">From <?= $row['departure'] ?> to <?= $row['arrival'] ?> via <?= $row['airline'] ?></div>
                      </label>
                    </div>
                  </div>
                <?php endwhile;
              else:
                echo "<p>No return flights found.</p>";
              endif;
            ?>
          <?php endif; ?>

          <button type="submit" class="btn btn-primary">Continue to Passengers</button>
        </form>

        <?php elseif ($step === 'passengers'): ?>
    <?php $passenger_count = isset($_SESSION['booking']['passengers']) ? intval($_SESSION['booking']['passengers']) : 1; ?>

    <h2 class="mb-4">Passenger Details</h2>
<form method="POST">
    <input type="hidden" name="step" value="bags">
    
    <?php for ($i = 1; $i <= $passenger_count; $i++): ?>
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title d-flex align-items-center">
                    <i class="fas fa-user me-2 text-muted"></i>
                    Passenger <?= $i ?>
                </h5>
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name[]" class="form-control" pattern="[A-Za-z\s]+" title="Only letters and spaces allowed" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gender</label>
                    <div class="d-flex gap-3">
                        <div class="form-check gender-option">
                            <input class="form-check-input" type="radio" name="gender[<?= $i-1 ?>]" id="male<?= $i ?>" value="Male" required>
                            <label class="form-check-label" for="male<?= $i ?>">
                                <i class="fas fa-mars text-primary me-2"></i> Male
                            </label>
                        </div>
                        <div class="form-check gender-option">
                            <input class="form-check-input" type="radio" name="gender[<?= $i-1 ?>]" id="female<?= $i ?>" value="Female">
                            <label class="form-check-label" for="female<?= $i ?>">
                                <i class="fas fa-venus text-danger me-2"></i> Female
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endfor; ?>

    <button type="submit" class="btn btn-primary px-4 py-2">
        Continue to Bags <i class="fas fa-chevron-right ms-2"></i>
    </button>
</form>

<?php elseif ($step === 'bags'): ?>
    <h2 class="mb-4">Baggage Options</h2>
    <form method="POST">
        <input type="hidden" name="step" value="seats">
        
        <div class="baggage-options">
            <!-- Cabin Bag (included) -->
            <div class="baggage-type card mb-3 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-briefcase bag-icon me-3 text-primary"></i>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">Cabin Baggage</h5>
                            <p class="text-muted small mb-0">Max 10kg, 55×40×20cm</p>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary py-2 px-3">1 included</span>
                    </div>
                </div>
            </div>
            
            <!-- Checked Luggage (selectable) -->
            <div class="baggage-type card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-suitcase bag-icon me-3 text-primary"></i>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">Checked Luggage</h5>
                            <p class="text-muted small mb-0">Max 23kg, 158cm total dimensions</p>
                        </div>
                    </div>
                    
                    <div class="baggage-controls bg-light rounded p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-bold">Quantity:</span>
                                <span class="text-muted ms-2">(Max 3)</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <button type="button" class="btn btn-outline-primary btn-minus rounded-circle" onclick="updateBags(-1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" name="bags" id="bagsInput" class="form-control text-center mx-2" 
                                       value="<?= $booking['bags'] ?? 1 ?>" min="0" max="3" readonly
                                       style="width: 60px; font-weight: 600;">
                                <button type="button" class="btn btn-outline-primary btn-plus rounded-circle" onclick="updateBags(1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="text-end">
                                <span class="text-muted small">Price per bag:</span>
                                <span class="d-block fw-bold text-primary">€35.00</span>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top text-end">
                            <span class="text-muted">Total:</span>
                            <span class="fs-5 fw-bold ms-2" id="bagsTotal">€<?= number_format(35 * ($booking['bags'] ?? 1), 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-4 py-2">
                Continue to Seats <i class="fas fa-chevron-right ms-2"></i>
            </button>
        </div>
    </form>


    <?php elseif ($step === 'seats'): ?>
  <div class="container">
    <div class="row">
      <div class="col-12">
        <h2>Select Seats</h2>
        <form method="POST">
          <input type="hidden" name="step" value="confirmation">

          <?php
            $passengers = $_SESSION['booking']['passengers_info'] ?? [];
            $flight_id = $_SESSION['booking']['flight_id'] ?? null;
            $return_flight_id = $_SESSION['booking']['return_flight_id'] ?? null;
            $trip_type = $_SESSION['booking']['trip_type'] ?? 'one-way';

            // Fetch taken seats for both flights
            $taken_seats = [];
            $flight_ids = [$flight_id];
            
            if ($trip_type === 'return' && $return_flight_id) {
              $flight_ids[] = $return_flight_id;
            }

            if (!empty($flight_ids)) {
              $placeholders = implode(',', array_fill(0, count($flight_ids), '?'));
              $types = str_repeat('i', count($flight_ids));
              $stmt = $conn->prepare("SELECT seat_number FROM seats_booked WHERE flight_id IN ($placeholders)");
              $stmt->bind_param($types, ...$flight_ids);
              $stmt->execute();
              $result = $stmt->get_result();
              while ($row = $result->fetch_assoc()) {
                $taken_seats[] = $row['seat_number'];
              }
            }
          ?>

          <div class="seat-selection-container mt-4">
            <div class="airplane-cabin mb-4 p-3 border rounded">
              <div class="cockpit text-center py-2 bg-dark text-white rounded mb-3">
                <h4 class="m-0">Cockpit</h4>
              </div>
              
              <div class="seats-grid mb-3">
                <?php 
                $seat_rows = ['A', 'B', 'C'];
                foreach ($seat_rows as $row): ?>
                  <div class="row-label fw-bold text-center mb-2">Row <?php echo $row; ?></div>
                  <?php for ($i = 1; $i <= 6; $i++): 
                    $seat = $row . $i;
                    $is_taken = in_array($seat, $taken_seats);
                  ?>
                    <div class="seat-option text-center">
                      <input type="radio" name="seat[]" id="seat_<?php echo $seat; ?>" 
                             value="<?php echo $seat; ?>" <?php echo $is_taken ? 'disabled' : ''; ?>>
                      <label for="seat_<?php echo $seat; ?>" 
                             class="seat-label <?php echo $is_taken ? 'taken' : 'available'; ?>">
                        <?php echo $i; ?>
                      </label>
                    </div>
                  <?php endfor; ?>
                <?php endforeach; ?>
              </div>
              
              <div class="exit-row text-center py-2 bg-dark text-white rounded">
                <h4 class="m-0">Exit Row</h4>
              </div>
            </div>

            <div class="seat-legend d-flex justify-content-center gap-4 mb-4">
              <div class="legend-item d-flex align-items-center gap-2">
                <div class="seat-sample available"></div>
                <span>Available</span>
              </div>
              <div class="legend-item d-flex align-items-center gap-2">
                <div class="seat-sample taken"></div>
                <span>Taken</span>
              </div>
              <div class="legend-item d-flex align-items-center gap-2">
                <div class="seat-sample selected"></div>
                <span>Selected</span>
              </div>
            </div>

            <div class="passenger-seat-assignment bg-light p-3 rounded mb-4">
              <h4>Passenger Seat Assignment</h4>
              <?php foreach ($passengers as $i => $passenger): ?>
                <div class="passenger-assignment d-flex justify-content-between align-items-center py-2 
                    <?php echo $i < count($passengers) - 1 ? 'border-bottom' : ''; ?>">
                  <span>Passenger <?php echo $i + 1; ?>: <?php echo htmlspecialchars($passenger['name'] ?? 'Passenger'); ?></span>
                  <div class="selected-seat fw-bold text-primary" id="selected-seat-<?php echo $i; ?>">
                    Not selected yet
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <button type="submit" class="btn btn-primary mt-3">Confirm Booking</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const seatRadios = document.querySelectorAll('.seat-option input[type="radio"]');
      const passengerCount = <?php echo count($passengers); ?>;
      let currentPassenger = 0;
      
      seatRadios.forEach(radio => {
        radio.addEventListener('change', function() {
          const seatName = this.value;
          document.getElementById(`selected-seat-${currentPassenger}`).textContent = seatName;
          currentPassenger = (currentPassenger + 1) % passengerCount;
        });
      });
    });
  </script>
<?php endif; ?>

<script>
// Your existing baggage quantity control
function updateBags(change) {
    const input = document.getElementById("bagsInput");
    let newValue = parseInt(input.value) + change;
    newValue = Math.max(parseInt(input.min), Math.min(newValue, parseInt(input.max)));
    input.value = newValue;
    document.getElementById("bagsTotal").textContent = "€" + (35 * newValue).toFixed(2);
}
</script>
<?php include '../includes/footer.php'; ?>