// Debug script to inspect Tauri API availability
console.log('Checking Tauri availability...');
console.log('window.__TAURI__:', typeof window.__TAURI__);
console.log('window.__TAURI_IPC__:', typeof window.__TAURI_IPC__);

// Try to dynamically import and inspect the modules
(async () => {
  try {
    const dialog = await import('@tauri-apps/plugin-dialog');
    console.log('Dialog module imported:', Object.keys(dialog));
    console.log('dialog.save type:', typeof dialog.save);
  } catch (e) {
    console.error('Failed to import dialog:', e.message);
  }

  try {
    const fs = await import('@tauri-apps/plugin-fs');
    console.log('FS module imported:', Object.keys(fs));
    console.log('fs.writeTextFile type:', typeof fs.writeTextFile);
    console.log('fs.writeBinaryFile type:', typeof fs.writeBinaryFile);
  } catch (e) {
    console.error('Failed to import fs:', e.message);
  }
})();
