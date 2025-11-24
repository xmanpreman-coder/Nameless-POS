const { spawn, exec } = require('child_process');
const path = require('path');
const os = require('os');
const fs = require('fs');

class LaravelServer {
  constructor() {
    this.serverProcess = null;
    this.appPath = path.join(__dirname, '..');
    this.isDev = process.env.ELECTRON_DEV === '1';
  }

  start() {
    return new Promise((resolve, reject) => {
      console.log('[LaravelServer] Starting Laravel dev server...');
      console.log('[LaravelServer] App path:', this.appPath);

      try {
        // Clear caches before starting
        this.clearCaches();

        // Determine PHP path
        const phpPath = process.platform === 'win32' ? 'php' : 'php';

        // Start server
        this.serverProcess = spawn(phpPath, [
          'artisan',
          'serve',
          '--host=localhost',
          '--port=8000',
          '--env=production'
        ], {
          cwd: this.appPath,
          stdio: ['ignore', 'pipe', 'pipe'],
          shell: true
        });

        let output = '';

        this.serverProcess.stdout.on('data', (data) => {
          output += data.toString();
          console.log('[LaravelServer]', data.toString());
          
          // Check if server started successfully
          if (output.includes('started') || output.includes('listening')) {
            resolve(true);
          }
        });

        this.serverProcess.stderr.on('data', (data) => {
          console.error('[LaravelServer ERROR]', data.toString());
        });

        this.serverProcess.on('error', (err) => {
          console.error('[LaravelServer] Failed to start:', err);
          reject(err);
        });

        // Timeout after 10 seconds
        setTimeout(() => {
          if (output.length > 0) {
            resolve(true);
          } else {
            reject(new Error('Server startup timeout'));
          }
        }, 10000);

      } catch (err) {
        console.error('[LaravelServer] Exception:', err);
        reject(err);
      }
    });
  }

  clearCaches() {
    try {
      const commands = [
        'php artisan cache:clear',
        'php artisan config:clear',
        'php artisan view:clear'
      ];

      commands.forEach(cmd => {
        exec(cmd, { cwd: this.appPath }, (err) => {
          if (err) console.log('[LaravelServer] Cache clear:', err.message);
        });
      });
    } catch (err) {
      console.log('[LaravelServer] Cache clear exception:', err.message);
    }
  }

  stop() {
    return new Promise((resolve) => {
      if (this.serverProcess) {
        console.log('[LaravelServer] Stopping server...');
        this.serverProcess.kill();
        this.serverProcess = null;
        setTimeout(resolve, 1000);
      } else {
        resolve();
      }
    });
  }

  isRunning() {
    return this.serverProcess && !this.serverProcess.killed;
  }
}

module.exports = LaravelServer;
