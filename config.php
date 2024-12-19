<?php
return [
    'username' => 'admin',
    // รหัสผ่านเดิม: 1234
    // สร้าง hash ของรหัสผ่านด้วย password_hash ใน PHP CLI หรือ Code ข้างล่างนี้
    // ตัวอย่างการสร้าง hash:
    // <?php echo password_hash("1234", PASSWORD_DEFAULT);
    // สมมุติได้ค่า: $2y$10$wYV9Ztuh...........
    'password_hash' => '$2y$12$WLOzzTucgIh6KpnYF9ECAOSf.nRMxU6bTCtC57D0RSWYGEK1K1Gu6',

    'upload_directory' => 'D:\\uploads\\', 
    'upload_log' => __DIR__ . DIRECTORY_SEPARATOR . 'upload_log.txt'
];
