// afterPack.js - Script untuk copy storage setelah build Electron
const fs = require('fs-extra');
const path = require('path');

exports.default = async function(context) {
  const appOutDir = context.appOutDir;
  const storageSrc = path.join(appOutDir, 'storage', 'app', 'public');
  const storageDest = path.join(appOutDir, 'public', 'storage');

  console.log('📦 AfterPack: Copying storage files...');
  console.log('   Source:', storageSrc);
  console.log('   Destination:', storageDest);

  try {
    // Buat folder destination jika belum ada
    await fs.ensureDir(storageDest);
    
    // Copy semua file dari storage/app/public ke public/storage
    if (await fs.pathExists(storageSrc)) {
      await fs.copy(storageSrc, storageDest, { 
        overwrite: true,
        errorOnExist: false 
      });
      console.log('✅ Storage files copied successfully!');
      
      // List files yang berhasil di-copy
      const files = await fs.readdir(storageDest);
      console.log('   Copied folders/files:', files);
    } else {
      console.log('⚠️  Warning: Storage source path not found');
    }
  } catch (error) {
    console.error('❌ Error copying storage files:', error);
    throw error;
  }
};
