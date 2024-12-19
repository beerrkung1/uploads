<?php
// generate_hash.php

// ฟังก์ชันสำหรับสร้างรหัสผ่านแฮช
function generatePasswordHash($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// รายการผู้ใช้และรหัสผ่านที่ต้องการ
$users = [
    'admin1' => 'Password1!',
    'admin2' => 'Password2!',
    'admin3' => 'Password3!',
    'admin4' => 'Password4!',
    'admin5' => 'Password5!'
];

// แสดงรหัสแฮชสำหรับแต่ละผู้ใช้
foreach ($users as $username => $password) {
    $hash = generatePasswordHash($password);
    echo "Username: $username\nPassword: $password\nHash: $hash\n\n";
}
?>
