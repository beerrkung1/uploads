<?php
// Return JSON
header('Content-Type: application/json; charset=utf-8');

session_start();
// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode([
        "error" => "Unauthorized"
    ]);
    exit;
}

// รับ path มาตรวจ
$check_path = $_POST['check_path'] ?? '';

// ป้องกัน Path Traversal (เบื้องต้น)
if (strpos($check_path, '..') !== false) {
    echo json_encode([
        "exists" => false,
        "error" => "Invalid path"
    ]);
    exit;
}

// check_path เช่น "D:\Project Data\xxxx..."
$realpath = realpath($check_path);
if ($realpath === false) {
    // path ไม่มีจริง
    echo json_encode([
        "exists" => false
    ]);
} else {
    // path มีจริง
    echo json_encode([
        "exists" => true
    ]);
}
