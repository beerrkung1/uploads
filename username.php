<?php
session_start();
$config = include 'config.php';

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['username'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// อ่านข้อมูลผู้ใช้จาก config.php
$users = $config['users'];

// กำหนดเส้นทางของไฟล์ config.php
$configFile = 'config.php';

// ฟังก์ชันสำหรับเขียนข้อมูลลงใน config.php
function writeConfig($configFile, $configArray) {
    $configContent = "<?php\nreturn [\n";

    // เขียนส่วน 'users'
    $configContent .= "    'users' => [\n";
    foreach ($configArray['users'] as $username => $passwordHash) {
        $configContent .= "        '" . addslashes($username) . "' => '" . addslashes($passwordHash) . "',\n";
    }
    $configContent .= "    ],\n";

    // เขียนส่วนอื่น ๆ ของ config
    foreach ($configArray as $key => $value) {
        if ($key === 'users') continue; // ข้าม 'users' เพราะเราเขียนไปแล้ว
        if (is_string($value)) {
            $configContent .= "    '" . addslashes($key) . "' => '" . addslashes($value) . "',\n";
        } elseif (is_array($value)) {
            // สำหรับค่าอื่นที่เป็น array หากมี
            $configContent .= "    '" . addslashes($key) . "' => " . var_export($value, true) . ",\n";
        }
    }

    $configContent .= "];\n?>";

    // เขียนลงไฟล์
    return file_put_contents($configFile, $configContent, LOCK_EX);
}

// การจัดการฟอร์มต่าง ๆ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $message = '';

        if ($action === 'add') {
            // การเพิ่มผู้ใช้ใหม่
            $new_username = trim($_POST['new_username']);
            $new_password = $_POST['new_password'];

            if (empty($new_username) || empty($new_password)) {
                $message = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
            } elseif (isset($users[$new_username])) {
                $message = "ชื่อผู้ใช้นี้มีอยู่แล้ว";
            } else {
                $users[$new_username] = password_hash($new_password, PASSWORD_DEFAULT);
                $config['users'] = $users;
                if (writeConfig($configFile, $config)) {
                    $message = "เพิ่มผู้ใช้สำเร็จ";
                } else {
                    $message = "เกิดข้อผิดพลาดในการเขียนไฟล์ config.php";
                }
            }
        }

        if ($action === 'edit') {
            // การแก้ไขผู้ใช้
            $edit_username = $_POST['edit_username'];
            $edit_password = $_POST['edit_password'];

            if (isset($users[$edit_username])) {
                if (!empty($edit_password)) {
                    $users[$edit_username] = password_hash($edit_password, PASSWORD_DEFAULT);
                    $config['users'] = $users;
                    if (writeConfig($configFile, $config)) {
                        $message = "แก้ไขรหัสผ่านผู้ใช้สำเร็จ";
                    } else {
                        $message = "เกิดข้อผิดพลาดในการเขียนไฟล์ config.php";
                    }
                } else {
                    $message = "กรุณากรอกรหัสผ่านใหม่";
                }
            } else {
                $message = "ไม่พบบัญชีผู้ใช้ที่ต้องการแก้ไข";
            }
        }

        if ($action === 'delete') {
            // การลบผู้ใช้
            $delete_username = $_POST['delete_username'];

            if ($delete_username === 'admin') {
                $message = "ไม่สามารถลบบัญชีผู้ใช้ admin ได้";
            } elseif (isset($users[$delete_username])) {
                unset($users[$delete_username]);
                $config['users'] = $users;
                if (writeConfig($configFile, $config)) {
                    $message = "ลบผู้ใช้สำเร็จ";
                } else {
                    $message = "เกิดข้อผิดพลาดในการเขียนไฟล์ config.php";
                }
            } else {
                $message = "ไม่พบบัญชีผู้ใช้ที่ต้องการลบ";
            }
        }

        // รีเฟรชข้อมูลผู้ใช้หลังการเปลี่ยนแปลง
        $config = include 'config.php';
        $users = $config['users'];
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
<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }
    table, th, td {
        border: 1px solid #ccc;
    }
    th, td {
        padding: 10px;
        text-align: left;
    }
    .message {
        margin: 10px 0;
        padding: 10px;
        border: 1px solid #4CAF50;
        background-color: #DFF2BF;
        color: #4F8A10;
    }
</style>
</head>
<body>
<div class="container">
    <h1>จัดการผู้ใช้</h1>
    <div class="nav-links">
        <a href="dashboard.php">กลับไปยัง Dashboard</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>
    <?php if (isset($message)): ?>
        <div class="message"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <h2>เพิ่มผู้ใช้ใหม่</h2>
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <label>ชื่อผู้ใช้ใหม่:</label><br>
        <input type="text" name="new_username" required><br><br>
        
        <label>รหัสผ่านใหม่:</label><br>
        <input type="password" name="new_password" required><br><br>
        
        <button type="submit">เพิ่มผู้ใช้</button>
    </form>

    <h2>รายการผู้ใช้</h2>
    <?php if (empty($users)): ?>
        <p>ไม่มีผู้ใช้ในระบบ</p>
    <?php else: ?>
        <table>
            <tr>
                <th>ชื่อผู้ใช้</th>
                <th>การกระทำ</th>
            </tr>
            <?php foreach ($users as $username => $passwordHash): ?>
                <tr>
                    <td><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <!-- แบบฟอร์มแก้ไขผู้ใช้ -->
                        <form method="POST" style="display:inline-block; margin-right:10px;">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="edit_username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="password" name="edit_password" placeholder="รหัสผ่านใหม่" required>
                            <button type="submit">แก้ไข</button>
                        </form>
                        
                        <!-- แบบฟอร์มลบผู้ใช้ -->
                        <form method="POST" style="display:inline-block;" onsubmit="return confirm('ต้องการลบผู้ใช้นี้หรือไม่?');">
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
