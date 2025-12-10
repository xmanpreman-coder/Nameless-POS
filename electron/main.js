import { app, BrowserWindow } from 'electron';
import path from 'path';
import { startPhpServer, stopPhpServer } from './spawn-php.js';
import { autoUpdater } from 'electron-updater';

// Auto-update configuration (requires publish target - e.g., GitHub Releases)
autoUpdater.autoDownload = false;
autoUpdater.logger = require('electron-log');
autoUpdater.logger.transports.file.level = 'info';


let mainWindow;

function createMainWindow() {
  mainWindow = new BrowserWindow({
    width: 1200,
    height: 800,
    webPreferences: {
      contextIsolation: true,
      nodeIntegration: false,
    },
  });

  // Load the bundled Laravel app via the embedded server
  const url = 'http://127.0.0.1:8000';
  mainWindow.loadURL(url).catch((err) => console.error('Failed to load URL', err));
  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

app.whenReady().then(async () => {
  try {
    await startPhpServer({
      bundlePath: path.join(process.resourcesPath || process.cwd(), 'resources', 'bundle'),
      port: 8000,
    });
  } catch (e) {
    console.error('Failed to start bundled PHP', e);
  }

  createMainWindow();
  // Check for updates in background (non-blocking)
  autoUpdater.checkForUpdatesAndNotify().catch((err) => {
    console.warn('Auto-updater check failed:', err);
  });
  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) createMainWindow();
  });
});

app.on('before-quit', async (e) => {
  await stopPhpServer();
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});
