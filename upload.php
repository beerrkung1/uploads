<?php
session_start();
$config = include 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

// เมื่ออัปโหลดไฟล์
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_folder = $_POST['first_folder'] ?? '';
    $second_folder = $_POST['second_folder'] ?? '';

    if (strpos($first_folder, '..') !== false || strpos($second_folder, '..') !== false) {
        $error = "โฟลเดอร์ไม่ถูกต้อง";
    } else {
        // final path: D:\Project Data\<first_folder>\Project\<second_folder>\Engineering\Pic\
        $upload_root = rtrim($config['upload_directory'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $final_path = $upload_root . $first_folder . DIRECTORY_SEPARATOR . "Project" . DIRECTORY_SEPARATOR . $second_folder . DIRECTORY_SEPARATOR . "Engineering" . DIRECTORY_SEPARATOR . "Pic" . DIRECTORY_SEPARATOR;

        if (!is_dir($final_path)) {
            mkdir($final_path, 0777, true);
        }

        $real_base = realpath($config['upload_directory']);
        $real_target_dir = realpath($final_path);

        if ($real_target_dir === false || strpos($real_target_dir, $real_base) !== 0) {
            $error = "ไม่สามารถเข้าถึงโฟลเดอร์ปลายทางได้";
        } else {
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = mime_content_type($_FILES['image']['tmp_name']);
                $original_name = $_FILES['image']['name'];
                $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg','jpeg','png','gif'];

                if (!in_array($extension, $allowed_ext) || !in_array($file_type, $allowed_types)) {
                    $error = "อนุญาตเฉพาะไฟล์รูปภาพ (jpg, png, gif) เท่านั้น";
                } else {
                    // เริ่มการตั้งชื่อไฟล์ใหม่
                    $username = $_SESSION['username'];
                    $today = date("Ymd"); // ปีเดือนวัน
                    $count = 0; 
                    
                    // นับจำนวนไฟล์ที่ user นี้อัปโหลดวันนี้จาก upload_log.txt
                    if (file_exists($config['upload_log'])) {
                        $log_lines = file($config['upload_log'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        foreach ($log_lines as $line) {
                            // รูปแบบ log: filename|timestamp|username|folder
                            $parts = explode("|", $line);
                            if (count($parts) === 4) {
                                $log_filename = $parts[0];
                                $log_timestamp = $parts[1];
                                $log_user = $parts[2];
                                $log_date = date("Ymd", $log_timestamp);
                                if ($log_user === $username && $log_date === $today) {
                                    $count++;
                                }
                            }
                        }
                    }

                    // เลขลำดับถัดไป
                    $count = $count + 1;
                    $seq = str_pad($count, 3, "0", STR_PAD_LEFT); // ###

                    // ตั้งชื่อไฟล์ตาม pattern: YYYYMMDD-username-###.extension
                    $new_filename = $today . "-" . $username . "-" . $seq . "." . $extension;
                    $target = $real_target_dir . DIRECTORY_SEPARATOR . $new_filename;

                    // หากบังเอิญมีชื่อซ้ำ วนหาชื่อใหม่ (โอกาสน้อยมาก)
                    while (file_exists($target)) {
                        $count++;
                        $seq = str_pad($count, 3, "0", STR_PAD_LEFT);
                        $new_filename = $today . "-" . $username . "-" . $seq . "." . $extension;
                        $target = $real_target_dir . DIRECTORY_SEPARATOR . $new_filename;
                    }

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                        $chosen_path = $first_folder . "\\Project\\" . $second_folder . "\\Engineering\\Pic";
                        file_put_contents($config['upload_log'], 
                            $new_filename . "|" . time() . "|" . $username . "|" . $chosen_path . "\n", 
                            FILE_APPEND
                        );
                        $success = "อัพโหลดรูปภาพสำเร็จ";
                    } else {
                        $error = "ไม่สามารถอัพโหลดไฟล์ได้ กรุณาตรวจสอบสิทธิ์โฟลเดอร์";
                    }
                }
            } else {
                $error = "กรุณาเลือกไฟล์รูปภาพ";
            }
        }
    }
}

// โหลดโฟลเดอร์ระดับแรกจาก D:\Project Data\ และเรียงจากใหม่ไปเก่า
$upload_root = rtrim($config['upload_directory'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$first_level_folders = [];
if (is_dir($upload_root)) {
    $dirs = scandir($upload_root);
    $folder_times = [];
    foreach ($dirs as $d) {
        if ($d !== '.' && $d !== '..') {
            $path = $upload_root . $d;
            if (is_dir($path)) {
                $folder_times[$d] = filemtime($path);
            }
        }
    }
    // เรียงจากใหม่ไปเก่า
    arsort($folder_times);
    $first_level_folders = array_keys($folder_times);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Upload รูปภาพ</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <h1>อัพโหลดรูปภาพ</h1>
    <div class="nav-links">
        <a href="dashboard.php">ย้อนกลับ</a> 
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="message" style="color:red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="message" style="color:green;"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <div>
        <label>Folders ปี:</label>
        <select id="first_select">
            <option value="">-กรุณาเลือกโฟลเดอร์-</option>
            <?php if (!empty($first_level_folders)): ?>
                <?php foreach ($first_level_folders as $f): ?>
                    <option value="<?php echo htmlspecialchars($f, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($f, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="">ไม่มีโฟลเดอร์</option>
            <?php endif; ?>
        </select>
    </div>

    <div style="margin-top:10px;">
        <label>Folders Project:</label>
        <select id="second_select" disabled>
            <option value="">-กรุณาเลือกProject-</option>
        </select>
    </div>

    <div id="selected-info" style="margin-top:10px; color:blue;"></div>

    <form method="post" enctype="multipart/form-data" id="upload-form" style="margin-top:20px;">
        <input type="hidden" name="first_folder" value="">
        <input type="hidden" name="second_folder" value="">
        <label>เลือกรูปภาพ:</label>
        <input type="file" name="image" accept="image/*">
        <button type="submit">อัพโหลด</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const firstSelect = document.getElementById('first_select');
    const secondSelect = document.getElementById('second_select');
    const selectedInfo = document.getElementById('selected-info');
    const firstFolderInput = document.querySelector('input[name="first_folder"]');
    const secondFolderInput = document.querySelector('input[name="second_folder"]');

    firstSelect.addEventListener('change', async () => {
        const chosenFirst = firstSelect.value;
        if (!chosenFirst) {
            secondSelect.innerHTML = '<option value="">-กรุณาเลือกProject-</option>';
            secondSelect.disabled = true;
            selectedInfo.textContent = "";
            firstFolderInput.value = "";
            secondFolderInput.value = "";
            return;
        }

        firstFolderInput.value = chosenFirst;
        const path = chosenFirst + "\\Project";
        let subFolders = await loadSubFolders(path);
        renderSecondLevel(subFolders);
        updateSelectedInfo();
    });

    secondSelect.addEventListener('change', () => {
        const chosenSecond = secondSelect.value;
        secondFolderInput.value = chosenSecond;
        updateSelectedInfo();
    });

    async function loadSubFolders(path) {
        const formData = new FormData();
        formData.append('path', path);
        let response = await fetch('list_folders.php', {
            method: 'POST',
            body: formData
        });
        if (response.ok) {
            let data = await response.json();
            return data.folders || [];
        }
        return [];
    }

    function renderSecondLevel(folders) {
        secondSelect.innerHTML = "";
        if (folders.length > 0) {
            let defaultOpt = document.createElement('option');
            defaultOpt.value = "";
            defaultOpt.textContent = "-กรุณาเลือกProject-";
            secondSelect.appendChild(defaultOpt);

            folders.forEach(f => {
                let opt = document.createElement('option');
                opt.value = f;
                opt.textContent = f;
                secondSelect.appendChild(opt);
            });
            secondSelect.disabled = false;
        } else {
            let noOpt = document.createElement('option');
            noOpt.value = "";
            noOpt.textContent = "ไม่มีโฟลเดอร์";
            secondSelect.appendChild(noOpt);
            secondSelect.disabled = true;
        }
    }

    function updateSelectedInfo() {
        const f1 = firstFolderInput.value;
        const f2 = secondFolderInput.value;

        if (f1 && f2) {
            selectedInfo.textContent = "คุณเลือก: " + f1 + " / " + f2;
        } else if (f1 && !f2) {
            selectedInfo.textContent = "คุณเลือก: " + f1;
        } else {
            selectedInfo.textContent = "";
        }
    }
});
</script>
</body>
</html>
