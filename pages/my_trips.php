<link rel="stylesheet" href="../css/styles.css">
<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
include '../includes/navbar.php';
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4>My Trips</h4>
                </div>
                <div class="card-body">
                    <form action="../booking/view_booking.php" method="GET">  <!-- Changed to GET and corrected path -->
                        <div class="mb-3">
                            <label for="confirmation_code" class="form-label">Confirmation Code</label>
                            <input type="text" class="form-control" id="confirmation_code" 
                                   name="code" placeholder="e.g. ABC-123" required>  <!-- Changed name to "code" -->
                            <div class="form-text">Enter the code you received when booking</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Find My Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>