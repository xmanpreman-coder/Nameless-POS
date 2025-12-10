import { app, BrowserWindow } from 'electron';
import path from 'path';
import { startPhpServer, stopPhpServer } from './spawn-php.js';
import * as _electronUpdater from 'electron-updater';
import * as _electronLog from 'electron-log';

// Compatibility: `electron-updater` and `electron-log` are CommonJS modules.
// Support both direct and default namespace shapes.
const autoUpdater = _electronUpdater.autoUpdater || (_electronUpdater.default && _electronUpdater.default.autoUpdater);
const electronLog = _electronLog || (_electronLog && _electronLog.default) || console;

// Auto-update configuration (requires publish target - e.g., GitHub Releases)
if (autoUpdater) {
  autoUpdater.autoDownload = false;
  try {
    autoUpdater.logger = electronLog;
    if (autoUpdater.logger && autoUpdater.logger.transports && autoUpdater.logger.transports.file) {
      autoUpdater.logger.transports.file.level = 'info';
    }
  } catch (e) {
    console.warn('Failed to configure autoUpdater logger', e);
  }
}


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
