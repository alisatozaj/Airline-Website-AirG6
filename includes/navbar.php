<!DOCTYPE html>
<html>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="styles.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    </head>
    <body class="<?php echo (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'homepage' : 'other-page'; ?>">
    <?php require_once 'config.php';?>

    <?php 
    $is_homepage = (basename($_SERVER['PHP_SELF']) === 'index.php');
    $navbar_class = $is_homepage ? 'navbar-transparent' : 'navbar-blue';
    ?>

    <nav class="navbar navbar-expand-lg fixed-top <?php echo $navbar_class; ?>"> 
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_PATH ?>/index.php">AirG6</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" 
                           href="<?= BASE_PATH ?>/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'my_trips.php' ? 'active' : '' ?>" 
                           href="<?= BASE_PATH ?>/pages/my_trips.php">My Trips</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'about.php' ? 'active' : '' ?>" 
                           href="<?= BASE_PATH ?>/pages/about.php">About Us</a>
                    </li>

                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'admin/') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_PATH ?>/admin/dashboard.php">Admin Dashboard</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="d-flex">
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                        <a href="<?= BASE_PATH ?>/login.php" class="btn ms-3">Logout</a>
                    <?php else: ?>
                        <a href="<?= BASE_PATH ?>/login.php" class="btn ms-3">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>