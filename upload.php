<?php
session_start();
$config = include 'config.php';

// เปิดการแสดงข้อผิดพลาดสำหรับการดีบัก
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ตรวจสอบสถานะการล็อกอิน
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

// เมื่อมีการส่งข้อมูลแบบ POST (อัปโหลดไฟล์)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_folder = $_POST['first_folder'] ?? '';
    $second_folder = $_POST['second_folder'] ?? '';

    // ป้องกัน Path Traversal
    if (strpos($first_folder, '..') !== false || strpos($second_folder, '..') !== false) {
        $error = "โฟลเดอร์ไม่ถูกต้อง";
    } else {
        // สร้าง path ปลายทาง: D:\Project Data\<first_folder>\Project\<second_folder>\Engineering\Pic\
        $upload_root = rtrim($config['upload_directory'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $final_path = $upload_root 
                    . $first_folder 
                    . DIRECTORY_SEPARATOR 
                    . "Project" 
                    . DIRECTORY_SEPARATOR 
                    . $second_folder 
                    . DIRECTORY_SEPARATOR 
                    . "Engineering" 
                    . DIRECTORY_SEPARATOR 
                    . "Pic" 
                    . DIRECTORY_SEPARATOR;

        // สร้างโฟลเดอร์หากไม่มี
        if (!is_dir($final_path)) {
            if (!@mkdir($final_path, 0777, true)) {
                $error = "ไม่สามารถสร้างโฟลเดอร์ปลายทางได้ (ตรวจสอบสิทธิ์)";
            }
        }

        $real_base = realpath($config['upload_directory']);
        $real_target_dir = realpath($final_path);

        // ตรวจสอบว่ามีโฟลเดอร์จริงหรือไม่ และอยู่ภายใต้ base
        if ($real_target_dir === false || strpos($real_target_dir, $real_base) !== 0) {
            $error = "ไม่สามารถเข้าถึงโฟลเดอร์ปลายทางได้ (path ผิด หรือ permission ไม่พอ)";
        } else {
            // ตรวจสอบไฟล์ที่อัปโหลดจากตัวเลือกทั้งสอง
            $files_to_process = [];
            // ตัวเลือก "ถ่ายรูป" (ไฟล์เดียว)
            if (isset($_FILES['image_capture']) && $_FILES['image_capture']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['image_capture']['error'] === UPLOAD_ERR_OK) {
                    $files_to_process[] = [
                        'name'     => $_FILES['image_capture']['name'],
                        'tmp_name' => $_FILES['image_capture']['tmp_name'],
                        'error'    => $_FILES['image_capture']['error'],
                        'size'     => $_FILES['image_capture']['size']
                    ];
                } else {
                    $file_error = $_FILES['image_capture']['error'];
                    $error = "เกิดข้อผิดพลาดในการอัปโหลด (รหัส: $file_error)";
                }
            }
            // ตัวเลือก "เลือกรูปจากแกลเลอรี" (อาจเลือกได้หลายรูป)
            elseif (isset($_FILES['image_gallery']) && count($_FILES['image_gallery']['name']) > 0 && $_FILES['image_gallery']['error'][0] !== UPLOAD_ERR_NO_FILE) {
                foreach ($_FILES['image_gallery']['name'] as $key => $name) {
                    if ($_FILES['image_gallery']['error'][$key] === UPLOAD_ERR_OK) {
                        $files_to_process[] = [
                            'name'     => $_FILES['image_gallery']['name'][$key],
                            'tmp_name' => $_FILES['image_gallery']['tmp_name'][$key],
                            'error'    => $_FILES['image_gallery']['error'][$key],
                            'size'     => $_FILES['image_gallery']['size'][$key]
                        ];
                    } else {
                        // หากไฟล์ใดมี error เราจะข้ามไฟล์นั้นไป (หรือสามารถจัดการแสดง error เพิ่มเติมได้)
                        continue;
                    }
                }
            } else {
                $error = "ไม่มีไฟล์ถูกส่งมา";
            }

            // ถ้ามีไฟล์ที่ต้องอัปโหลด
            if (empty($error) && !empty($files_to_process)) {
                $username = $_SESSION['username'];
                $today = date("Ymd");
                $count = 0; 
                
                // นับจำนวนไฟล์ที่ user นี้อัปโหลดวันนี้จาก upload_log.txt
                if (file_exists($config['upload_log'])) {
                    $log_lines = file($config['upload_log'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($log_lines as $line) {
                        // รูปแบบ log: filename|timestamp|username|folder
                        $parts = explode("|", $line);
                        if (count($parts) === 4) {
                            $log_timestamp = $parts[1];
                            $log_user = $parts[2];
                            $log_date = date("Ymd", $log_timestamp);
                            if ($log_user === $username && $log_date === $today) {
                                $count++;
                            }
                        }
                    }
                }
                
                foreach ($files_to_process as $file) {
                    $count++;
                    $seq = str_pad($count, 3, "0", STR_PAD_LEFT);
                    $original_name = $file['name'];
                    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                    $new_filename = "{$today}-{$username}-{$seq}.{$extension}";
                    $target_file = $real_target_dir . DIRECTORY_SEPARATOR . $new_filename;

                    if (move_uploaded_file($file['tmp_name'], $target_file)) {
                        // บันทึก log
                        $chosen_path = $first_folder . "\\Project\\" . $second_folder . "\\Engineering\\Pic";
                        file_put_contents(
                            $config['upload_log'], 
                            $new_filename . "|" . time() . "|" . $username . "|" . $chosen_path . "\n", 
                            FILE_APPEND
                        );
                        $success .= "อัปโหลดไฟล์ <strong>$new_filename</strong> สำเร็จ<br>";
                    } else {
                        $error .= "ไม่สามารถย้ายไฟล์ <strong>$new_filename</strong> ไปยังโฟลเดอร์ปลายทางได้<br>";
                    }
                }
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
<style>
    /* สไตล์ง่าย ๆ สำหรับปุ่ม */
    .upload-btn {
        display: inline-block;
        padding: 10px 20px;
        margin: 5px;
        background-color: #4285f4;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .upload-btn:hover {
        background-color: #357ae8;
    }
</style>
</head>
<body>
<div class="container">
    <h1>อัปโหลดรูปภาพ</h1>
    <div class="nav-links">
        <a href="dashboard.php">ย้อนกลับ</a> 
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="message" style="color:red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="message" style="color:green;"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- ส่วนเลือกโฟลเดอร์ (เหมือนในโค้ดเดิม) -->
    <div>
        <label>Folders ปี:</label>
        <select id="first_select">
            <option value="">-กรุณาเลือกโฟลเดอร์-</option>
            <?php if (!empty($first_level_folders)): ?>
                <?php foreach ($first_level_folders as $f): ?>
                    <option value="<?php echo htmlspecialchars($f, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($f, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="">ไม่มีโฟลเดอร์</option>
            <?php endif; ?>
        </select>
    </div>

    <div style="margin-top:10px;">
        <label>Folders Project:</label>
        <select id="second_select" disabled>
            <option value="">กรุณาเลือก Project</option>
        </select>
    </div>

    <div id="selected-info" style="margin-top:10px; color:blue;"></div>
    <div id="fullpath-info" style="margin-top:10px; color:green; font-weight: bold;"></div>
    <div id="fullpath-check" style="margin-top:5px; color:red;"></div>

    <!-- ฟอร์มอัปโหลดไฟล์ -->
    <form method="post" enctype="multipart/form-data" id="upload-form" style="margin-top:20px;">
        <input type="hidden" name="first_folder" value="">
        <input type="hidden" name="second_folder" value="">

        <!-- ตัวเลือกการอัปโหลด: ให้เลือกได้แค่ตัวเลือกใดตัวเลือกหนึ่ง -->
        <div>
            <button type="button" class="upload-btn" id="btn-capture">ถ่ายรูป</button>
            <button type="button" class="upload-btn" id="btn-gallery">เลือกรูปจากแกลเลอรี (เลือกได้หลายรูป)</button>
        </div>

        <!-- ส่วนของ input สำหรับถ่ายรูป (แสดงเฉพาะเมื่อกด "ถ่ายรูป") -->
        <div id="capture-container" style="display:none; margin-top:10px;">
            <label>ถ่ายรูป:</label>
            <input type="file" name="image_capture" accept="image/*" capture="environment">
        </div>

        <!-- ส่วนของ input สำหรับเลือกรูปจากแกลเลอรี (แสดงเฉพาะเมื่อกด "เลือกรูปจากแกลเลอรี") -->
        <div id="gallery-container" style="display:none; margin-top:10px;">
            <label>เลือกรูปจากแกลเลอรี:</label>
            <input type="file" name="image_gallery[]" accept="image/*" multiple>
        </div>

        <button type="submit" class="upload-btn" style="margin-top:20px;">อัปโหลด</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // ส่วนของโฟลเดอร์ (เหมือนในโค้ดเดิม)
    const firstSelect = document.getElementById('first_select');
    const secondSelect = document.getElementById('second_select');
    const selectedInfo = document.getElementById('selected-info');
    const fullPathInfo = document.getElementById('fullpath-info');
    const fullPathCheck = document.getElementById('fullpath-check');

    const firstFolderInput = document.querySelector('input[name="first_folder"]');
    const secondFolderInput = document.querySelector('input[name="second_folder"]');

    firstSelect.addEventListener('change', async () => {
        const chosenFirst = firstSelect.value;
        if (!chosenFirst) {
            secondSelect.innerHTML = '<option value="">-กรุณาเลือก Project-</option>';
            secondSelect.disabled = true;
            selectedInfo.textContent = "";
            firstFolderInput.value = "";
            secondFolderInput.value = "";
            fullPathInfo.textContent = "";
            fullPathCheck.textContent = "";
            return;
        }
        firstFolderInput.value = chosenFirst;
        const path = chosenFirst + "\\Project";
        let subFolders = await loadSubFolders(path);
        renderSecondLevel(subFolders);
        updateSelectedInfo();
        updateFullPathInfo();
    });

    secondSelect.addEventListener('change', () => {
        const chosenSecond = secondSelect.value;
        secondFolderInput.value = chosenSecond;
        updateSelectedInfo();
        updateFullPathInfo();
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
            defaultOpt.textContent = "-กรุณาเลือก Project-";
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

    function updateFullPathInfo() {
        const f1 = firstFolderInput.value;
        const f2 = secondFolderInput.value;
        if (!f1) {
            fullPathInfo.textContent = "";
            fullPathCheck.textContent = "";
            return;
        }
        let base = "<?php echo addslashes($config['upload_directory']); ?>";
        base = base.replace(/\\/g, "\\\\"); 
        let fullPath = base + f1 + "\\Project\\";
        if (f2) {
            fullPath += f2 + "\\";
        }
        fullPath += "Engineering\\Pic\\";
        fullPathInfo.textContent = "Full Path: " + fullPath;
        checkPathExists(fullPath);
    }

    async function checkPathExists(fullPath) {
        let formData = new FormData();
        formData.append('check_path', fullPath);
        let resp = await fetch('check_path.php', {
            method: 'POST',
            body: formData
        });
        if (!resp.ok) {
            fullPathCheck.textContent = "ไม่สามารถตรวจสอบ path ได้ (status " + resp.status + ")";
            return;
        }
        let data = await resp.json();
        if (data.exists) {
            fullPathCheck.textContent = "Path นี้มีอยู่ในระบบแล้ว";
        } else {
            fullPathCheck.textContent = "Path นี้ยังไม่มี (หรือเข้าถึงไม่ได้)";
        }
    }

    // ส่วนของการเลือกวิธีอัปโหลด (ถ่ายรูป หรือเลือกรูปจากแกลเลอรี)
    const btnCapture = document.getElementById('btn-capture');
    const btnGallery = document.getElementById('btn-gallery');
    const captureContainer = document.getElementById('capture-container');
    const galleryContainer = document.getElementById('gallery-container');

    btnCapture.addEventListener('click', () => {
        // แสดงส่วนของการถ่ายรูปและซ่อนส่วนเลือกรูปจากแกลเลอรี
        captureContainer.style.display = 'block';
        galleryContainer.style.display = 'none';
        // ล้างค่า input ในส่วนของแกลเลอรี
        const galleryInput = galleryContainer.querySelector('input[name="image_gallery[]"]');
        if(galleryInput) galleryInput.value = "";
    });

    btnGallery.addEventListener('click', () => {
        // แสดงส่วนของเลือกรูปจากแกลเลอรีและซ่อนส่วนการถ่ายรูป
        galleryContainer.style.display = 'block';
        captureContainer.style.display = 'none';
        // ล้างค่า input ในส่วนของถ่ายรูป
        const captureInput = captureContainer.querySelector('input[name="image_capture"]');
        if(captureInput) captureInput.value = "";
    });

    // ตรวจสอบขนาดไฟล์ในฝั่งไคลเอนต์ (ก่อนส่งฟอร์ม)
    document.getElementById('upload-form').addEventListener('submit', function(e) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        // ตรวจสอบในทั้งสอง input ที่อาจถูกแสดง
        const captureInput = captureContainer.querySelector('input[name="image_capture"]');
        const galleryInput = galleryContainer.querySelector('input[name="image_gallery[]"]');

        if (captureContainer.style.display !== 'none' && captureInput.files.length > 0) {
            const file = captureInput.files[0];
            if (file.size > maxSize) {
                alert("ขนาดไฟล์ใหญ่เกินไป (สูงสุด 10MB)");
                e.preventDefault();
            }
        }
        if (galleryContainer.style.display !== 'none' && galleryInput.files.length > 0) {
            for (let file of galleryInput.files) {
                if (file.size > maxSize) {
                    alert("ขนาดไฟล์บางไฟล์ใหญ่เกินไป (สูงสุด 10MB)");
                    e.preventDefault();
                    break;
                }
            }
        }
    });
});
</script>
</body>
</html>
