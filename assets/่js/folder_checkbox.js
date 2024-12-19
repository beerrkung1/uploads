document.addEventListener('DOMContentLoaded', () => {
    const folderContainer = document.getElementById('folder-container');
    const backBtn = document.getElementById('back-btn');
    let currentPath = ""; // path ที่เลือก ณ ปัจจุบัน

    // เมื่อคลิก checkbox เลือกโฟลเดอร์
    folderContainer.addEventListener('change', async (e) => {
        if (e.target.classList.contains('folder-checkbox')) {
            // เมื่อเลือกโฟลเดอร์ในระดับนี้แล้ว ให้ซ่อนโฟลเดอร์อื่นทั้งหมด และโหลด subfolder ของโฟลเดอร์ที่เลือก
            const chosenFolder = e.target.value;
            // ปรับ currentPath
            currentPath = (currentPath ? currentPath + "\\" : "") + chosenFolder;

            // เรียก AJAX หาซับโฟลเดอร์ของ currentPath
            let subfolders = await loadSubFolders(currentPath);
            renderFolders(subfolders);

            // ถ้ามี currentPath แสดงปุ่ม back ได้
            backBtn.style.display = currentPath ? 'inline-block' : 'none';
        }
    });

    // ปุ่ม Back
    backBtn.addEventListener('click', async () => {
        // ถอยกลับ 1 ระดับ
        let parts = currentPath.split('\\');
        parts.pop(); // ลบโฟลเดอร์สุดท้าย
        currentPath = parts.join('\\');

        // โหลดโฟลเดอร์ในระดับนี้
        let subfolders = await loadSubFolders(currentPath);
        renderFolders(subfolders);

        // ถ้า currentPath ว่าง แปลว่าอยู่ระดับ root ไม่ต้องแสดง back
        backBtn.style.display = currentPath ? 'inline-block' : 'none';
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
            if (data.folders) {
                return data.folders;
            }
        }
        return [];
    }

    function renderFolders(folders) {
        // ลบโฟลเดอร์เก่า
        folderContainer.innerHTML = "";

        if (folders.length > 0) {
            // ถ้ามี subfolders แสดงเป็น checkbox ให้เลือก
            folders.forEach(folder => {
                const label = document.createElement('label');
                label.style.display = 'block';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.classList.add('folder-checkbox');
                checkbox.value = folder;
                label.appendChild(checkbox);
                label.appendChild(document.createTextNode(" " + folder));
                folderContainer.appendChild(label);
            });
        } else {
            // ไม่มี subfolder แล้ว แสดงว่า currentPath คือ path สุดท้าย
            // ผู้ใช้สามารถอัปโหลดไฟล์ได้
            // แสดงข้อความว่าไม่มีโฟลเดอร์ย่อยแล้ว
            const p = document.createElement('p');
            p.textContent = "ไม่มีโฟลเดอร์ย่อยอีกแล้ว คุณสามารถอัปโหลดไฟล์ในโฟลเดอร์นี้";
            folderContainer.appendChild(p);
        }

        // อัปเดต hidden input สำหรับอัปโหลด
        const finalInput = document.querySelector('input[name="final_folder"]');
        finalInput.value = currentPath;
    }

    // เริ่มต้นโหลดโฟลเดอร์ระดับบนสุด
    (async function init() {
        let rootFolders = await loadSubFolders("");
        renderFolders(rootFolders);
        backBtn.style.display = 'none';
    })();
});
