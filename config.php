<?php
return [
    'username' => 'admin',
    // รหัสผ่านเดิม: 1234
    // สร้าง hash ของรหัสผ่านด้วย password_hash ใน PHP CLI หรือ Code ข้างล่างนี้
    // ตัวอย่างการสร้าง hash:
    // <?php echo password_hash("1234", PASSWORD_DEFAULT);
    // สมมุติได้ค่า: $2y$10$wYV9Ztuh...........
    'password_hash' => '$2y$10$wYV9Ztuh67aQ7MZOnG3S3udz9GwnHBgHVu2Zr5yzsAQy7K.1YZ90m',

    'upload_directory' => 'D:\\uploads\\', 
    'upload_log' => __DIR__ . DIRECTORY_SEPARATOR . 'upload_log.txt'
];
