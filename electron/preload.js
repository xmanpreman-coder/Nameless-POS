const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('electronAPI', {
  getPrinters: () => ipcRenderer.invoke('get-printers'),
  print: (options) => ipcRenderer.invoke('print', options),
  backupDatabase: () => ipcRenderer.invoke('backup-database'),
  restoreDatabase: (filePath) => ipcRenderer.invoke('restore-database', filePath),
  // Scanner bridge for hardware input
  onScannerInput: (callback) => ipcRenderer.on('scanner-input', callback),
  sendScannerData: (barcode) => ipcRenderer.invoke('process-scanner-input', barcode)
});
