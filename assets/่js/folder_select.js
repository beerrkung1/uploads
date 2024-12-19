document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('folder-select-container');

    container.addEventListener('change', async (e) => {
        if (e.target.classList.contains('folder-select')) {
            // ลบ select ด้านล่างทั้งหมดออกก่อน (ในกรณีที่ผู้ใช้เลือกโฟลเดอร์ใหม่)
            let next = e.target.nextElementSibling;
            while (next) {
                const toRemove = next;
                next = next.nextElementSibling;
                if (toRemove.classList.contains('folder-select')) {
                    container.removeChild(toRemove);
                }
            }

            const selectedPath = getSelectedPath();
            if (!selectedPath) return;

            const formData = new FormData();
            formData.append('path', selectedPath);

            let response = await fetch('list_folders.php', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                let data = await response.json();
                if (data.folders && data.folders.length > 0) {
                    // มีซับโฟลเดอร์ สร้าง select ใหม่
                    const newSelect = document.createElement('select');
                    newSelect.classList.add('folder-select');
                    newSelect.name = "folder_select[]";
                    let defaultOption = document.createElement('option');
                    defaultOption.value = "";
                    defaultOption.textContent = "-- กรุณาเลือกโฟลเดอร์ย่อย --";
                    newSelect.appendChild(defaultOption);

                    data.folders.forEach(folder => {
                        let opt = document.createElement('option');
                        opt.value = folder;
                        opt.textContent = folder;
                        newSelect.appendChild(opt);
                    });

                    container.appendChild(newSelect);
                }
            }
        }
    });

    function getSelectedPath() {
        // รวมค่าของ select ทั้งหมด
        const selects = container.querySelectorAll('.folder-select');
        let pathParts = [];
        for (const s of selects) {
            if (s.value) {
                pathParts.push(s.value);
            } else {
                // ถ้า select ปัจจุบันยังไม่ได้เลือกค่า ให้หยุด
                break;
            }
        }
        // ใช้ backslash (Windows) เชื่อม path
        return pathParts.join('\\');
    }

    // เก็บค่าที่เลือกสุดท้ายก่อน submit
    const form = document.querySelector('form#upload-form');
    form.addEventListener('submit', () => {
        const hiddenInput = form.querySelector('input[name="final_folder"]');
        hiddenInput.value = getSelectedPath();
    });
});
