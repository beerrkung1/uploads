<?php
session_start();
$config = include 'config.php';

// เปิดการแสดงข้อผิดพลาดสำหรับการดีบัก (ในกรณีที่เซิร์ฟเวอร์อนุญาต)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ตรวจสอบสถานะการล็อกอิน (ต้องมีการเซ็ต $_SESSION['logged_in'] = true ในขั้นตอน Login)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

/**
 * ฟังก์ชันสำหรับบีบอัดและย่อขนาดรูปภาพ โดยพยายามไม่ให้เกิน $maxSize (เริ่มต้น 2MB)
 * - ย่อความสูงสูงสุดไว้ที่ 1000 px (ปรับเพิ่ม/ลดได้)
 * - ลดคุณภาพภาพ (quality) ไปเรื่อย ๆ จนกว่าจะได้ขนาดไฟล์ตามต้องการ หรือ quality < 10
 */
function compressImage($source, $destination, $maxSize = 2 * 1024 * 1024)
{
    // อ่านข้อมูลรูปภาพ
    $info = getimagesize($source);
    if ($info === false) {
        return false;
    }

    $mime = $info['mime'];

    // สร้างทรัพยากรรูปภาพจากไฟล์
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    if (!$image) {
        return false;
    }

    // กำหนดความสูงสูงสุด
    $maxHeight = 1000;
    $width  = imagesx($image);
    $height = imagesy($image);

    // คำนวณอัตราส่วนย่อภาพ (อิงความสูง)
    $scale = min($maxHeight / $height, 1);

    $newWidth  = floor($width * $scale);
    $newHeight = floor($height * $scale);

    // สร้างภาพใหม่
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // สำหรับ PNG / GIF ให้รักษาความโปร่งใส
    if ($mime === 'image/png' || $mime === 'image/gif') {
        imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
    }

    // ย่อขนาด
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, 
                       $newWidth, $newHeight, $width, $height);

    // วนบันทึกลดคุณภาพทีละ 5 จนกว่าจะ <= $maxSize หรือ quality เหลือ < 10
    $quality = 90;
    do {
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($newImage, $destination, $quality);
                break;
            case 'image/png':
                // imagepng ใช้ค่า 0-9 (0 = ดีที่สุด)
                $pngQuality = 9 - floor($quality / 10);
                imagepng($newImage, $destination, $pngQuality);
                break;
            case 'image/gif':
                imagegif($newImage, $destination);
                break;
        }
        $filesize = filesize($destination);
        $quality -= 5;
        if ($quality < 10) {
            break;
        }
    } while ($filesize > $maxSize);

    // ทำลาย resource
    imagedestroy($image);
    imagedestroy($newImage);

    // คืนค่า true / false ว่าขนาด <= $maxSize หรือไม่
    return $filesize <= $maxSize;
}

/**
 * เมื่อมีการส่งข้อมูลแบบ POST (กรณีกดปุ่ม 'อัปโหลด')
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // โฟลเดอร์ใน select แรก
    $first_folder = $_POST['first_folder'] ?? '';
    // โฟลเดอร์ใน select ที่สอง (project)
    $second_folder = $_POST['second_folder'] ?? '';

    // ป้องกันการใช้ ../ (Path Traversal)
    if (strpos($first_folder, '..') !== false || strpos($second_folder, '..') !== false) {
        $error = "โฟลเดอร์ไม่ถูกต้อง";
    } else {
        // สร้าง path ปลายทาง เช่น D:\Project Data\<first_folder>\Project\<second_folder>\Engineering\Pic\
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

        // ตรวจสอบโฟลเดอร์ ถ้าไม่มีให้สร้าง
        if (!is_dir($final_path)) {
            if (!@mkdir($final_path, 0777, true)) {
                $error = "ไม่สามารถสร้างโฟลเดอร์ปลายทางได้ (ตรวจสอบสิทธิ์)";
            }
        }

        // ตรวจสอบว่า $final_path อยู่ใน realpath ของ upload_directory หรือไม่
        $real_base = realpath($config['upload_directory']);
        $real_target_dir = realpath($final_path);

        if ($real_target_dir === false || strpos($real_target_dir, $real_base) !== 0) {
            $error = "ไม่สามารถเข้าถึงโฟลเดอร์ปลายทางได้ (path ผิด หรือ permission ไม่พอ)";
        } else {
            // ตรวจสอบไฟล์ image
            if (isset($_FILES['image'])) {
                $file_error = $_FILES['image']['error'];
                if ($file_error === UPLOAD_ERR_OK) {
                    // *** ตัดการตรวจสอบขนาดไฟล์ $_FILES['image']['size'] ออกไป ***
                    // เตรียมตั้งชื่อไฟล์ใหม่ (วันที่ + username + ลำดับ)
                    $username = $_SESSION['username'] ?? 'unknown';
                    $today = date("Ymd");
                    $count = 0; 
                    
                    // นับจำนวนไฟล์ที่ user นี้อัปโหลดวันนี้จาก upload_log.txt
                    if (file_exists($config['upload_log'])) {
                        $log_lines = file($config['upload_log'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        foreach ($log_lines as $line) {
                            // รูปแบบ: filename|timestamp|username|folder
                            $parts = explode("|", $line);
                            if (count($parts) === 4) {
                                $log_timestamp = $parts[1];
                                $log_user = $parts[2];
                                // แปลงเป็นรูปแบบ Ymd เพื่อตรวจสอบว่าเป็นวันเดียวกันหรือไม่
                                $log_date = date("Ymd", $log_timestamp);
                                if ($log_user === $username && $log_date === $today) {
                                    $count++;
                                }
                            }
                        }
                    }

                    $count++;
                    $seq = str_pad($count, 3, "0", STR_PAD_LEFT);
                    $original_name = $_FILES['image']['name'];
                    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

                    // ตั้งชื่อไฟล์ใหม่
                    $new_filename = "{$today}-{$username}-{$seq}.{$extension}";
                    $temp_target = $real_target_dir . DIRECTORY_SEPARATOR . $new_filename;

                    // ย้ายไฟล์จาก temp ไปยังโฟลเดอร์ปลายทาง
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $temp_target)) {
                        // เรียก compressImage ให้เหลือ <= 2MB (ถ้าทำไม่ได้จะลบไฟล์ทิ้ง)
                        if (compressImage($temp_target, $temp_target, 2 * 1024 * 1024)) {
                            // สำเร็จ - บันทึก log
                            $chosen_path = $first_folder 
                                         . "\\Project\\" 
                                         . $second_folder 
                                         . "\\Engineering\\Pic";
                            file_put_contents(
                                $config['upload_log'], 
                                $new_filename . "|" . time() . "|" . $username . "|" . $chosen_path . "\n", 
                                FILE_APPEND
                            );
                            $success = "อัปโหลดและบีบอัดรูปภาพสำเร็จ";
                        } else {
                            // หากบีบอัดไม่สำเร็จ
                            unlink($temp_target);
                            $error = "ไม่สามารถบีบอัดรูปภาพให้มีขนาดต่ำกว่า 2MB ได้";
                        }
                    } else {
                        $error = "ไม่สามารถย้ายไฟล์ไปยังโฟลเดอร์ปลายทางได้ (ตรวจสอบ permission)";
                    }
                } else {
                    // จัดการ error code อื่นๆ
                    switch ($file_error) {
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $error = "ไฟล์ที่อัปโหลดมีขนาดใหญ่เกินกำหนดใน php.ini / form";
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $error = "ไฟล์ถูกอัปโหลดมาไม่ครบ";
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $error = "ไม่มีไฟล์ถูกอัปโหลด";
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $error = "ไม่มีโฟลเดอร์ชั่วคราวบนเซิร์ฟเวอร์";
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $error = "ไม่สามารถเขียนไฟล์ลงดิสก์ได้";
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $error = "การอัปโหลดไฟล์ถูกยกเลิกโดยส่วนขยาย PHP";
                            break;
                        default:
                            $error = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์ (รหัส: $file_error)";
                            break;
                    }
                }
            } else {
                $error = "ไม่มีไฟล์ถูกส่งมา";
            }
        }
    }
}

/**
 * ส่วนโหลดโฟลเดอร์ระดับแรก (year folders) มาใส่ใน select
 */
$upload_root = rtrim($config['upload_directory'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$first_level_folders = [];
if (is_dir($upload_root)) {
    $dirs = scandir($upload_root);
    $folder_times = [];
    foreach ($dirs as $d) {
        if ($d !== '.' && $d !== '..') {
            $path = $upload_root . $d;
            if (is_dir($path)) {
                // เก็บเวลาสร้าง/แก้ไขโฟลเดอร์ เพื่อจะได้ sort ได้
                $folder_times[$d] = filemtime($path);
            }
        }
    }
    // เรียงจากใหม่ -> เก่า
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
    <h1>อัปโหลดรูปภาพ</h1>
    <div class="nav-links">
        <a href="dashboard.php">ย้อนกลับ</a> 
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="message" style="color:red;">
            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="message" style="color:green;">
            <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <!-- เลือกโฟลเดอร์ -->
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
            <option value="">-กรุณาเลือก Project-</option>
        </select>
    </div>

    <div id="selected-info" style="margin-top:10px; color:blue;"></div>
    <div id="fullpath-info" style="margin-top:10px; color:green; font-weight: bold;"></div>
    <div id="fullpath-check" style="margin-top:5px; color:red;"></div>

    <!-- ฟอร์มอัปโหลด -->
    <form method="post" enctype="multipart/form-data" id="upload-form" style="margin-top:20px;">
        <input type="hidden" name="first_folder" value="">
        <input type="hidden" name="second_folder" value="">

        <label>ถ่ายรูปหรือเลือกรูปภาพ:</label>
        <input 
            type="file" 
            name="image" 
            required
            accept="image/*"
        >
        <button type="submit">อัปโหลด</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const firstSelect = document.getElementById('first_select');
    const secondSelect = document.getElementById('second_select');
    const selectedInfo = document.getElementById('selected-info');
    const fullPathInfo = document.getElementById('fullpath-info');
    const fullPathCheck = document.getElementById('fullpath-check');

    const firstFolderInput = document.querySelector('input[name="first_folder"]');
    const secondFolderInput = document.querySelector('input[name="second_folder"]');

    // เมื่อเลือกโฟลเดอร์ปี (first_select)
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

        // โหลด sub-folders (Project) ผ่าน Ajax ไปยัง list_folders.php
        const path = chosenFirst + "\\Project"; 
        let subFolders = await loadSubFolders(path);
        renderSecondLevel(subFolders);

        updateSelectedInfo();
        updateFullPathInfo();
    });

    // เมื่อเลือกโฟลเดอร์ Project
    secondSelect.addEventListener('change', () => {
        const chosenSecond = secondSelect.value;
        secondFolderInput.value = chosenSecond;
        updateSelectedInfo();
        updateFullPathInfo();
    });

    // เรียก list_folders.php เพื่อคืนค่า subfolders ใน path ที่ส่งไป
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

    // เติมตัวเลือกใน select ที่สอง (secondSelect)
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

    // อัปเดตข้อความแสดงโฟลเดอร์ที่เลือก
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

    // อัปเดต Full Path
    function updateFullPathInfo() {
        const f1 = firstFolderInput.value;
        const f2 = secondFolderInput.value;
        if (!f1) {
            fullPathInfo.textContent = "";
            fullPathCheck.textContent = "";
            return;
        }

        let base = "<?php echo addslashes($config['upload_directory']); ?>";
        // แก้ backslash สำหรับจาวาสคริปต์
        base = base.replace(/\\/g, "\\\\"); 

        let fullPath = base + f1 + "\\Project\\";
        if (f2) {
            fullPath += f2 + "\\";
        }
        fullPath += "Engineering\\Pic\\";

        fullPathInfo.textContent = "Full Path: " + fullPath;
        checkPathExists(fullPath);
    }

    // เรียก check_path.php เพื่อดูว่า path นี้มีอยู่จริงหรือไม่
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

    // *** ถ้าต้องการตัดการตรวจสอบขนาดไฟล์ฝั่ง client ก็ไม่ต้องเขียนฟังก์ชันจับ event ตรงนี้ ***
    // document.getElementById('upload-form').addEventListener('submit', (e) => {
    //     ...
    // });
});
</script>
</body>
</html>
