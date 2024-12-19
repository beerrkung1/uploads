<?php
session_start();
$config = include 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// สแกนหาโฟลเดอร์ทั้งหมดใน D:\Project Data\
$upload_root = $config['upload_directory']; 
$allowed_folders = [];

// สแกน directory เพื่อหาทุกโฟลเดอร์ย่อยภายใต้ D:\Project Data\
if (is_dir($upload_root)) {
    $dirs = scandir($upload_root);
    foreach ($dirs as $d) {
        if ($d !== '.' && $d !== '..') {
            $path = $upload_root . $d;
            if (is_dir($path)) {
                $allowed_folders[] = $d;
            }
        }
    }
}

// เมื่อมีการ Submit Form อัพโหลด
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_folder = $_POST['folder'] ?? '';

    // ตรวจสอบว่าชื่อโฟลเดอร์นั้นอยู่ใน $allowed_folders
    if (!in_array($selected_folder, $allowed_folders)) {
        $error = "โฟลเดอร์ที่เลือกไม่ถูกต้อง";
    } else {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = mime_content_type($_FILES['image']['tmp_name']);
            $original_name = $_FILES['image']['name'];
            $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

            // ตรวจสอบนามสกุลไฟล์ว่าเป็นภาพ
            $allowed_ext = ['jpg','jpeg','png','gif'];

            if (!in_array($extension, $allowed_ext) || !in_array($file_type, $allowed_types)) {
                $error = "อนุญาตเฉพาะไฟล์รูปภาพ (jpg, png, gif) เท่านั้น";
            } else {
                $filename = basename($original_name);
                $target_dir = $upload_root . $selected_folder . DIRECTORY_SEPARATOR;

                // ถ้าไม่มีโฟลเดอร์ก็สร้าง (ควรมีอยู่แล้ว ถ้าไม่มีก็สร้าง)
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                $target = $target_dir . $filename;

                // ป้องกันชื่อซ้ำ
                if (file_exists($target)) {
                    $filename = time() . "_" . $filename;
                    $target = $target_dir . $filename;
                }

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    // บันทึก log: filename|timestamp|username|folder
                    $logLine = $filename . "|" . time() . "|" . $_SESSION['username'] . "|" . $selected_folder . "\n";
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
    const folderSelect = form.querySelector('select[name="folder"]');
    if (!fileInput.files.length) {
      alert('กรุณาเลือกรูปภาพก่อนอัพโหลด');
      e.preventDefault();
    } else if (!folderSelect.value) {
      alert('กรุณาเลือกโฟลเดอร์');
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
        <label>เลือกโฟลเดอร์สำหรับอัพโหลด:</label>
        <select name="folder" required>
            <option value="">-- กรุณาเลือกโฟลเดอร์ --</option>
            <?php foreach ($allowed_folders as $folder): ?>
                <option value="<?php echo htmlspecialchars($folder); ?>"><?php echo htmlspecialchars($folder); ?></option>
            <?php endforeach; ?>
        </select>

        <label>เลือกรูปภาพ:</label>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">อัพโหลด</button>
    </form>
</div>
</body>
</html>
