<?php
require 'functions.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];
    $folder = trim($_POST['folder']);

    // ตรวจสอบว่าโฟลเดอร์ที่เลือกมีอยู่จริง
    $uploadDir = 'uploads/' . $folder;
    if (!is_dir($uploadDir)) {
        die('โฟลเดอร์ไม่ถูกต้อง');
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileSize = $_FILES['image']['size'];
        $fileType = $_FILES['image']['type'];
        $fileNameCmps = pathinfo($fileName);
        $fileExtension = strtolower($fileNameCmps['extension']);

        // ตรวจสอบนามสกุลไฟล์
        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // ตรวจสอบ MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fileTmpPath);
            finfo_close($finfo);

            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($mimeType, $allowedMimeTypes)) {
                die('ไฟล์ที่อัปโหลดไม่ใช่ไฟล์รูปภาพ');
            }

            // สร้างชื่อไฟล์ใหม่เพื่อหลีกเลี่ยงการชนกัน
            $newFileName = uniqid() . '.' . $fileExtension;

            $destPath = $uploadDir . '/' . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // บันทึกประวัติการอัปโหลด
                $history = readJSON('history.json');
                $history[] = [
                    'username' => $username,
                    'datetime' => date('Y-m-d H:i:s'),
                    'folder' => $folder,
                    'filename' => $newFileName
                ];
                writeJSON('history.json', $history);

                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'เกิดข้อผิดพลาดในการย้ายไฟล์';
            }
        } else {
            $error = 'นามสกุลไฟล์ไม่ถูกต้อง';
        }
    } else {
        $error = 'ไม่พบไฟล์ที่อัปโหลดหรือเกิดข้อผิดพลาดในการอัปโหลด';
    }

    // หากเกิดข้อผิดพลาด ให้แสดงข้อผิดพลาดและกลับไปยังหน้า Dashboard
    if (isset($error)) {
        echo "<script>alert('$error'); window.location.href='dashboard.php';</script>";
    }
} else {
    header('Location: dashboard.php');
    exit();
}
?>
