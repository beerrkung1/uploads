<?php
session_start();
$config = include 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

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

        // สร้าง path หากไม่มี
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
                    $filename = basename($original_name);
                    $target = $real_target_dir . DIRECTORY_SEPARATOR . $filename;

                    // ป้องกันชื่อซ้ำ
                    if (file_exists($target)) {
                        $filename = time() . "_" . $filename;
                        $target = $real_target_dir . DIRECTORY_SEPARATOR . $filename;
                    }

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                        $chosen_path = $first_folder . "\\Project\\" . $second_folder . "\\Engineering\\Pic";
                        file_put_contents($config['upload_log'], 
                            $filename . "|" . time() . "|" . $_SESSION['username'] . "|" . $chosen_path . "\n", 
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

    <div id="step1-container">
        <h3>เลือกโฟลเดอร์จาก D:\Project Data\</h3>
    </div>

    <div id="step2-container" style="display:none;">
        <h3>เลือกโฟลเดอร์จาก D:\Project Data\<folderแรก>\Project\</h3>
    </div>

    <div id="final-container" style="display:none;">
        <p>ไม่มีโฟลเดอร์ย่อยอีกแล้ว สามารถอัปโหลดไฟล์ได้</p>
    </div>

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
    const step1Container = document.getElementById('step1-container');
    const step2Container = document.getElementById('step2-container');
    const finalContainer = document.getElementById('final-container');

    const firstFolderInput = document.querySelector('input[name="first_folder"]');
    const secondFolderInput = document.querySelector('input[name="second_folder"]');

    // โหลดโฟลเดอร์ระดับแรกจาก D:\Project Data\
    loadSubFolders("").then(folders => {
        renderCheckboxes(step1Container, folders, 'first');
    });

    // เมื่อเลือกโฟลเดอร์ระดับแรก
    step1Container.addEventListener('change', async (e) => {
        if (e.target.classList.contains('folder-checkbox-first')) {
            const chosen = e.target.value;
            firstFolderInput.value = chosen;

            // โหลดโฟลเดอร์ระดับสองจาก D:\Project Data\<first_folder>\Project\
            const path = chosen + "\\Project";
            let subFolders = await loadSubFolders(path);

            // ซ่อน step1 แสดง step2
            step1Container.innerHTML = "";
            step1Container.style.display = "none";

            step2Container.style.display = "block";
            renderCheckboxes(step2Container, subFolders, 'second');
        }
    });

    // เมื่อเลือกโฟลเดอร์ระดับสอง
    step2Container.addEventListener('change', (e) => {
        if (e.target.classList.contains('folder-checkbox-second')) {
            const chosen = e.target.value;
            secondFolderInput.value = chosen;

            // ไม่มี subfolder ต่อไป ให้แสดง finalContainer
            step2Container.innerHTML = "";
            step2Container.style.display = "none";

            finalContainer.style.display = "block";
        }
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

    function renderCheckboxes(container, folders, level) {
        container.innerHTML = "";
        if (folders.length > 0) {
            folders.forEach(folder => {
                const label = document.createElement('label');
                label.style.display = 'block';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.value = folder;
                checkbox.classList.add(`folder-checkbox-${level}`);
                label.appendChild(checkbox);
                label.appendChild(document.createTextNode(" " + folder));
                container.appendChild(label);
            });
        } else {
            container.innerHTML = "<p>ไม่มีโฟลเดอร์ในระดับนี้</p>";
        }
    }
});
</script>
</body>
</html>
