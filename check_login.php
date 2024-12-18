<?php
session_start();
require 'config.php'; // เรียกใช้ตัวแปร ALLOWED_USERNAME และ ALLOWED_PASSWORD_HASH

// รับข้อมูลจากฟอร์ม
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// ตรวจสอบความถูกต้อง
if ($username === ALLOWED_USERNAME && password_verify($password, ALLOWED_PASSWORD_HASH)) {
    // Login สำเร็จ
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $username;
    header("Location: protected.php");
    exit();
} else {
    // Login ไม่สำเร็จ
    $_SESSION['error'] = "Username หรือ Password ไม่ถูกต้อง";
    header("Location: login.php");
    exit();
}

?>