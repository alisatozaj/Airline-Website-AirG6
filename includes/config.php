<?php
// config.php - UPDATED VERSION

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'airline');

// Website base path 
define('BASE_PATH', '/airline_website');

// Session configuration - ONLY RUN IF SESSION NOT STARTED
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 86400); // 1 day
    ini_set('session.gc_maxlifetime', 86400);  // 1 day
    session_set_cookie_params(86400); // Simpler version for localhost
}