
<?php
// กำหนดค่า Username ที่อนุญาต
define('ALLOWED_USERNAME', 'adminbpair');
$original_password = "C0mp!exP@ssw0rd2024?";
$hashed_password = password_hash($original_password, PASSWORD_BCRYPT);
define('ALLOWED_PASSWORD_HASH', $hashed_password);

?>