<?php
session_start();
$config = include 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES['image']['tmp_name']);
        $original_name = $_FILES['image']['name'];
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        // ตรวจสอบนามสกุลไฟล์ว่าเป็นภาพหรือไม่
        $allowed_ext = ['jpg','jpeg','png','gif'];

        if (!in_array($extension, $allowed_ext) || !in_array($file_type, $allowed_types)) {
            $error = "อนุญาตเฉพาะไฟล์รูปภาพ (jpg, png, gif) เท่านั้น";
        } else {
            $filename = basename($original_name);
            $target = $config['upload_directory'] . $filename;

            // ป้องกันชื่อซ้ำ
            if (file_exists($target)) {
                $filename = time() . "_" . $filename;
                $target = $config['upload_directory'] . $filename;
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
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
<link rel="stylesheet" href="assets/css/style.css">
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');
  form.addEventListener('submit', e => {
    const fileInput = form.querySelector('input[type="file"]');
    if (!fileInput.files.length) {
      alert('กรุณาเลือกรูปภาพก่อนอัพโหลด');
      e.preventDefault();
    }
  });
});
</script>
</head>
<body>
<div class="container">
    <h1>อัพโหลดรูปภาพ</h1>
    <div class="nav-links">
        <a href="dashboard.php">ย้อนกลับ</a> 
        <a href="logout.php">ออกจากระบบ</a>
    </div>
    <?php if (!empty($error)): ?>
        <div class="message" style="color:red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="message" style="color:green;"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label>เลือกรูปภาพ:</label>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">อัพโหลด</button>
    </form>
</div>
</body>
</html>
