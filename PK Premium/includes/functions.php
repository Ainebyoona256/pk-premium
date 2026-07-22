<?php
// includes/functions.php - Utility functions for the application

/**
 * Check if today is a weekend or Ugandan public holiday
 * @return bool
 */
function isWeekendOrHoliday() {
    $holidays = [
        '2026-01-01',
        '2026-02-18',
        '2026-03-08',
        '2026-04-03',
        '2026-04-10',
        '2026-04-18',
        '2026-04-19',
        '2026-05-01',
        '2026-06-03',
        '2026-06-09',
        '2026-06-14',
        '2026-10-10',
        '2026-12-25',
        '2026-12-26'
    ];
    
    $today = date('Y-m-d');
    $day = date('N');
    
    return ($day >= 6) || in_array($today, $holidays);
}

/**
 * Get active discount percentage
 * @param mysqli $conn
 * @return int
 */
function getActiveDiscount($conn) {
    if (isWeekendOrHoliday()) {
        return 20;
    }
    
    $today = date('Y-m-d');
    
    try {
        $stmt = $conn->prepare("SELECT discount_percent FROM offers WHERE is_active=1 AND ? BETWEEN start_date AND end_date LIMIT 1");
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res && $res->num_rows > 0) {
            return (int) $res->fetch_assoc()['discount_percent'];
        }
    } catch (Exception $e) {
        return 0;
    }
    
    return 0;
}

/**
 * Upload file to ImageKit
 * @param array $file $_FILES['file']
 * @param string $folder
 * @return string|false URL on success, false on failure
 */
function uploadToImageKit($file, $folder = IMAGEKIT_FOLDER) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }
    
    $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file['name']);
    $fileContent = base64_encode(file_get_contents($file['tmp_name']));
    
    // Generate signature for server-side upload
    $expiry = time() + 3600;
    $signature = hash('sha256', $expiry . IMAGEKIT_PRIVATE_KEY);
    
    $postFields = [
        'file' => $fileContent,
        'fileName' => $fileName,
        'folder' => $folder,
        'publicKey' => IMAGEKIT_PUBLIC_KEY,
        'signature' => $signature,
        'expire' => $expiry,
        'useUniqueFileName' => true
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, IMAGEKIT_UPLOAD_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && $response) {
        $data = json_decode($response, true);
        if ($data && isset($data['url'])) {
            return $data['url'];
        }
    }
    
    return false;
}

/**
 * Sanitize string input
 * @param string $str
 * @return string
 */
function sanitize($str) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($str))));
}

/**
 * Redirect to a URL
 * @param string $url
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Format price with currency
 * @param float $price
 * @return string
 */
function formatPrice($price) {
    return number_format((float) $price, 2) . ' UGX';
}

?>