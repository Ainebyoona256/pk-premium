<?php
// includes/subscription_check.php
// Checks subscription status and locks site for expired non-admin users

if (!isset($conn) || !($conn instanceof mysqli)) {
    return;
}

try {
    $result = $conn->query("SELECT subscription_expiry FROM settings WHERE id=1");
    if (!$result) {
        return;
    }
    
    $row = $result->fetch_assoc();
    if (!$row) {
        return;
    }
    
    $expiry = $row['subscription_expiry'];
    $days_left = floor((strtotime($expiry) - time()) / 86400);
    
    $IS_ADMIN = isset($_SESSION['admin']);
    
    if (!$IS_ADMIN && $days_left < 0) {
        die('<div style="background:#000;color:#D4AF37;height:100vh;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;font-family:Poppins,sans-serif;"><h1 style="font-size:2rem;">PK PREMIUM STYLES AND SCENTS</h1><p style="margin-top:1rem;font-size:1.2rem;">This store is temporarily closed.</p><p style="margin-top:0.5rem;">For renewal contact ' . htmlspecialchars(RENEWAL_CONTACT) . '</p></div>');
    }
    
    if ($IS_ADMIN && $days_left <= 2 && $days_left >= 0) {
        $_SESSION['sub_warning'] = "Subscription expires in " . $days_left . " day" . ($days_left == 1 ? '' : 's') . ". Contact " . RENEWAL_CONTACT . " to renew";
    } elseif ($IS_ADMIN && $days_left < 0) {
        $_SESSION['sub_warning'] = "Subscription EXPIRED " . abs($days_left) . " days ago. Contact " . RENEWAL_CONTACT . " to renew immediately";
    }
} catch (Exception $e) {
    // Fail gracefully - allow site to load
}

?>