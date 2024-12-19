<?php
session_start();
$config = include 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// เมื่ออัปโหลดไฟล์
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $final_folder = $_POST['final_folder'] ?? '';
    if (strpos($final_folder, '..') !== false) {
        $error = "โฟลเดอร์ไม่ถูกต้อง";
    } else {
        $upload_root = rtrim($config['upload_directory'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $final_folder = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $final_folder);
        $target_dir = $upload_root . $final_folder;
        $real_target_dir = realpath($target_dir);
        $real_base = realpath($config['upload_directory']);

        if ($final_folder === "") {
            // ถ้าผู้ใช้ไม่ได้เลือกอะไรเลย ถือว่าอัปโหลดที่ root directory
            $real_target_dir = realpath($upload_root);
            $target_dir = $upload_root;
        }

        if ($real_target_dir === false || strpos($real_target_dir, $real_base) !== 0) {
            $error = "โฟลเดอร์ไม่ถูกต้อง หรือไม่มีโฟลเดอร์นี้";
        } else {
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = mime_content_type($_FILES['image']['tmp_name']);
                $original_name = $_FILES['image']['name'];
                $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg','jpeg','png','gif'];

                if (!in_array($extension, $allowed_ext) || !in_array($file_type, $allowed_types)) {
                    $error = "อนุญาตเฉพาะไฟล์รูปภาพ (jpg, png, gif) เท่านั้น";
                } else {
                    $filename = basename($original_name);

                    if (!is_dir($real_target_dir)) {
                        mkdir($real_target_dir, 0777, true);
                    }

                    $target = $real_target_dir . DIRECTORY_SEPARATOR . $filename;

                    // ป้องกันชื่อซ้ำ
                    if (file_exists($target)) {
                        $filename = time() . "_" . $filename;
                        $target = $real_target_dir . DIRECTORY_SEPARATOR . $filename;
                    }

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                        file_put_contents($config['upload_log'], 
                            $filename . "|" . time() . "|" . $_SESSION['username'] . "|" . $final_folder . "\n", 
                            FILE_APPEND
                        );
                        $success = "อัพโหลดรูปภาพสำเร็จ";
                    } else {
                        $error = "ไม่สามารถอัพโหลดไฟล์ได้ กรุณาตรวจสอบสิทธิ์โฟลเดอร์";
                    }
                }
            } else {
                $error = "กรุณาเลือกไฟล์รูปภาพ";
            }
        }
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
<script src="assets/js/folder_checkbox.js"></script>
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
    <div id="folder-container"></div>

    <form method="post" enctype="multipart/form-data" id="upload-form" style="margin-top:20px;">
        <input type="hidden" name="final_folder" value="">
        <label>เลือกรูปภาพ:</label>
        <input type="file" name="image" accept="image/*">
        <button type="submit">อัพโหลด</button>
    </form>
</div>
</body>
</html>
