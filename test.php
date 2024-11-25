<?php
// ตั้งค่าโฟลเดอร์เริ่มต้นเป็นไดรฟ์ D:
$baseDir = 'D:\\';

// ฟังก์ชันเพื่อให้แน่ใจว่าเส้นทางไม่ออกนอกไดรฟ์ D:
function sanitizePath($path, $baseDir) {
    $realBase = realpath($baseDir);
    $realUserPath = realpath($path);
    
    if ($realUserPath === false || strpos($realUserPath, $realBase) !== 0) {
        return $realBase;
    }
    return $realUserPath;
}

// ตรวจสอบและรับข้อมูลจากฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetDir = isset($_POST['folder']) ? $_POST['folder'] : $baseDir;
    $targetDir = sanitizePath($targetDir, $baseDir);

    if (!is_dir($targetDir)) {
        die('โฟลเดอร์เป้าหมายไม่ถูกต้อง');
    }

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = $_FILES['file']['tmp_name'];
        $filename = basename($_FILES['file']['name']);
        $targetFilePath = $targetDir . DIRECTORY_SEPARATOR . $filename;

        // ย้ายไฟล์ไปยังโฟลเดอร์เป้าหมาย
        if (move_uploaded_file($uploadedFile, $targetFilePath)) {
            echo 'อัปโหลดไฟล์สำเร็จ: ' . htmlspecialchars($filename);
        } else {
            echo 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์';
        }
    } else {
        echo 'ไม่มีไฟล์ถูกอัปโหลดหรือเกิดข้อผิดพลาด';
    }
} else {
    echo 'การเข้าถึงหน้านี้ไม่ได้รับอนุญาต';
}
?>
