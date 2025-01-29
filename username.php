<?php
session_start();
$config = include 'config.php';

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['username'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// ฟังก์ชันสำหรับอ่านผู้ใช้จากไฟล์ JSON
function readUsers($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }
    $json = file_get_contents($filePath);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

// ฟังก์ชันสำหรับเขียนผู้ใช้ลงในไฟล์ JSON
function writeUsers($filePath, $users) {
    $json = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($filePath, $json, LOCK_EX);
}

$usersFile = 'users.json';
$users = readUsers($usersFile);

// การจัดการเพิ่มผู้ใช้
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $newUsername = trim($_POST['new_username']);
        $newPassword = $_POST['new_password'];
        
        if (empty($newUsername) || empty($newPassword)) {
            $message = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
        } elseif (isset($users[$newUsername])) {
            $message = "ชื่อผู้ใช้นี้มีอยู่แล้ว";
        } else {
            $users[$newUsername] = password_hash($newPassword, PASSWORD_DEFAULT);
            writeUsers($usersFile, $users);
            $message = "เพิ่มผู้ใช้สำเร็จ";
        }
    }
    
    // การจัดการแก้ไขผู้ใช้
    if ($action === 'edit') {
        $editUsername = $_POST['edit_username'];
        $editPassword = $_POST['edit_password'];
        
        if (isset($users[$editUsername])) {
            if (!empty($editPassword)) {
                $users[$editUsername] = password_hash($editPassword, PASSWORD_DEFAULT);
            }
            writeUsers($usersFile, $users);
            $message = "แก้ไขผู้ใช้สำเร็จ";
        } else {
            $message = "ไม่พบบัญชีผู้ใช้ที่ต้องการแก้ไข";
        }
    }
    
    // การจัดการลบผู้ใช้
    if ($action === 'delete') {
        $deleteUsername = $_POST['delete_username'];
        
        if ($deleteUsername === 'admin') {
            $message = "ไม่สามารถลบบัญชีผู้ใช้ admin ได้";
        } elseif (isset($users[$deleteUsername])) {
            unset($users[$deleteUsername]);
            writeUsers($usersFile, $users);
            $message = "ลบผู้ใช้สำเร็จ";
        } else {
            $message = "ไม่พบบัญชีผู้ใช้ที่ต้องการลบ";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการผู้ใช้</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <h1>จัดการผู้ใช้</h1>
    <div class="nav-links">
        <a href="dashboard.php">กลับไปยัง Dashboard</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>
    <?php if (isset($message)): ?>
        <div class="message" style="color:green;"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    
    <h2>เพิ่มผู้ใช้ใหม่</h2>
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <label>ชื่อผู้ใช้ใหม่:</label>
        <input type="text" name="new_username" required>
        
        <label>รหัสผ่านใหม่:</label>
        <input type="password" name="new_password" required>
        
        <button type="submit">เพิ่มผู้ใช้</button>
    </form>
    
    <h2>รายการผู้ใช้</h2>
    <?php if (empty($users)): ?>
        <p>ไม่มีผู้ใช้ในระบบ</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>ชื่อผู้ใช้</th>
                <th>การกระทำ</th>
            </tr>
            <?php foreach ($users as $username => $passwordHash): ?>
                <tr>
                    <td><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <!-- แบบฟอร์มแก้ไขผู้ใช้ -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="edit_username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="password" name="edit_password" placeholder="รหัสผ่านใหม่ (ถ้าเปลี่ยน)">
                            <button type="submit">แก้ไข</button>
                        </form>
                        
                        <!-- แบบฟอร์มลบผู้ใช้ -->
                        <form method="POST" style="display:inline;" onsubmit="return confirm('ต้องการลบผู้ใช้นี้หรือไม่?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="delete_username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit">ลบ</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
