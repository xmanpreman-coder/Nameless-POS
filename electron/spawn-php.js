import { spawn } from 'child_process';
import path from 'path';

let phpProcess = null;

export function startPhpServer(options = {}) {
  const __dirname = path.dirname(new URL(import.meta.url).pathname);
  const bundlePath = options.bundlePath || path.join(__dirname, '..', 'resources', 'bundle');
  const phpPath = options.phpPath || path.join(bundlePath, 'php', 'php.exe');
  const publicPath = options.publicPath || path.join(bundlePath, 'public');
  const port = options.port || 8000;
  const host = options.host || '127.0.0.1';

  if (phpProcess) {
    return Promise.resolve({ pid: phpProcess.pid });
  }

  return new Promise((resolve, reject) => {
    const args = ['-S', `${host}:${port}`, '-t', publicPath];

    phpProcess = spawn(phpPath, args, {
      stdio: ['ignore', 'pipe', 'pipe'],
      windowsHide: true,
      cwd: bundlePath,
    });

    let started = false;

    const onData = (buf) => {
      const s = buf.toString();
      if (!started) {
        started = true;
        resolve({ pid: phpProcess.pid });
      }
    };

    phpProcess.stdout.on('data', onData);
    phpProcess.stderr.on('data', (d) => {
      // ignore for now
    });

    phpProcess.on('exit', () => {
      phpProcess = null;
    });

    phpProcess.on('error', (err) => {
      phpProcess = null;
      reject(err);
    });

    // fallback timeout
    setTimeout(() => {
      if (!started && phpProcess) {
        started = true;
        resolve({ pid: phpProcess.pid });
      }
    }, 1500);
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
