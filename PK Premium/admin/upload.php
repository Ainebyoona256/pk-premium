<?php
// admin/upload.php - Handles ImageKit file uploads via AJAX
require_once '../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
$folder = isset($_POST['folder']) ? sanitize($_POST['folder']) : IMAGEKIT_FOLDER;

$url = uploadToImageKit($file, $folder);

if ($url) {
    echo json_encode(['success' => true, 'url' => $url]);
} else {
    echo json_encode(['success' => false, 'message' => 'Upload failed. Please try again.']);
}

?>