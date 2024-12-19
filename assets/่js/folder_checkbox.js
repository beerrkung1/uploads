document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Loaded, initializing folder structure...');
    const folderContainer = document.getElementById('folder-container');
    const backBtn = document.getElementById('back-btn');
    let currentPath = "";

    folderContainer.addEventListener('change', async (e) => {
        if (e.target.classList.contains('folder-checkbox')) {
            const chosenFolder = e.target.value;
            currentPath = (currentPath ? currentPath + "\\" : "") + chosenFolder;
            console.log('Chosen folder:', currentPath);
            let subfolders = await loadSubFolders(currentPath);
            console.log('Subfolders of', currentPath, ':', subfolders);
            renderFolders(subfolders);
            backBtn.style.display = currentPath ? 'inline-block' : 'none';
        }
    });

    backBtn.addEventListener('click', async () => {
        let parts = currentPath.split('\\');
        parts.pop();
        currentPath = parts.join('\\');
        console.log('Go back, current path:', currentPath);
        let subfolders = await loadSubFolders(currentPath);
        console.log('Subfolders of', currentPath, ':', subfolders);
        renderFolders(subfolders);
        backBtn.style.display = currentPath ? 'inline-block' : 'none';
    });

    async function loadSubFolders(path) {
        console.log('Loading subfolders for path:', path);
        const formData = new FormData();
        formData.append('path', path);
        let response = await fetch('list_folders.php', {
            method: 'POST',
            body: formData
        });
        if (response.ok) {
            let data = await response.json();
            if (data.error) {
                console.error('Error from list_folders.php:', data.error, data);
            }
            return data.folders || [];
        } else {
            console.error('Failed to load subfolders, status:', response.status);
        }
        return [];
    }

    function renderFolders(folders) {
        folderContainer.innerHTML = "";
        if (folders.length > 0) {
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
            const p = document.createElement('p');
            p.textContent = "ไม่มีโฟลเดอร์ย่อย สามารถอัพโหลดไฟล์ได้";
            folderContainer.appendChild(p);
        }
        document.querySelector('input[name="final_folder"]').value = currentPath;
    }

    (async function init() {
        console.log('Init load root folders');
        let rootFolders = await loadSubFolders("");
        console.log('Root folders:', rootFolders);
        renderFolders(rootFolders);
        backBtn.style.display = 'none';
    })();
});
