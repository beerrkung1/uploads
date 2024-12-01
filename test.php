<?php
// ตั้งค่า path ไดรฟ์ D:
$directory = 'D:/';

// ตรวจสอบว่า path มีอยู่จริง
if (is_dir($directory)) {
    // อ่านรายการไฟล์และโฟลเดอร์ในไดรฟ์
    $files = scandir($directory);

    // แสดงผลไฟล์และโฟลเดอร์ในไดรฟ์ D:
    echo "<h1>รายการไฟล์และโฟลเดอร์ในไดรฟ์ D:</h1>";
    echo "<ul>";
    foreach ($files as $file) {
        if ($file !== "." && $file !== "..") {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
} else {
    echo "ไม่สามารถเข้าถึงไดรฟ์ D: ได้";
}
?>
