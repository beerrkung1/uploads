<?php
session_start();
$config = include 'config.php';

// ถ้าล็อกอินแล้ว ให้ไป dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    // เดิม: ตรวจสอบเฉพาะ $config['username'] และ $config['password_hash']
    // ใหม่: ตรวจสอบใน $config['users'] (array)
    if (!empty($config['users']) && is_array($config['users'])) {
        // ตรวจสอบว่ามี username นี้อยู่ใน array หรือไม่
        if (array_key_exists($user, $config['users'])) {
            // มี user นี้ใน config
            $stored_hash = $config['users'][$user];
            // เช็ค password
            if (password_verify($pass, $stored_hash)) {
                // ผ่าน
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $user;
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "รหัสผ่านไม่ถูกต้อง";
            }
        } else {
            $error = "ไม่พบบัญชีผู้ใช้ หรือชื่อผู้ใช้ไม่ถูกต้อง";
        }
    } else {
        $error = "ระบบยังไม่พร้อม หรือไม่มีข้อมูลผู้ใช้ใน config";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <h1>เข้าสู่ระบบ</h1>
    <?php if (!empty($error)): ?>
        <div class="message" style="color:red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
