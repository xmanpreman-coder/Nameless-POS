import { spawn } from 'child_process';
import path from 'path';
import { fileURLToPath } from 'url';
import fs from 'fs';

let phpProcess = null;

export function startPhpServer(options = {}) {
  const __dirname = path.dirname(fileURLToPath(import.meta.url));
  const appRoot = path.join(__dirname, '..');
  const port = options.port || 8000;
  const host = options.host || '127.0.0.1';

  if (phpProcess) {
    console.log('[PHP] Server already running with PID:', phpProcess.pid);
    return Promise.resolve({ pid: phpProcess.pid });
  }

  return new Promise((resolve, reject) => {
    try {
      console.log('[PHP] Starting server:', { appRoot, host, port });
      
      const args = ['artisan', 'serve', '--host=' + host, '--port=' + port, '--env=production'];

      // Try to find PHP - check common locations on Windows
      let phpExe = 'php';
      const potentialPaths = [
        'php', // PATH environment
        path.join(process.env.PROGRAMFILES || 'C:\\Program Files', 'PHP', 'php.exe'),
        path.join(process.env['PROGRAMFILES(X86)'] || 'C:\\Program Files (x86)', 'PHP', 'php.exe'),
        'C:\\php\\php.exe',
        path.join(appRoot, 'php', 'php.exe'),
      ];

      for (const candidate of potentialPaths) {
        if (fs.existsSync(candidate)) {
          phpExe = candidate;
          console.log('[PHP] Found PHP at:', phpExe);
          break;
        }
      }

      console.log('[PHP] Using PHP executable:', phpExe);

      phpProcess = spawn(phpExe, args, {
        stdio: ['ignore', 'pipe', 'pipe'],
        windowsHide: true,
        cwd: appRoot,
        shell: true, // Use shell to handle PATH resolution better
      });

      console.log('[PHP] Process spawned with PID:', phpProcess.pid);

      let started = false;
      const timeout = setTimeout(() => {
        if (!started) {
          started = true;
          console.log('[PHP] Startup timeout reached, assuming ready (PID: ' + phpProcess.pid + ')');
          resolve({ pid: phpProcess.pid });
        }
      }, 5000);

      const onData = (buf) => {
        const s = buf.toString();
        console.log('[PHP Output]', s.trim());
        if (!started && (s.toLowerCase().includes('started') || s.toLowerCase().includes('serving') || s.toLowerCase().includes('listening'))) {
          started = true;
          clearTimeout(timeout);
          console.log('[PHP] Server started successfully');
          resolve({ pid: phpProcess.pid });
        }
      };

      phpProcess.stdout.on('data', onData);
      phpProcess.stderr.on('data', (d) => {
        console.log('[PHP Stderr]', d.toString().trim());
        onData(d);
      });

      phpProcess.on('exit', (code) => {
        console.log('[PHP] Process exited with code:', code);
        phpProcess = null;
      });

      phpProcess.on('error', (err) => {
        console.error('[PHP] Spawn error:', err);
        phpProcess = null;
        reject(err);
      });
    } catch (err) {
      console.error('[PHP] Exception:', err);
      reject(err);
    }
  });
}

export function stopPhpServer() {
  return new Promise((resolve) => {
    if (!phpProcess) return resolve(true);
    try {
      phpProcess.kill();
    } catch (e) {
      // ignore
    }
    phpProcess = null;
    resolve(true);
  });
}
