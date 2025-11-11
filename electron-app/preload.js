const { contextBridge, ipcRenderer } = require('electron');

// Expose protected methods that allow the renderer process to use
// the ipcRenderer without exposing the entire object
contextBridge.exposeInMainWorld('electronAPI', {
  // Updater API
  checkForUpdates: () => ipcRenderer.invoke('check-for-updates'),
  downloadUpdate: () => ipcRenderer.invoke('download-update'),
  installUpdate: () => ipcRenderer.invoke('install-update'),
  closeUpdateWindow: () => ipcRenderer.invoke('close-update-window'),
  getAppVersion: () => ipcRenderer.invoke('get-app-version'),
  
  // Listeners
  onUpdaterStatus: (callback) => {
    ipcRenderer.on('updater-status', (event, data) => callback(data));
  },
  
  onDownloadProgress: (callback) => {
    ipcRenderer.on('download-progress', (event, progress) => callback(progress));
  },
  
  onUpdateDownloaded: (callback) => {
    ipcRenderer.on('update-downloaded', (event, info) => callback(info));
  },
  
  onUpdateError: (callback) => {
    ipcRenderer.on('update-error', (event, error) => callback(error));
  },
  
  // Remove listeners
  removeAllListeners: (channel) => {
    ipcRenderer.removeAllListeners(channel);
  }
});

