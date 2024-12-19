document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('folder-select-container');

    // เมื่อมีการเปลี่ยนค่าใน select ใดๆ
    container.addEventListener('change', async (e) => {
        if (e.target.classList.contains('folder-select')) {
            // ลบ select ที่อยู่ถัดจาก select ปัจจุบันออกทั้งหมดก่อน (กรณีเปลี่ยน folder ใหม่)
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

            // เรียก AJAX เพื่อดึง subfolder ของ path ปัจจุบัน
            const formData = new FormData();
            formData.append('path', selectedPath);

            let response = await fetch('list_folders.php', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                let data = await response.json();
                if (data.folders && data.folders.length > 0) {
                    // สร้าง select ใหม่ให้เลือก subfolder
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
        // รวมค่าของ select ทุกตัวเพื่อสร้าง path ย่อย
        const selects = container.querySelectorAll('.folder-select');
        let pathParts = [];
        for (const s of selects) {
            if (s.value) {
                pathParts.push(s.value);
            } else {
                // ถ้ายังไม่ได้เลือกหรือ select ไม่มีค่า ให้หยุด
                break;
            }
        }
        return pathParts.join('\\');
    }

    // เมื่อ submit form ให้บันทึก path ลงใน hidden input
    const form = document.querySelector('form#upload-form');
    form.addEventListener('submit', () => {
        const hiddenInput = form.querySelector('input[name="final_folder"]');
        hiddenInput.value = getSelectedPath();
    });
});
