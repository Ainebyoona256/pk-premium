<?php
// config.php - Central configuration and database connection
session_start();

// Database credentials
define('DB_HOST', 'sql200.ezyro.com');
define('DB_USER', 'ezyro_42471412');
define('DB_PASS', '45f98f48b507d');
define('DB_NAME', 'ezyro_42471412_pkpremium');

// ImageKit credentials
define('IMAGEKIT_PUBLIC_KEY', 'public_uz9710ionSVRvyJF0GVMG4K0DmA=');
define('IMAGEKIT_PRIVATE_KEY', 'private_xIzq1g5huyjt/jwCWNJw7cT1Zx0=');
define('IMAGEKIT_URL', 'https://ik.imagekit.io/pkstores');
define('IMAGEKIT_FOLDER', 'pk-premium');
define('IMAGEKIT_UPLOAD_URL', 'https://upload.imagekit.io/api/v1/files/upload');

// Business info
define('ADMIN_EMAIL', 'ntunguraphionahk@gmail.com');
define('ADMIN_PASSWORD', 'Phionah@26');
define('WHATSAPP', '256779686142');
define('CALLS', '0703504504');
define('RENEWAL_CONTACT', '+256760915873');
define('LOCATION', 'Nakawa / Kitende');
define('SUBSCRIPTION_EXPIRY', '2026-12-31');
define('SITE_URL', 'https://pkpremiumstylesandscents.com');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Include functional libraries
include __DIR__ . '/includes/subscription_check.php';
include __DIR__ . '/includes/functions.php';

?>