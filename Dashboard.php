<?php
session_start();
$config = include 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$uploads = [];
if (file_exists($config['upload_log'])) {
    $fileContent = file_get_contents($config['upload_log']);
    if ($fileContent) {
        $lines = explode("\n", trim($fileContent));
        foreach ($lines as $line) {
            // รูปแบบ: filename|timestamp|username|folder
            $parts = explode("|", $line);
            if (count($parts) === 4) {
                $uploads[] = [
                    'filename' => $parts[0],
                    'timestamp' => $parts[1],
                    'username' => $parts[2],
                    'folder' => $parts[3]
                ];
            }
        }
    }
}

usort($uploads, function($a, $b) {
    return $b['timestamp'] <=> $a['timestamp'];
});
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/css/style.css">
<style>
    .upload-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #ccc;
        padding: 10px 0;
    }
    .upload-details {
        flex: 1;
    }
    .upload-thumbnail img {
        max-width: 100px;
        max-height: 100px;
        margin-left: 20px;
    }
    .upload-thumbnail {
        flex-shrink: 0;
    }
</style>
</head>
<body>
<div class="container">
    <h1>Dashboard</h1>
    <div class="nav-links">
        <a href="upload.php">อัพโหลดรูปภาพใหม่</a> 
        <?php if ($_SESSION['username'] === 'admin'): ?>
            <a href="username.php">จัดการผู้ใช้</a>
        <?php endif; ?>
        <a href="logout.php">ออกจากระบบ</a>
    </div>
    <h2>ประวัติการอัพโหลด</h2>
    <?php if (empty($uploads)): ?>
        <p>ยังไม่มีการอัพโหลด</p>
    <?php else: ?>
        <div class="upload-list">
            <?php foreach ($uploads as $up): ?>
                <div class="upload-item">
                    <div class="upload-details">
                        <strong>ไฟล์:</strong> <?php echo htmlspecialchars($up['filename'], ENT_QUOTES, 'UTF-8'); ?><br>
                        <strong>โฟลเดอร์:</strong> <?php echo htmlspecialchars($up['folder'], ENT_QUOTES, 'UTF-8'); ?><br>
                        <strong>อัพโหลดโดย:</strong> <?php echo htmlspecialchars($up['username'], ENT_QUOTES, 'UTF-8'); ?><br>
                        <strong>อัพโหลดเมื่อ:</strong> <?php echo date("Y-m-d H:i:s", $up['timestamp']); ?>
                    </div>
                    <div class="upload-thumbnail">
                        <?php
                        // สร้าง URL สำหรับรูปภาพ
                        $imageUrl = $config['upload_url'] . $up['folder'] . '/' . rawurlencode($up['filename']);
                        // ตรวจสอบว่าไฟล์เป็นภาพหรือไม่
                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                        $fileExtension = strtolower(pathinfo($up['filename'], PATHINFO_EXTENSION));
                        if (in_array($fileExtension, $imageExtensions)) {
                            echo '<img src="' . htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($up['filename'], ENT_QUOTES, 'UTF-8') . '">';
                        } else {
                            echo '<span>ไม่สามารถแสดงตัวอย่าง</span>';
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
