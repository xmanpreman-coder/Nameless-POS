const { app, BrowserWindow, ipcMain, dialog } = require('electron');
const { autoUpdater } = require('electron-updater');
const path = require('path');
const { spawn } = require('child_process');
const fs = require('fs').promises;

// Disable hardware acceleration to prevent black screens on some systems.
app.disableHardwareAcceleration();

// Keep a reference to the Laravel server process
let laravelServerProcess;

// Check if running in development
const isDev = process.env.NODE_ENV === 'development' || 
              !app.isPackaged ||
              process.defaultApp ||
              /[\\/]electron-prebuilt[\\/]/.test(process.execPath) ||
              /[\\/]electron[\\/]/.test(process.execPath);

// Keep a global reference of the window object
let mainWindow;
let updateWindow;

// Laravel app URL
const LARAVEL_URL = process.env.LARAVEL_URL || 'http://localhost:8000';

// Configure auto-updater
autoUpdater.autoDownload = false;
autoUpdater.autoInstallOnAppQuit = true;

// Check for updates every 1 minute (60000 ms)
const UPDATE_CHECK_INTERVAL = 60000; // 1 minute
let updateCheckInterval;

function createWindow() {
  // Create the browser window
  mainWindow = new BrowserWindow({
    width: 1400,
    height: 900,
    minWidth: 1024,
    minHeight: 768,
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
      enableRemoteModule: false,
      preload: path.join(__dirname, 'preload.js'),
      webSecurity: false // Disabled for local development/wrapper
    },
    icon: path.join(__dirname, 'build', 'icon.ico'),
    show: false, // Don't show until ready
    frame: true,
    titleBarStyle: 'default'
  });

  // Load the Laravel application
  // Add a small delay to ensure the Laravel server is ready
  setTimeout(() => {
    mainWindow.loadURL(LARAVEL_URL);
  }, 3000); // 3-second delay

  // Show window when ready to prevent visual flash
  mainWindow.once('ready-to-show', () => {
    mainWindow.show();
    
    // Focus the window
    if (isDev) {
      mainWindow.webContents.openDevTools();
    }
  });

  // Handle window closed
  mainWindow.on('closed', () => {
    mainWindow = null;
  });

  // Handle navigation
  mainWindow.webContents.on('will-navigate', (event, navigationUrl) => {
    const parsedUrl = new URL(navigationUrl);
    const parsedLaravelUrl = new URL(LARAVEL_URL);
    
    // Prevent navigation to external URLs
    if (parsedUrl.origin !== parsedLaravelUrl.origin) {
      event.preventDefault();
      // Optionally open in external browser
      require('electron').shell.openExternal(navigationUrl);
    }
  });

  // Handle new window (for links that open in new tab)
  mainWindow.webContents.setWindowOpenHandler(({ url }) => {
    const parsedUrl = new URL(url);
    const parsedLaravelUrl = new URL(LARAVEL_URL);
    
    if (parsedUrl.origin === parsedLaravelUrl.origin) {
      // This is an internal link. Allow it to open in a new window.
      // This will fix the print preview issue.
      return { action: 'allow' };
    } else {
      // For all external links, open them in the user's default browser.
      require('electron').shell.openExternal(url);
      return { action: 'deny' };
    }
  });

  // Handle certificate errors (for local development)
  mainWindow.webContents.on('certificate-error', (event, url, error, certificate, callback) => {
    if (url.startsWith('http://localhost') || url.startsWith('http://127.0.0.1')) {
      // Ignore certificate errors for localhost
      event.preventDefault();
      callback(true);
    } else {
      callback(false);
    }
  });

  // Handle page errors
  mainWindow.webContents.on('did-fail-load', (event, errorCode, errorDescription, validatedURL) => {
    if (errorCode === -106) {
      // ERR_INTERNET_DISCONNECTED or similar
      showErrorDialog('Connection Error', 
        `Cannot connect to Laravel application at ${LARAVEL_URL}.\n\n` + 
        'Please make sure:\n' + 
        '1. The application is starting correctly.\n' + 
        '2. No other service is using port 8000.\n' + 
        '3. No firewall is blocking the connection.');
    }
  });
}

function createUpdateWindow() {
  updateWindow = new BrowserWindow({
    width: 500,
    height: 400,
    resizable: false,
    frame: false,
    transparent: true,
    alwaysOnTop: true,
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
      preload: path.join(__dirname, 'preload.js')
    },
    show: false
  });

  updateWindow.loadFile('updater.html');
  
  updateWindow.once('ready-to-show', () => {
    updateWindow.show();
    updateWindow.center();
  });

  updateWindow.on('closed', () => {
    updateWindow = null;
  });
}

function showErrorDialog(title, message) {
  if (mainWindow) {
    dialog.showMessageBox(mainWindow, {
      type: 'error',
      title: title,
      message: message,
      buttons: ['OK']
    });
  }
}

// Auto-updater events
autoUpdater.on('checking-for-update', () => {
  console.log('Checking for update...');
  sendStatusToWindow('checking-for-update');
});

autoUpdater.on('update-available', (info) => {
  console.log('Update available:', info);
  sendStatusToWindow('update-available', info);
  
  // Show update window
  if (!updateWindow) {
    createUpdateWindow();
  }
  
  // Ask user if they want to download
  if (mainWindow) {
    dialog.showMessageBox(mainWindow, {
      type: 'info',
      title: 'Update Available',
      message: `A new version (${info.version}) is available.`, 
      detail: 'Would you like to download and install it now?',
      buttons: ['Download Now', 'Later'],
      defaultId: 0,
      cancelId: 1
    }).then((result) => {
      if (result.response === 0) {
        // User wants to download
        autoUpdater.downloadUpdate();
        sendStatusToWindow('download-started');
      }
    });
  }
});

autoUpdater.on('update-not-available', (info) => {
  console.log('Update not available:', info);
  sendStatusToWindow('update-not-available', info);
});

autoUpdater.on('error', (err) => {
  console.error('Error in auto-updater:', err);
  sendStatusToWindow('error', err.message);
  
  if (updateWindow) {
    updateWindow.webContents.send('update-error', err.message);
  }
});

autoUpdater.on('download-progress', (progressObj) => {
  let log_message = "Download speed: " + progressObj.bytesPerSecond;
  log_message = log_message + ' - Downloaded ' + progressObj.percent + '%';
  log_message = log_message + ' (' + progressObj.transferred + "/" + progressObj.total + ')';
  console.log(log_message);
  
  sendStatusToWindow('download-progress', progressObj);
  
  if (updateWindow) {
    updateWindow.webContents.send('download-progress', progressObj);
  }
});

autoUpdater.on('update-downloaded', (info) => {
  console.log('Update downloaded:', info);
  sendStatusToWindow('update-downloaded', info);
  
  if (updateWindow) {
    updateWindow.webContents.send('update-downloaded', info);
  }
  
  // Ask user if they want to install now
  if (mainWindow) {
    dialog.showMessageBox(mainWindow, {
      type: 'info',
      title: 'Update Ready',
      message: 'Update downloaded successfully!',
      detail: 'The application will restart to install the update.',
      buttons: ['Restart Now', 'Later'],
      defaultId: 0,
      cancelId: 1
    }).then((result) => {
      if (result.response === 0) {
        // Restart and install
        autoUpdater.quitAndInstall(false, true);
      }
    });
  }
});

function sendStatusToWindow(status, data = null) {
  if (mainWindow && mainWindow.webContents) {
    mainWindow.webContents.send('updater-status', { status, data });
  }
}

// IPC handlers
ipcMain.handle('backup-database', async () => {
  try {
    const home = app.getPath('home');
    const dbDirectory = path.join(home, '.nameless-pos');
    const sourceDbPath = path.join(dbDirectory, 'database.sqlite');

    // Check if the source database file exists
    try {
      await fs.access(sourceDbPath);
    } catch (error) {
      dialog.showErrorBox('Backup Error', 'Database file not found. Have you run the application and generated data yet?');
      return { success: false, error: 'File not found' };
    }

    const { filePath } = await dialog.showSaveDialog(mainWindow, {
      title: 'Backup Database',
      defaultPath: `database-backup-${new Date().toISOString().slice(0, 10)}.sqlite`,
      filters: [
        { name: 'SQLite Databases', extensions: ['sqlite'] },
        { name: 'All Files', extensions: ['*'] }
      ]
    });

    if (filePath) {
      await fs.copyFile(sourceDbPath, filePath);
      dialog.showMessageBox(mainWindow, {
        type: 'info',
        title: 'Backup Successful',
        message: `Database successfully backed up to:\n${filePath}`
      });
      return { success: true };
    } else {
      // User cancelled the save dialog
      return { success: false, error: 'Backup cancelled' };
    }
  } catch (error) {
    console.error('Backup failed:', error);
    dialog.showErrorBox('Backup Error', `An error occurred during the backup: ${error.message}`);
    return { success: false, error: error.message };
  }
});

ipcMain.handle('check-for-updates', async () => {
  try {
    await autoUpdater.checkForUpdates();
    return { success: true };
  } catch (error) {
    return { success: false, error: error.message };
  }
});

ipcMain.handle('download-update', async () => {
  try {
    autoUpdater.downloadUpdate();
    return { success: true };
  } catch (error) {
    return { success: false, error: error.message };
  }
});

ipcMain.handle('install-update', async () => {
  try {
    autoUpdater.quitAndInstall(false, true);
    return { success: true };
  } catch (error) {
    return { success: false, error: error.message };
  }
});

ipcMain.handle('close-update-window', () => {
  if (updateWindow) {
    updateWindow.close();
  }
});

ipcMain.handle('get-app-version', () => {
  return app.getVersion();
});

// App event handlers
app.whenReady().then(() => {
  // Start the Laravel server
  const laravelPath = path.join(__dirname, '..'); // Assumes Laravel is in the parent directory
  
  laravelServerProcess = spawn('php', ['artisan', 'serve'], {
      cwd: laravelPath,
      shell: true // Use shell on Windows
  });

  laravelServerProcess.stdout.on('data', (data) => {
      console.log(`Laravel Server: ${data}`);
  });

  laravelServerProcess.stderr.on('data', (data) => {
      console.error(`Laravel Server Error: ${data}`);
  });

  createWindow();

  // Start checking for updates
  if (!isDev) {
    // Check immediately
    autoUpdater.checkForUpdates();
    
    // Then check every interval
    updateCheckInterval = setInterval(() => {
      autoUpdater.checkForUpdates();
    }, UPDATE_CHECK_INTERVAL);
  }

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
      createWindow();
    }
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('before-quit', () => {
  console.log('Killing Laravel server process...');
  // Clear update check interval
  if (updateCheckInterval) {
    clearInterval(updateCheckInterval);
  }
  // Kill the Laravel server process
  if (laravelServerProcess) {
    laravelServerProcess.kill();
  }
});

// Security: Prevent new window creation
app.on('web-contents-created', (event, contents) => {
  contents.on('new-window', (event, navigationUrl) => {
    event.preventDefault();
    require('electron').shell.openExternal(navigationUrl);
  });
});

// Handle uncaught exceptions
process.on('uncaughtException', (error) => {
  console.error('Uncaught Exception:', error);
});