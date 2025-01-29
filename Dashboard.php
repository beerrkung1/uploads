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

// การตั้งค่าการแบ่งหน้า
$perPage = 20; // จำนวนรายการต่อหน้า
$totalUploads = count($uploads);
$totalPages = ceil($totalUploads / $perPage);

// ดึงหน้าปัจจุบันจาก URL, ตั้งค่าเริ่มต้นเป็น 1
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// ตรวจสอบให้แน่ใจว่าหน้าปัจจุบันอยู่ในขอบเขตที่ถูกต้อง
if ($currentPage < 1) {
    $currentPage = 1;
} elseif ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

// คำนวณ offset
$offset = ($currentPage - 1) * $perPage;

// ตัดรายการสำหรับหน้าปัจจุบัน
$currentUploads = array_slice($uploads, $offset, $perPage);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/css/style.css">
<style>
    /* เพิ่มสไตล์สำหรับการแบ่งหน้า */
    .pagination {
        margin-top: 20px;
        text-align: center;
    }
    .pagination a, .pagination span {
        display: inline-block;
        padding: 8px 16px;
        margin: 0 4px;
        text-decoration: none;
        color: #333;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .pagination a:hover {
        background-color: #f0f0f0;
    }
    .pagination .current {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }
    .pagination .disabled {
        color: #ccc;
        border-color: #ddd;
        pointer-events: none;
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
    <?php if (empty($currentUploads)): ?>
        <p>ยังไม่มีการอัพโหลด</p>
    <?php else: ?>
        <ul>
            <?php foreach ($currentUploads as $up): ?>
                <li>
                    <strong>ไฟล์:</strong> <?php echo htmlspecialchars($up['filename'], ENT_QUOTES, 'UTF-8'); ?><br>
                    <strong>โฟลเดอร์:</strong> <?php echo htmlspecialchars($up['folder'], ENT_QUOTES, 'UTF-8'); ?><br>
                    <strong>อัพโหลดโดย:</strong> <?php echo htmlspecialchars($up['username'], ENT_QUOTES, 'UTF-8'); ?><br>
                    <strong>อัพโหลดเมื่อ:</strong> <?php echo date("Y-m-d H:i:s", $up['timestamp']); ?><br>
                    <!-- หากต้องการแสดงรูปจาก Virtual Directory เช่น http://your-domain/ProjectData/ -->
                    <!-- <img src="http://your-domain/ProjectData/<?php echo rawurlencode(str_replace('\\','/',$up['folder'] . '/' . $up['filename'])); ?>" 
                         alt="<?php echo htmlspecialchars($up['filename'], ENT_QUOTES, 'UTF-8'); ?>"
                         style="max-width:200px;"> -->
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- แสดงการแบ่งหน้า -->
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?php echo $currentPage - 1; ?>">&laquo; ก่อนหน้า</a>
            <?php else: ?>
                <span class="disabled">&laquo; ก่อนหน้า</span>
            <?php endif; ?>

            <!-- แสดงหน้าต่างๆ -->
            <?php
            // กำหนดจำนวนหน้าที่จะแสดงในแถบการแบ่งหน้า
            $range = 2; // จำนวนหน้าที่จะแสดงก่อนและหลังหน้าปัจจุบัน

            // เริ่มต้นและสิ้นสุดของช่วงหน้าที่จะแสดง
            $start = max(1, $currentPage - $range);
            $end = min($totalPages, $currentPage + $range);

            if ($start > 1) {
                echo '<a href="?page=1">1</a>';
                if ($start > 2) {
                    echo '<span>...</span>';
                }
            }

            for ($i = $start; $i <= $end; $i++) {
                if ($i == $currentPage) {
                    echo '<span class="current">' . $i . '</span>';
                } else {
                    echo '<a href="?page=' . $i . '">' . $i . '</a>';
                }
            }

            if ($end < $totalPages) {
                if ($end < $totalPages - 1) {
                    echo '<span>...</span>';
                }
                echo '<a href="?page=' . $totalPages . '">' . $totalPages . '</a>';
            }
            ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?php echo $currentPage + 1; ?>">ถัดไป &raquo;</a>
            <?php else: ?>
                <span class="disabled">ถัดไป &raquo;</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
