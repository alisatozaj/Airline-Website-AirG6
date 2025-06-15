<link rel="stylesheet" href="../css/styles.css">
<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
include '../includes/navbar.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>About Us</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .about-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../images/plane1.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-bottom: 40px;
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 15px;
        }
        .team-card {
            border: none;
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .team-card:hover {
            transform: translateY(-5px);
        }
        .team-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin: 0 auto 15px;
            border: 3px solid #0d6efd;
        }
        .stats-item {
            text-align: center;
            padding: 20px;
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <h1><i class="fas fa-plane"></i> About AirG6</h1>
            <p class="lead">Connecting the world with exceptional service since 2010</p>
        </div>
    </section>

    <div class="container">
        <!-- Our Story -->
        <section class="mb-5">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2>Our Story</h2>
                    <p>Founded in 2010, AirG6 began with a single aircraft and a big dream. Today, we operate a modern fleet serving over 50 destinations worldwide.</p>
                    <p>Our commitment to safety, comfort, and exceptional service has made us one of the fastest-growing airlines in the region.</p>
                    <a href="#" class="btn btn-outline-primary">Learn more about our history</a>
                </div>
                <div class="col-md-6">
                    <img src="../images/terminal.jpg" alt="Airport Terminal" class="img-fluid rounded">
                </div>
            </div>
        </section>

        <!-- Key Features -->
        <section class="mb-5 text-center">
            <h2 class="mb-4">Why Fly With Us</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Safety First</h4>
                    <p>Industry-leading safety standards with a perfect safety record since our founding.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-smile"></i>
                    </div>
                    <h4>Customer Satisfaction</h4>
                    <p>98% customer satisfaction rate based on post-flight surveys.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h4>Eco-Friendly</h4>
                    <p>Committed to carbon-neutral operations by 2025.</p>
                </div>
            </div>
        </section>

        <!-- Stats -->
        <section class="mb-5 bg-light py-4 rounded">
            <div class="row">
                <div class="col-md-3 stats-item">
                    <div class="stats-number">50+</div>
                    <div class="stats-label">Destinations</div>
                </div>
                <div class="col-md-3 stats-item">
                    <div class="stats-number">2M+</div>
                    <div class="stats-label">Passengers Yearly</div>
                </div>
                <div class="col-md-3 stats-item">
                    <div class="stats-number">150+</div>
                    <div class="stats-label">Daily Flights</div>
                </div>
                <div class="col-md-3 stats-item">
                    <div class="stats-number">12</div>
                    <div class="stats-label">Years in Service</div>
                </div>
            </div>
        </section>

        <!-- Team -->
        <section class="mb-5">
            <h2 class="text-center mb-4">Meet Our Leadership</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card team-card text-center p-3">
                        <img src="../images/team2.png" alt="CEO" class="team-img">
                        <h4>Sarah Johnson</h4>
                        <p class="text-muted">CEO & Founder</p>
                        <p>"Our vision is to make air travel accessible and enjoyable for everyone."</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card team-card text-center p-3">
                        <img src="../images/team1.png" alt="COO" class="team-img">
                        <h4>Michael Chen</h4>
                        <p class="text-muted">Chief Operations Officer</p>
                        <p>"We're committed to operational excellence in every flight."</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card team-card text-center p-3">
                        <img src="../images/team3.png" alt="CSO" class="team-img">
                        <h4>David Wilson</h4>
                        <p class="text-muted">Chief Safety Officer</p>
                        <p>"Safety isn't just our priority - it's our culture."</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="text-center mb-5">
            <h2>Ready to Fly With Us?</h2>
            <p class="lead mb-4">Experience the difference with AirG6</p>
            <a href="../index.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-search"></i> Book a Flight
            </a>
            <a href="../pages/contact.php" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-envelope"></i> Contact Us
            </a>
        </section>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>