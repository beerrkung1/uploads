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
</head>
<body>
<div class="container">
    <h1>Dashboard</h1>
    <div class="nav-links">
        <a href="upload.php">อัพโหลดรูปภาพใหม่</a> 
        <a href="logout.php">ออกจากระบบ</a>
    </div>
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
                         alt="<?php echo htmlspecialchars($up['filename']); ?>">
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
</body>
</html>
