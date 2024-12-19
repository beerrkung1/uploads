<?php
session_start();
$config = include 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// การอัปโหลดไฟล์และบันทึก log เหมือนเดิม ไม่มีการเปลี่ยนแปลงตรรกะ

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (อัปโหลดไฟล์เหมือนเดิม)
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Upload รูปภาพ</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/folder_checkbox.js"></script>
<script>
// Debug: ตรวจสอบว่า JS โหลดแล้ว
console.log('folder_checkbox.js loaded');
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

    <button type="button" id="back-btn" style="display:none;">Back</button>
    <div id="folder-container">
        <!-- ที่นี่จะถูก JS สร้างรายการโฟลเดอร์ -->
    </div>

    <form method="post" enctype="multipart/form-data" id="upload-form" style="margin-top:20px;">
        <input type="hidden" name="final_folder" value="">

        <label>เลือกรูปภาพ:</label>
        <input type="file" name="image" accept="image/*">
        <button type="submit">อัพโหลด</button>
    </form>
</div>
</body>
</html>
