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
            // ได้โฟลเดอร์แรกแล้ว
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
            // ไม่มีโฟลเดอร์ย่อยแล้ว กรณีนี้อาจใช้ finalContainer ได้เลย
            container.innerHTML = "<p>ไม่มีโฟลเดอร์ในระดับนี้</p>";
        }
    }
});
