<link rel="stylesheet" href="../css/styles.css">
<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
include '../includes/navbar.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    
    // Simple validation
    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['error'] = "Please fill in all fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address";
    } else {
        // In a real application, you would:
        // 1. Save to database
        // 2. Send email notification
        // 3. Redirect to thank you page
        
        $_SESSION['message'] = "Thank you for your message! We'll get back to you soon.";
        header("Location: pages/contact.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Us</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .contact-hero {
            background-color: #f8f9fa;
            padding: 60px 0;
            margin-bottom: 30px;
            text-align: center;
        }
        .contact-icon {
            font-size: 2rem;
            color: #0d6efd;
            margin-bottom: 15px;
        }
        .contact-info {
            text-align: center;
            padding: 20px;
        }
        .contact-form {
            max-width: 600px;
            margin: 0 auto;
        }
        .map-container {
            height: 300px;
            background-color: #eee;
            margin-top: 30px;
            border-radius: 5px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <h1><i class="fas fa-envelope"></i> Contact Us</h1>
            <p class="lead">We're here to help with any questions</p>
        </div>
    </section>

    <div class="container mb-5">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="row">
            <!-- Contact Information -->
            <div class="col-md-4 mb-4">
                <div class="contact-info">
                    <div class="contact-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <h4>Call Us</h4>
                    <p>+1 (800) 123-4567</p>
                    <p>24/7 Customer Support</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="contact-info">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h4>Email Us</h4>
                    <p>support@</p>
                    <p>help@</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="contact-info">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h4>Visit Us</h4>
                    <p>123 Aviation Way</p>
                    <p>New York, NY 10001</p>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="contact-form">
            <h3 class="text-center mb-4">Send Us a Message</h3>
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="message" class="form-label">Your Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </div>
            </form>
        </div>

        <!-- Map Placeholder -->
        <div class="map-container mt-5">
            <!-- In a real implementation, you would embed Google Maps here -->
            <div style="height: 100%; display: flex; align-items: center; justify-content: center;">
                <div class="text-center">
                    <i class="fas fa-map-marked-alt" style="font-size: 3rem; color: #666;"></i>
                    <p class="mt-2">Map would display here</p>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>