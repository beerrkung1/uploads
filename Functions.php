<?php
// เปิดการแสดงข้อผิดพลาดสำหรับการดีบัก
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// เริ่มต้นเซสชัน
session_start();

// ฟังก์ชันอ่านข้อมูลจากไฟล์ JSON
function readJSON($file) {
    if (!file_exists($file)) {
        error_log("ไฟล์ $file ไม่พบ");
        return [];
    }
    $json = file_get_contents($file);
    if ($json === false) {
        error_log("ไม่สามารถอ่านไฟล์ $file ได้");
        return [];
    }
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error decoding JSON from $file: " . json_last_error_msg());
        return [];
    }
    return $data;
}

// ฟังก์ชันเขียนข้อมูลลงไฟล์ JSON
function writeJSON($file, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        error_log("Error encoding JSON to $file: " . json_last_error_msg());
        return false;
    }
    file_put_contents($file, $json);
    return true;
}

// ฟังก์ชันตรวจสอบการเข้าสู่ระบบ
function isLoggedIn() {
    return isset($_SESSION['username']);
}

// ฟังก์ชันการเข้าสู่ระบบ
function login($username, $password) {
    $users = readJSON('users.json');

    if (empty($users)) {
        error_log("ไม่พบผู้ใช้ในไฟล์ users.json");
        return false;
    }

    foreach ($users as $user) {
        if ($user['username'] === $username) {
            // แสดงข้อมูลผู้ใช้สำหรับการดีบัก
            echo "พบผู้ใช้: " . htmlspecialchars($user['username']) . "<br>";
            echo "รหัสแฮช: " . htmlspecialchars($user['passwordHash']) . "<br>";

            if (password_verify($password, $user['passwordHash'])) {
                echo "รหัสผ่านถูกต้อง.<br>";
                $_SESSION['username'] = $username;
                return true;
            } else {
                echo "การตรวจสอบรหัสผ่านล้มเหลว.<br>";
                error_log("รหัสผ่านไม่ถูกต้องสำหรับผู้ใช้ $username");
                return false;
            }
        }
    }
    error_log("ไม่พบผู้ใช้ $username ในไฟล์ users.json");
    return false;
}

// ฟังก์ชันการลงทะเบียนผู้ใช้
function register($username, $password) {
    $users = readJSON('users.json');
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            return false; // ผู้ใช้มีอยู่แล้ว
        }
    }
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $users[] = [
        'username' => $username,
        'passwordHash' => $passwordHash
    ];
    return writeJSON('users.json', $users);
}
?>
