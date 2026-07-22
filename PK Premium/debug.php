<?php
// debug.php - Upload this to your root to diagnose the 500 error
// DELETE THIS FILE AFTER DEBUGGING

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PK Premium Debug Info</h2>";
echo "<pre>\n";

echo "1. PHP VERSION:\n";
echo phpversion() . "\n\n";

echo "2. MYSQLI EXTENSION:\n";
echo extension_loaded('mysqli') ? 'LOADED' : 'MISSING' . "\n\n";

echo "3. CURL EXTENSION:\n";
echo extension_loaded('curl') ? 'LOADED' : 'MISSING' . "\n\n";

echo "4. DATABASE CONNECTION TEST:\n";
$host = 'sql200.ezyro.com';
$user = 'ezyro_42471412';
$pass = '45f98f48b507d';
$db   = 'ezyro_42471412_pkpremium';

$conn = @new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo "FAILED: " . $conn->connect_error . "\n\n";
} else {
    echo "SUCCESS\n\n";
    
    echo "5. TABLES CHECK:\n";
    $tables = ['settings', 'admin', 'categories', 'products', 'offers'];
    foreach ($tables as $table) {
        $res = $conn->query("SHOW TABLES LIKE '$table'");
        echo $table . ': ' . ($res->num_rows > 0 ? 'EXISTS' : 'MISSING') . "\n";
    }
    echo "\n";
    
    echo "6. ADMIN CHECK:\n";
    $res = $conn->query("SELECT id, email FROM admin LIMIT 1");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        echo "Admin found: " . $row['email'] . "\n";
    } else {
        echo "No admin found - did you import db.sql?\n";
    }
    
    $conn->close();
}

echo "\n7. FILE CHECK:\n";
$files = [
    'config.php',
    'includes/functions.php',
    'includes/subscription_check.php',
    'includes/header.php',
    'includes/footer.php',
    'index.php',
    'product.php',
    'cart.php',
    'admin/login.php',
    'admin/dashboard.php',
    'admin/upload.php',
    'style.css',
    'db.sql'
];
foreach ($files as $f) {
    echo $f . ': ' . (file_exists($f) ? 'EXISTS' : 'MISSING') . "\n";
}

echo "\n</pre>";
echo "<p><strong>Once you find the issue, DELETE THIS FILE (debug.php) for security.</strong></p>";