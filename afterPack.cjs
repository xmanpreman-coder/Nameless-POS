// afterPack.cjs - Script untuk copy storage setelah build Electron (CommonJS)
const fs = require('fs-extra');
const path = require('path');

module.exports = async function(context) {
  const appOutDir = context.appOutDir;
  const storageSrc = path.join(appOutDir, 'storage', 'app', 'public');
  const storageDest = path.join(appOutDir, 'public', 'storage');

  console.log('📦 AfterPack: Copying storage files...');
  console.log('   Source:', storageSrc);
  console.log('   Destination:', storageDest);

  try {
    await fs.ensureDir(storageDest);
    if (await fs.pathExists(storageSrc)) {
      await fs.copy(storageSrc, storageDest, { overwrite: true, errorOnExist: false });
      console.log('✅ Storage files copied successfully!');
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
