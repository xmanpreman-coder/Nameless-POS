const { app, BrowserWindow, dialog } = require('electron');
const path = require('path');
const LaravelServer = require('./LaravelServer');
const DatabaseManager = require('./DatabaseManager'); // Assuming it's needed

let mainWindow;
let laravelServer;

async function createWindow() {
  console.log('🚀 Creating window...');
  const startTime = Date.now();
  
  mainWindow = new BrowserWindow({
    width: 1200,
    height: 800,
    show: false, // Don't show until ready
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'), // If you have a preload script
      nodeIntegration: false, // Keep false for security
      contextIsolation: true, // Keep true for security
      // Allow loading insecure content (for localhost HTTP) - be careful in production
      webSecurity: false 
    }
  });

  // Load an initial loading screen or a blank page
  mainWindow.loadURL('about:blank'); 

  // Initialize and start Laravel server
  laravelServer = new LaravelServer();
  
  try {
    console.log('⏳ Starting Laravel server...');
    const serverStart = Date.now();
    
    await laravelServer.start();
    const serverTime = Date.now() - serverStart;
    console.log(`✅ Laravel server started on port ${laravelServer.getPort()} (${serverTime}ms)`);

    // Once server is ready, load the Laravel app with the correct port
    const port = laravelServer.getPort();
    console.log(`📡 Loading app from http://127.0.0.1:${port}...`);
    const loadStart = Date.now();
    
    mainWindow.loadURL(`http://127.0.0.1:${port}`);
    
    mainWindow.once('ready-to-show', () => {
      const loadTime = Date.now() - loadStart;
      const totalTime = Date.now() - startTime;
      console.log(`📺 Window ready (${loadTime}ms) - Total startup: ${totalTime}ms`);
      mainWindow.show();
    });

  } catch (error) {
    dialog.showErrorBox(
      'Application Error',
      `Failed to start the application. Please check the logs.\nError: ${error.message}`
    );
    console.error('❌ Failed to start application:', error);
    app.quit();
  }

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

app.whenReady().then(createWindow);

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('activate', () => {
  if (BrowserWindow.getAllWindows().length === 0) {
    createWindow();
  }
});

app.on('before-quit', async () => {
  if (laravelServer && laravelServer.isRunning()) {
    console.log('Stopping Laravel server before quitting...');
    await laravelServer.stop();
  }
});
