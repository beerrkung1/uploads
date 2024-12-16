
<?php
// กำหนดค่า Username ที่อนุญาต
define('ALLOWED_USERNAME', 'adminbpair');

// สมมติว่าเรามีค่า Password "C0mp!exP@ssw0rd2024?"
$original_password = "C0mp!exP@ssw0rd2024?";

// เพื่อความปลอดภัย ไม่ควรเก็บเป็น Plain text ใน Production
// เราจะเก็บเฉพาะ hash เท่านั้น
// รันครั้งเดียวแล้วคัดลอกค่า hash ไปใช้ จากนั้นลบ $original_password ออกไปได้
$hashed_password = password_hash($original_password, PASSWORD_BCRYPT);

// ลองแสดงค่า hash (ทดสอบครั้งแรกเท่านั้น พอได้ hash แล้ว ควรคอมเมนต์ออก)
// echo $hashed_password; 
// จากนั้นนำค่า hash นี้มาใส่แทน $hashed_password ในไฟล์ config จริง ๆ โดยไม่ต้องเก็บ $original_password
// เช่น:
// define('ALLOWED_PASSWORD_HASH', '$2y$10$Dlw5bRfNUeUt7S0w9lU6JO...');
//
// เมื่อได้ hash มาแล้ว ให้แก้โค้ดเป็น:
define('ALLOWED_PASSWORD_HASH', $hashed_password);
