<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // ถ้ายังไม่ได้ล็อกอิน ส่งกลับหน้า login
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Protected Area</title>
</head>
<body>
    <h1>ยินดีต้อนรับ <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></h1>
    <p>คุณได้เข้าสู่ระบบเรียบร้อยแล้ว!</p>
    <a href="logout.php">Logout</a>
</body>
</html>

