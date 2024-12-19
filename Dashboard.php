<?php
require 'functions.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['username'];
$history = readJSON('history.json');
$userHistory = [];

// กรองประวัติสำหรับผู้ใช้ปัจจุบัน
foreach ($history as $entry) {
    if ($entry['username'] === $username) {
        $userHistory[] = $entry;
    }
}

// อ่านรายการโฟลเดอร์ที่มีอยู่ใน uploads/
$folders = array_filter(glob('uploads/*'), 'is_dir');
$folders = array_map(function($folder) {
    return basename($folder);
}, $folders);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="dashboard-container">
        <h2>ยินดีต้อนรับ, <?php echo htmlspecialchars($username); ?></h2>
        <a href="logout.php" class="logout-link">ออกจากระบบ</a>

        <h3>อัปโหลดรูปภาพ</h3>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <label for="folder">เลือกโฟลเดอร์:</label>
            <select id="folder" name="folder" required>
                <?php foreach ($folders as $folder): ?>
                    <option value="<?php echo htmlspecialchars($folder); ?>"><?php echo htmlspecialchars($folder); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="image">เลือกไฟล์รูปภาพ:</label>
            <input type="file" id="image" name="image" accept="image/*" required>

            <button type="submit">อัปโหลด</button>
        </form>

        <h3>ประวัติการอัปโหลดของคุณ</h3>
        <?php if (empty($userHistory)): ?>
            <p>ยังไม่มีประวัติการอัปโหลด</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>วันที่และเวลา</th>
                        <th>โฟลเดอร์</th>
                        <th>ชื่อไฟล์</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userHistory as $index => $entry): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($entry['datetime']); ?></td>
                            <td><?php echo htmlspecialchars($entry['folder']); ?></td>
                            <td><?php echo htmlspecialchars($entry['filename']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
