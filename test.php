<?php
$directory = 'D:/';

try {
    if (is_dir($directory)) {
        $files = scandir($directory);
        echo "<h1>รายการไฟล์และโฟลเดอร์ในไดรฟ์ D:</h1><ul>";
        foreach ($files as $file) {
            if ($file !== "." && $file !== "..") {
                echo "<li>$file</li>";
            }
        }
        echo "</ul>";
    } else {
        throw new Exception("ไม่สามารถเข้าถึงไดรฟ์ D: ได้ หรือไม่มีไดรฟ์นี้ในระบบ");
    }
} catch (Exception $e) {
    echo "ข้อผิดพลาด: " . $e->getMessage();
}
?>
