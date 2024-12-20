<?php
session_start();
$config = include 'config.php';

header('Content-Type: application/json; charset=utf-8');

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$relative_path = $_POST['path'] ?? '';

if (strpos($relative_path, '..') !== false) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid path"]);
    exit;
}

$base_dir = rtrim($config['upload_directory'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$relative_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative_path);
$target_dir = $base_dir . $relative_path;

$real_base = realpath($config['upload_directory']);
$real_target = realpath($target_dir);

if ($real_target === false || strpos($real_target, $real_base) !== 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid directory"]);
    exit;
}

$dirs = [];
$scan = scandir($real_target);
foreach ($scan as $d) {
    if ($d !== '.' && $d !== '..') {
        $full_path = $real_target . DIRECTORY_SEPARATOR . $d;
        if (is_dir($full_path)) {
            $dirs[$d] = filemtime($full_path);
        }
    }
}

// เรียงลำดับโฟลเดอร์จากใหม่ไปเก่า ตาม filemtime
arsort($dirs); // เรียงโดย key คือชื่อโฟลเดอร์, value คือ time

$sorted_folders = array_keys($dirs);

echo json_encode(["folders" => $sorted_folders]);
?>