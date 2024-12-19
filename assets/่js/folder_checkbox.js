document.addEventListener('DOMContentLoaded', () => {
    const folderContainer = document.getElementById('folder-container');
    const backBtn = document.getElementById('back-btn');
    let currentPath = "";

    folderContainer.addEventListener('change', async (e) => {
        if (e.target.classList.contains('folder-checkbox')) {
            // เมื่อเลือกโฟลเดอร์ ให้เจาะลึกลงไป
            const chosenFolder = e.target.value;
            currentPath = (currentPath ? currentPath + "\\" : "") + chosenFolder;
            let subfolders = await loadSubFolders(currentPath);
            renderFolders(subfolders);
            backBtn.style.display = currentPath ? 'inline-block' : 'none';
        }
    });

    backBtn.addEventListener('click', async () => {
        // ถอยกลับขึ้น 1 ระดับ
        let parts = currentPath.split('\\');
        parts.pop();
        currentPath = parts.join('\\');
        let subfolders = await loadSubFolders(currentPath);
        renderFolders(subfolders);
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
            return data.folders || [];
        } else {
            return [];
        }
    }

    function renderFolders(folders) {
        folderContainer.innerHTML = "";
        if (folders.length > 0) {
            // มี subfolder ให้เลือก
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
            // ไม่มี subfolder เพิ่มแล้ว
            const p = document.createElement('p');
            p.textContent = "ไม่มีโฟลเดอร์ย่อย สามารถอัพโหลดไฟล์ในโฟลเดอร์นี้";
            folderContainer.appendChild(p);
        }
        document.querySelector('input[name="final_folder"]').value = currentPath;
    }

    // โหลดโฟลเดอร์ระดับแรก
    (async function init() {
        let rootFolders = await loadSubFolders("");
        renderFolders(rootFolders);
        backBtn.style.display = 'none';
    })();
});
