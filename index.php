<link rel="stylesheet" href="css/styles.css">
<?php
// index.php - UPDATED ORDER
require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/auth.php';
require_once 'includes/db.php';
include 'includes/navbar.php';

// // Debug code
// echo "<!-- DEBUG: ";
// echo "Logged in: " . (isset($_SESSION['logged_in']) ? 'YES' : 'NO');
// echo " | Role: " . ($_SESSION['role'] ?? 'guest');
// echo " -->";

// Fetch all unique locations (both origin and destination)
$query = "SELECT DISTINCT departure AS location FROM flights
          UNION
          SELECT DISTINCT arrival AS location FROM flights";
$result = mysqli_query($conn, $query);

// Store results in array
$locations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $locations[] = $row['location'];
}
?>

<!-- Hero Section -->
<section class="hero">
  <div class="hero-overlay"></div>
  <div class="hero-content container">
    <h1 class="mb-4">Your Gateway to Global Destinations</h1>

    <form action="booking/booking_process.php" method="POST" class="booking-form p-4 rounded">
    <div class="mb-3">
  <div class="form-check form-check-inline">
    <input class="form-check-input" type="radio" name="trip_type" id="oneway" value="oneway" checked>
    <label class="form-check-label" for="oneway">One-way</label>
  </div>
  <div class="form-check form-check-inline">
    <input class="form-check-input" type="radio" name="trip_type" id="return" value="return">
    <label class="form-check-label" for="return">Return</label>
  </div>
</div>

      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">From</label>
          <select name="from" class="form-select" required>
  <option value="" disabled selected>Select origin</option>
  <?php foreach ($locations as $loc): ?>
    <option value="<?= htmlspecialchars($loc) ?>"><?= htmlspecialchars($loc) ?></option>
  <?php endforeach; ?>
</select>

        </div>

        <div class="col-md-3">
          <label class="form-label">To</label>
          <select name="to" class="form-select" required>
  <option value="" disabled selected>Select destination</option>
  <?php foreach ($locations as $loc): ?>
    <option value="<?= htmlspecialchars($loc) ?>"><?= htmlspecialchars($loc) ?></option>
  <?php endforeach; ?>
</select>

        </div>

        <div class="col-md-2">
          <label class="form-label">Departure</label>
          <input type="date" name="departure_date" class="form-control" required>
        </div>

        <div class="col-md-2" id="return-date-field">
  <label class="form-label">Return</label>
  <input type="date" name="return_date" class="form-control">
</div>

        <div class="col-md-2">
          <label class="form-label">Passengers</label>
          <input type="number" name="passengers" class="form-control" min="1" max="10" value="1" required>
      </div>

      <div class="text-center mt-4">
      <button type="submit" class="search-btn">Search Flights</button>
      </div>
    </form>
  </div>
</section>
<section class="destinations py-5">
  <div class="container">
    <h2 class="text-center mb-5">Featured Destinations</h2>
    <div class="row g-4">
      <!-- Destination 1 -->
      <div class="col-md-4">
        <div class="destination-card">
          <img src="images/paris.png" alt="Paris" class="img-fluid">
          <div class="destination-overlay">
            <h3>Paris</h3>
            <p>From $399</p>
          </div>
        </div>
      </div>
      <!-- Destination 2 -->
      <div class="col-md-4">
        <div class="destination-card">
          <img src="images/tokyo.png" alt="Tokyo" class="img-fluid">
          <div class="destination-overlay">
            <h3>Tokyo</h3>
            <p>From $899</p>
          </div>
        </div>
      </div>
      <!-- Destination 3 -->
      <div class="col-md-4">
        <div class="destination-card">
          <img src="images/spain.png" alt="New York" class="img-fluid">
          <div class="destination-overlay">
            <h3>Spain</h3>
            <p>From $499</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="testimonials py-5 bg-light">
  <div class="container">
    <h2 class="text-center mb-5">Traveler Experiences</h2>
    <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <!-- Testimonial 1 -->
        <div class="carousel-item active">
          <div class="testimonial-card mx-auto">
            <div class="quote-icon">“</div>
            <p class="testimonial-text">The premium service made my business trip effortless. Will fly again!</p>
            <div class="traveler-info">
              <img src="images/traveler1.png" class="traveler-img">
              <div>
                <h5>Michael Chen</h5>
                <p>Frequent Flyer</p>
              </div>
            </div>
          </div>
        </div>
        <!-- Testimonial 2 -->
        <div class="carousel-item">
          <div class="testimonial-card mx-auto">
            <div class="quote-icon">“</div>
            <p class="testimonial-text">Best legroom in economy class I've ever experienced.</p>
            <div class="traveler-info">
              <img src="images/traveler2.png" class="traveler-img">
              <div>
                <h5>Sarah Johnson</h5>
                <p>Family Traveler</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
      </button>
    </div>
  </div>
</section>
<section class="benefits py-5">
  <div class="container">
    <h2 class="text-center mb-5">Why Choose AirG6</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="benefit-card text-center">
          <div class="benefit-icon">✈️</div>
          <h3>Modern Fleet</h3>
          <p>Newest aircraft with premium comforts</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="benefit-card text-center">
          <div class="benefit-icon">🕒</div>
          <h3>On Time</h3>
          <p>90% on-time arrival record</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="benefit-card text-center">
          <div class="benefit-icon">🌟</div>
          <h3>5-Star Service</h3>
          <p>Award-winning cabin crew</p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="main.js"></script>
