<?php
session_start();

// ฟังก์ชันอ่านข้อมูลจากไฟล์ JSON
function readJSON($file) {
    if (!file_exists($file)) {
        return [];
    }
    $json = file_get_contents($file);
    return json_decode($json, true);
}

// ฟังก์ชันเขียนข้อมูลลงไฟล์ JSON
function writeJSON($file, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($file, $json);
}

// ฟังก์ชันตรวจสอบการเข้าสู่ระบบ
function isLoggedIn() {
    return isset($_SESSION['username']);
}

// ฟังก์ชันการเข้าสู่ระบบ
function login($username, $password) {
    $users = readJSON('users.json');
    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['passwordHash'])) {
            $_SESSION['username'] = $username;
            return true;
        }
    }
    return false;
}

// ฟังก์ชันการลงทะเบียนผู้ใช้ (ถ้าต้องการ)
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
    writeJSON('users.json', $users);
    return true;
}
?>
