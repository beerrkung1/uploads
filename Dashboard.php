<?php
session_start();
$config = include 'config.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// อ่านไฟล์ log ประวัติการอัพโหลด
$uploads = [];
if (file_exists($config['upload_log'])) {
    $fileContent = file_get_contents($config['upload_log']);
    if ($fileContent) {
        $lines = explode("\n", trim($fileContent));
        foreach ($lines as $line) {
            // รูปแบบการเก็บ: filename|timestamp
            $parts = explode("|", $line);
            if (count($parts) === 2) {
                $uploads[] = [
                    'filename' => $parts[0],
                    'timestamp' => $parts[1]
                ];
            }
        }
    }
}

// เรียงลำดับตามเวลา (ล่าสุดอยู่บน)
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
</head>
<body>
    <h1>Dashboard ประวัติการอัพโหลดรูปภาพ</h1>
    <a href="upload.php">อัพโหลดรูปภาพใหม่</a> | 
    <a href="logout.php">ออกจากระบบ</a>
    <hr>
    <h2>ประวัติการอัพโหลด</h2>
    <?php if (empty($uploads)): ?>
        <p>ยังไม่มีการอัพโหลด</p>
    <?php else: ?>
        <ul>
            <?php foreach ($uploads as $up): ?>
                <li>
                    <strong><?php echo htmlspecialchars($up['filename']); ?></strong><br>
                    อัพโหลดเมื่อ: <?php echo date("Y-m-d H:i:s", $up['timestamp']); ?><br>
                    <img src="http://your-domain-or-ip/uploads/<?php echo rawurlencode($up['filename']); ?>" 
                         alt="<?php echo htmlspecialchars($up['filename']); ?>" width="200">
                </li>
                <hr>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
