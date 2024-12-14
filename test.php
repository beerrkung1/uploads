<?php
$directory = 'D:/Project Data/2567/project/';
try {
    if (is_dir($directory)) {
        $files = scandir($directory);
        echo "ไฟล์ใน D: <br>";
        foreach ($files as $file) {
            if ($file !== "." && $file !== "..") {
                echo $file . "<br>";
            }
        }
    } else {
        throw new Exception("ไม่สามารถเข้าถึงไดรฟ์ D: ได้");
    }
} catch (Exception $e) {
    echo "ข้อผิดพลาด: " . $e->getMessage();
}
?>
