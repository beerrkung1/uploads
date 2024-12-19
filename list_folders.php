<?php
session_start();
$config = include 'config.php';

// ตรวจสอบการล็อกอินก่อน
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// รับ path มาจาก AJAX
$relative_path = $_POST['path'] ?? '';

// ป้องกัน Path Traversal: ห้ามมี '..' ใน path
if (strpos($relative_path, '..') !== false) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid path"]);
    exit;
}

$base_dir = rtrim($config['upload_directory'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$target_dir = $base_dir . $relative_path;

// ตรวจสอบว่าเป็นไดเรกทอรีและอยู่ภายใต้ base_dir จริงๆ
// realpath จะช่วยตรวจสอบ path จริง ถ้าไม่สามารถ resolve จะ return false
$real_target = realpath($target_dir);
$real_base = realpath($config['upload_directory']);

if ($real_target === false || strpos($real_target, $real_base) !== 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid directory"]);
    exit;
}

// สแกนหา subfolder
$dirs = [];
$scan = scandir($real_target);
foreach ($scan as $d) {
    if ($d !== '.' && $d !== '..') {
        $full_path = $real_target . DIRECTORY_SEPARATOR . $d;
        if (is_dir($full_path)) {
            $dirs[] = $d;
        }
    }
}

// ส่งกลับเป็น JSON
echo json_encode(["folders" => $dirs]);
?>