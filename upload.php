<?php
session_start();
$config = include 'config.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        // ตรวจสอบชนิดไฟล์ภาพเบื้องต้น
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowed)) {
            $error = "อนุญาตเฉพาะไฟล์รูปภาพ (jpg, png, gif) เท่านั้น";
        } else {
            $filename = basename($_FILES['image']['name']);
            $target = $config['upload_directory'] . $filename;

            // ป้องกันชื่อซ้ำด้วยการเพิ่มเวลา unix time
            if (file_exists($target)) {
                $filename = time() . "_" . $filename;
                $target = $config['upload_directory'] . $filename;
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                // เขียน log
                $logLine = $filename . "|" . time() . "\n";
                file_put_contents($config['upload_log'], $logLine, FILE_APPEND);
                $success = "อัพโหลดรูปภาพสำเร็จ";
            } else {
                $error = "ไม่สามารถอัพโหลดไฟล์ได้ กรุณาตรวจสอบสิทธิ์โฟลเดอร์";
            }
        }
    } else {
        $error = "กรุณาเลือกไฟล์รูปภาพ";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Upload รูปภาพ</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>อัพโหลดรูปภาพ</h1>
    <a href="dashboard.php">ย้อนกลับ</a> | 
    <a href="logout.php">ออกจากระบบ</a>
    <hr>
    <?php if (!empty($error)): ?>
        <div style="color:red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div style="color:green;"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label>เลือกรูปภาพ:</label><br>
        <input type="file" name="image" accept="image/*"><br><br>
        <button type="submit">อัพโหลด</button>
    </form>
</body>
</html>
