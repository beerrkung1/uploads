<?php
// ชื่อผู้ใช้ รหัสผ่านที่กำหนดตายตัว (Hard-coded)
// ในระบบจริงควรมีวิธีการเข้ารหัสรหัสผ่าน แต่ตัวอย่างนี้เพื่อความง่าย
return [
    'username' => 'admin',
    'password' => '1234', 
    'upload_directory' => 'D:\\Prject Data\\', // ระบุ path อัพโหลด
    'upload_log' => __DIR__ . DIRECTORY_SEPARATOR . 'upload_log.txt'
];
?>