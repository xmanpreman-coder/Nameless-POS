const { app, BrowserWindow, ipcMain, dialog } = require('electron');
const { autoUpdater } = require('electron-updater');
const path = require('path');
const LaravelServer = require('./LaravelServer');
const DatabaseManager = require('./DatabaseManager');

let laravelServer = null;
let databaseManager = null;

function createWindow () {
  const win = new BrowserWindow({
    width: 1200,
    height: 800,
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
      contextIsolation: true,
      nodeIntegration: false
    }
  });

  const url = process.env.NAMELESS_URL || 'http://localhost:8000';
  win.loadURL(url);

  // optional: open devtools when env set
  if (process.env.ELECTRON_DEV === '1') {
    win.webContents.openDevTools();
  }

  // Expose printers related handlers
  ipcMain.handle('get-printers', () => {
    try {
      return win.webContents.getPrinters();
    } catch (e) {
      return { error: e.message };
    }
  });

  ipcMain.handle('print', async (event, options) => {
    return new Promise((resolve) => {
      try {
        win.webContents.print(options || {}, (success, failureReason) => {
          resolve({ success, failureReason });
        });
      } catch (e) {
        resolve({ success: false, failureReason: e.message });
      }
    });
  });

  // Database backup/restore handlers
  ipcMain.handle('backup-database', async () => {
    if (!databaseManager) {
      databaseManager = new DatabaseManager();
    }
    const result = await databaseManager.backupDatabase();
    
    // Show success/error dialog
    if (result.success) {
      dialog.showMessageBox(win, {
        type: 'info',
        title: 'Backup Successful',
        message: result.message,
        buttons: ['OK', 'Open Backup Folder']
      }).then(response => {
        if (response.response === 1) {
          const { shell } = require('electron');
          shell.showItemInFolder(result.filePath);
        }
      });
    } else {
      dialog.showErrorBox('Backup Error', result.message);
    }
    
    return result;
  });

  ipcMain.handle('restore-database', async (event, backupFilePath) => {
    if (!databaseManager) {
      databaseManager = new DatabaseManager();
    }
    
    const result = await databaseManager.restoreDatabase(backupFilePath);
    
    if (result.success) {
      dialog.showMessageBox(win, {
        type: 'info',
        title: 'Restore Successful',
        message: result.message + ' App will restart now.',
        buttons: ['OK']
      }).then(() => {
        app.relaunch();
        app.exit(0);
      });
    } else {
      dialog.showErrorBox('Restore Error', result.message);
    }
    
    return result;
  });
}

app.whenReady().then(async () => {
  try {
    // Start Laravel server first
    laravelServer = new LaravelServer();
    await laravelServer.start();
    console.log('[Main] Laravel server started successfully');
    
    // Give server time to fully initialize
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    // Then create window
    createWindow();
  } catch (err) {
    console.error('[Main] Failed to start:', err);
    dialog.showErrorBox('Error', 'Failed to start Nameless POS application');
    app.quit();
  }

  // Check for updates after 5 seconds
  setTimeout(() => {
    autoUpdater.checkForUpdatesAndNotify();
  }, 5000);

  app.on('activate', function () {
    if (BrowserWindow.getAllWindows().length === 0) createWindow();
  });
});

// Auto-updater events
autoUpdater.on('checking-for-update', () => {
  console.log('Checking for update...');
});

autoUpdater.on('update-available', (info) => {
  console.log('Update available.');
  dialog.showMessageBox({
    type: 'info',
    title: 'Update Available',
    message: 'A new version is available. It will be downloaded in the background.',
    buttons: ['OK']
  });
});

autoUpdater.on('update-not-available', (info) => {
  console.log('Update not available.');
});

autoUpdater.on('error', (err) => {
  console.log('Error in auto-updater. ' + err);
});

autoUpdater.on('download-progress', (progressObj) => {
  let log_message = "Download speed: " + progressObj.bytesPerSecond;
  log_message = log_message + ' - Downloaded ' + progressObj.percent + '%';
  log_message = log_message + ' (' + progressObj.transferred + "/" + progressObj.total + ')';
  console.log(log_message);
});

autoUpdater.on('update-downloaded', (info) => {
  console.log('Update downloaded');
  dialog.showMessageBox({
    type: 'info',
    title: 'Update Ready',
    message: 'Update downloaded. The application will restart to apply the update.',
    buttons: ['Restart Now', 'Later']
  }).then((result) => {
    if (result.response === 0) {
      autoUpdater.quitAndInstall();
    }
  });
});

app.on('window-all-closed', async function () {
  // Stop Laravel server
  if (laravelServer) {
    await laravelServer.stop();
  }
  
  if (process.platform !== 'darwin') app.quit();
});
