<?php
// ตั้งชื่อไฟล์หรือโฟลเดอร์สำหรับทดสอบ
$testDir = "D:\\Project Data\\_testFolder";
$testFile = "D:\\Project Data\\_testFolder\\test_file.txt";

// 1) ตรวจสอบว่าสามารถสร้างโฟลเดอร์ได้ไหม
echo "<h3>Check creating folder</h3>";
if (!is_dir($testDir)) {
    if (mkdir($testDir, 0777, true)) {
        echo "สร้างโฟลเดอร์ <code>{$testDir}</code> สำเร็จ<br>";
    } else {
        echo "ไม่สามารถสร้างโฟลเดอร์ <code>{$testDir}</code> ได้<br>";
        echo "โปรดตรวจสอบ Permission หรือ Path<br>";
    }
} else {
    echo "โฟลเดอร์ <code>{$testDir}</code> มีอยู่แล้ว<br>";
}

// 2) ตรวจสอบว่าสามารถเขียนไฟล์ได้ไหม
echo "<h3>Check writing file</h3>";
$data = "Hello, this is a test file.\nDate: " . date("Y-m-d H:i:s");
if (file_put_contents($testFile, $data) !== false) {
    echo "เขียนไฟล์ <code>{$testFile}</code> สำเร็จ<br>";
} else {
    echo "ไม่สามารถเขียนไฟล์ <code>{$testFile}</code> ได้<br>";
}

// 3) ตรวจสอบว่าสามารถอ่านไฟล์ได้ไหม
echo "<h3>Check reading file</h3>";
if (is_file($testFile)) {
    $content = file_get_contents($testFile);
    if ($content !== false) {
        echo "อ่านไฟล์ <code>{$testFile}</code> สำเร็จ: <pre>" . htmlspecialchars($content) . "</pre>";
    } else {
        echo "ไม่สามารถอ่านไฟล์ <code>{$testFile}</code> ได้<br>";
    }
} else {
    echo "ไฟล์ <code>{$testFile}</code> ไม่พบ (ยังไม่ได้สร้างหรือสร้างไม่สำเร็จ)<br>";
}
