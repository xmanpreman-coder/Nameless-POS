const { spawn, exec, execFile } = require('child_process');
const path = require('path');
const os = require('os');
const fs = require('fs');
const { app } = require('electron');

class LaravelServer {
  constructor() {
    this.serverProcess = null;
    this.phpPath = null;
    this.port = null;  // Store the port that server is running on
  }

  // Read .env file (simple parser) and return key/value map
  loadEnvFile(appRoot) {
    try {
      // Try a sequence of env filenames so packaged apps that ship
      // a production env file are handled.
      const candidates = [
        path.join(appRoot, '.env'),
        path.join(appRoot, '.env.production'),
        path.join(appRoot, '.env.local')
      ];

      let envPath = null;
      for (const c of candidates) {
        if (fs.existsSync(c)) { envPath = c; break; }
      }
      if (!envPath) return {};
      const contents = fs.readFileSync(envPath, 'utf8');
      const lines = contents.split(/\r?\n/);
      const result = {};
      for (const line of lines) {
        const trimmed = line.trim();
        if (!trimmed || trimmed.startsWith('#')) continue;
        const idx = trimmed.indexOf('=');
        if (idx === -1) continue;
        const key = trimmed.substring(0, idx).trim();
        let val = trimmed.substring(idx + 1).trim();
        // remove surrounding quotes
        if ((val.startsWith("\"") && val.endsWith("\"")) || (val.startsWith("'") && val.endsWith("'"))) {
          val = val.substring(1, val.length - 1);
        }
        result[key] = val;
      }
      return result;
    } catch (e) {
      console.log('[LaravelServer] loadEnvFile error:', e && e.message);
      return {};
    }
  }

  // Append a diagnostic entry to a local diagnostics file under userData
  writeDiagnosticLog(msg) {
    try {
      const logDir = app.getPath && app.getPath('userData') ? app.getPath('userData') : os.tmpdir();
      const logFile = path.join(logDir, 'laravel-server-diagnostics.log');
      const ts = (new Date()).toISOString();
      fs.appendFileSync(logFile, `[${ts}] ${msg}\n`);
    } catch (e) {
      console.log('[LaravelServer] Failed to write diagnostic log:', e && e.message);
    }
  }

  /**
   * Get the port that server is running on
   */
  getPort() {
    return this.port || 8000; // Default to 8000 if not set
  }

  /**
   * Get the app root directory
   * When running as EXE: app is in resources/app/ inside the executable
   * When running in dev: project root
   */
  getAppRoot() {
    console.log('[LaravelServer] Detecting app root...');
    console.log('[LaravelServer] __dirname:', __dirname);
    console.log('[LaravelServer] process.cwd():', process.cwd());
    console.log('[LaravelServer] app.getAppPath():', app.getAppPath());
    
    // List of places to look for artisan file
    const possibleRoots = [
      // Packaged app scenarios
      path.join(app.getAppPath(), '..', '..'),           // resources/app -> resources
      path.join(app.getAppPath(), '..'),                 // app -> resources
      app.getAppPath(),                                  // Direct app path
      
      // Development scenarios
      process.cwd(),                                     // Current working directory
      path.resolve(__dirname, '..'),                     // Electron folder parent
      
      // Fallback: assume Laravel is in same folder as electron
      path.resolve(__dirname, '..', '..'),              // Two levels up from electron
    ];

    console.log('[LaravelServer] Searching for artisan in:');
    for (const root of possibleRoots) {
      const artisanPath = path.join(root, 'artisan');
      console.log(`  - ${root}`);
      if (fs.existsSync(artisanPath)) {
        console.log(`[LaravelServer] ✓ Found artisan at: ${artisanPath}`);
        return root;
      }
    }

    // If not found, try one more thing - look upward from electron folder
    console.log('[LaravelServer] Artisan not found in standard locations. Searching upward...');
    let searchPath = path.resolve(__dirname);
    for (let i = 0; i < 5; i++) {
      if (fs.existsSync(path.join(searchPath, 'artisan'))) {
        console.log(`[LaravelServer] ✓ Found artisan at: ${searchPath}`);
        return searchPath;
      }
      searchPath = path.resolve(searchPath, '..');
      if (searchPath === path.resolve(searchPath, '..')) break; // Stop at root
    }

    // Last resort - return most likely location
    console.log('[LaravelServer] WARNING: Could not find artisan. Using fallback path.');
    return path.resolve(__dirname, '..');
  }

  /**
   * Find PHP executable
   * Tries bundled PHP first (inside resources/php), then system PHP
   */
  findPhpPath() {
    if (this.phpPath) {
      return this.phpPath;
    }

    // Try bundled PHP first (packed inside EXE)
    const bundledPhpPaths = [
      path.join(app.getAppPath(), 'php', 'php.exe'),           // resources/app/php/php.exe
      path.join(app.getAppPath(), '..', 'php', 'php.exe'),     // resources/php/php.exe
      path.join(process.resourcesPath, 'php', 'php.exe'),      // resources/php/php.exe
    ];

    for (const phpPath of bundledPhpPaths) {
      if (fs.existsSync(phpPath)) {
        console.log(`[LaravelServer] ✓ Found bundled PHP at: ${phpPath}`);
        this.phpPath = phpPath;
        return phpPath;
      }
    }

    // Fallback to system PHP from PATH
    console.log('[LaravelServer] Bundled PHP not found. Using system PHP from PATH');
    this.phpPath = 'php';
    return 'php';
  }

  start() {
    return new Promise(async (resolve, reject) => {
      try {
        // Early diagnostic so we can confirm the packaged launcher ran.
        this.writeDiagnosticLog('[LaravelServer] startup reached');
        this.writeDiagnosticLog(`[LaravelServer] env app.getAppPath=${String(app.getAppPath())}`);
        this.writeDiagnosticLog(`[LaravelServer] env process.cwd=${String(process.cwd())}`);
        this.writeDiagnosticLog(`[LaravelServer] env __dirname=${String(__dirname)}`);

        const appRoot = this.getAppRoot();
        const phpPath = this.findPhpPath();

        console.log('[LaravelServer] App root:', appRoot);
        console.log('[LaravelServer] PHP path:', phpPath);
        console.log('[LaravelServer] Starting Laravel server...');

        // Emit a small directory listing and .env preview to diagnostics so
        // we can verify which env file is present in the packaged runtime.
        try {
          const files = fs.readdirSync(appRoot || '.');
          const envFiles = files.filter(f => f && f.toLowerCase().startsWith('.env'));
          this.writeDiagnosticLog(`[LaravelServer] appRoot listing: ${files.join(', ')}`);
          this.writeDiagnosticLog(`[LaravelServer] env files found: ${envFiles.join(', ')}`);
          for (const ef of envFiles) {
            try {
              const p = path.join(appRoot, ef);
              const contents = fs.readFileSync(p, 'utf8');
              const match = contents.match(/APP_KEY\s*=\s*(.*)/);
              const preview = match && match[1] ? (match[1].trim().substring(0, 30) + '...(masked)') : '(no APP_KEY)';
              this.writeDiagnosticLog(`[LaravelServer] ${ef} APP_KEY preview: ${preview}`);
            } catch (e) {
              this.writeDiagnosticLog(`[LaravelServer] Failed reading ${ef}: ${e && e.message}`);
            }
          }
        } catch (e) {
          this.writeDiagnosticLog(`[LaravelServer] Failed to list appRoot: ${e && e.message}`);
        }

        // If running as a packaged app, clear config/views/routes caches so
        // Laravel reads the runtime `APP_ENV=production` and other env vars.
        // This prevents accidentally shipping a dev `config` cache that
        // would keep Livewire or Debugbar enabled.
        // Treat packaged EXE and unpacked release folders (win-unpacked) as production.
        const appPath = String(app.getAppPath() || '').toLowerCase();
        const isUnpackedRelease = appPath.includes('win-unpacked') || appPath.includes('resources\\app');
        if (app.isPackaged || isUnpackedRelease) {
          console.log('[LaravelServer] Packaged/unpacked-release detected — clearing Laravel caches to enforce production config');
          try {
            // remove any pre-generated config caches first (may be bundled)
            this.removeBootstrapCacheFiles(appRoot);
            // Ensure storage/cache/view directories exist so Laravel can compile views
            this.ensureStorageDirs(appRoot);
            // run cache clears (non-blocking but awaited)
            await this.clearCaches(appRoot, phpPath);
          } catch (e) {
            console.log('[LaravelServer] Warning: cache clear failed', e && e.message);
          }
        }

        // Try multiple ports to avoid conflicts (8000-8100)
        // Expand the probe range so packaged runs have a higher chance
        // of finding a free port on busy systems.
        const ports = Array.from({ length: 101 }, (_, i) => 8000 + i);

        const tryStartOnPort = (port) => {
          return new Promise((res, rej) => {
            const spawnCmd = phpPath;
            const spawnArgs = [
              'artisan',
              'serve',
              '--host=127.0.0.1',
              `--port=${port}`,
              '--env=production'
            ];
            // Load .env values (if present) so we can inject a real APP_KEY
            const envFromFile = this.loadEnvFile(appRoot) || {};
            const effectiveAppKey = envFromFile.APP_KEY || process.env.APP_KEY || '';

            // Validate APP_KEY: must be base64:... and decode to 32 bytes (AES-256 key)
            let appKeyValid = false;
            try {
              if (effectiveAppKey && effectiveAppKey.startsWith && effectiveAppKey.startsWith('base64:')) {
                const raw = effectiveAppKey.substring(7);
                const buf = Buffer.from(raw, 'base64');
                appKeyValid = buf.length === 32;
              }
            } catch (e) {
              appKeyValid = false;
            }

            // Diagnostic log and console output for invalid/missing APP_KEY
            if (!appKeyValid) {
              const msg = `[LaravelServer] Invalid or missing APP_KEY detected for appRoot=${appRoot}. ` +
                `Found in file: ${envFromFile && Object.keys(envFromFile).length ? 'yes' : 'no'}. ` +
                `process.env.APP_KEY present: ${!!process.env.APP_KEY}. ` +
                `effectiveAppKeyPreview: ${effectiveAppKey ? (effectiveAppKey.substring(0, 20) + '...(masked)') : '(none)'}.`;
              console.error(msg);
              this.writeDiagnosticLog(msg);
              rej(new Error('Invalid or missing APP_KEY. Packaged application requires a valid base64 APP_KEY in .env or process.env.APP_KEY'));
              return;
            }

            const spawnOpts = {
              cwd: appRoot,
              stdio: ['ignore', 'pipe', 'pipe'],
              shell: false,
              env: {
                ...process.env,
                // Force production env for the spawned artisan process
                APP_ENV: 'production',
                // Ensure Debugbar is disabled and APP_DEBUG is false for packaged/unpacked runs
                DEBUGBAR_ENABLED: 'false',
                APP_DEBUG: 'false',
                // Inject APP_KEY from the bundled .env when present so encryption/decryption works
                APP_KEY: effectiveAppKey
              }
            };

            const proc = spawn(spawnCmd, spawnArgs, spawnOpts);
            let output = '';
            let errored = false;

            // Stream stdout/stderr to diagnostics so we capture artisan messages
            const writeStd = (tag, data) => {
              const msg = data.toString();
              try { this.writeDiagnosticLog(`[LaravelServer:${port}] ${tag}: ${msg.replace(/\r?\n/g, ' | ')}`); } catch (e) {}
            };

            const cleanup = () => {
              if (proc && !proc.killed) {
                try { proc.kill(); } catch (e) {}
              }
            };

            proc.stdout.on('data', (data) => {
              const msg = data.toString();
              output += msg;
              console.log(`[LaravelServer:${port}]`, msg.trim());
              writeStd('STDOUT', data);
              if (/started|Development Server|Server running|listening/i.test(output)) {
                res({ proc, port });
              }
            });

            proc.stderr.on('data', (data) => {
              const msg = data.toString();
              console.error(`[LaravelServer:${port} ERROR]`, msg.trim());
              writeStd('STDERR', data);
              if (/Failed to listen|Address already in use|getaddrinfo/i.test(msg)) {
                errored = true;
                cleanup();
                rej(new Error(msg));
              }
            });

            proc.on('error', (err) => {
              errored = true;
              this.writeDiagnosticLog(`[LaravelServer:${port}] proc error: ${err && err.message}`);
              cleanup();
              rej(err);
            });

            proc.on('exit', (code) => {
              this.writeDiagnosticLog(`[LaravelServer:${port}] proc exit code: ${code}`);
              if (!errored && code !== 0) {
                rej(new Error(`exit code ${code}`));
              }
            });

            // Timeout for this port: allow more time for artisan to boot
            // on slower machines or when starting bundled PHP.
            setTimeout(() => {
              if (!errored && output.length > 0) {
                res({ proc, port });
              } else {
                cleanup();
                rej(new Error('timeout'));
              }
            }, 5000);
          });
        };

        // sequentially try ports
        let started = false;
        for (const p of ports) {
          try {
            const { proc, port } = await tryStartOnPort(p);
            this.serverProcess = proc;
            this.port = port;  // Store the port
            console.log('[LaravelServer] Started on port', port);
            started = true;
            resolve(true);
            break;
          } catch (e) {
            console.log('[LaravelServer] Port', p, 'failed:', e.message.replace(/\r?\n/g, ' '));
            // try next port
            continue;
          }
        }

        if (!started) {
          throw new Error('No available ports to start Laravel server');
        }

      } catch (err) {
        console.error('[LaravelServer] Exception:', err);
        reject(err);
      }
    });
  }

  clearCaches(appRoot, phpPath) {
    return new Promise((resolve) => {
      try {
        // Run cache clear commands using execFile to avoid shell quoting issues.
        // We include `config:clear` to ensure cached config from development
        // isn't accidentally used inside the packaged app.
        const commands = [
          ['artisan', ['config:clear']],
          ['artisan', ['cache:clear']],
          ['artisan', ['route:clear']],
          ['artisan', ['view:clear']]
        ];

        let completed = 0;

        commands.forEach(([file, args]) => {
          // execFile with php executable: execFile(phpPath, ['artisan', ...args])
          execFile(phpPath, [file, ...args], { cwd: appRoot, timeout: 15000 }, (err) => {
            if (err) {
              console.log('[LaravelServer] Cache command result:', err.message);
            }
            completed++;
            if (completed === commands.length) {
              resolve();
            }
          });
        });

        // Fallback timeout
        setTimeout(() => resolve(), 12000);

      } catch (err) {
        console.log('[LaravelServer] Cache clear exception:', err.message);
        resolve();
      }
    });
  }

  removeBootstrapCacheFiles(appRoot) {
    try {
      const cacheDir = path.join(appRoot, 'bootstrap', 'cache');
      if (fs.existsSync(cacheDir)) {
        const files = fs.readdirSync(cacheDir);
        const targets = ['config.php', 'routes.php', 'packages.php', 'services.php'];
        for (const f of files) {
          if (targets.includes(f)) {
            try {
              const fp = path.join(cacheDir, f);
              fs.unlinkSync(fp);
              console.log(`[LaravelServer] Removed cached file: ${fp}`);
            } catch (e) {
              console.log(`[LaravelServer] Failed to remove ${f}:`, e && e.message);
            }
          }
        }
      }
    } catch (e) {
      console.log('[LaravelServer] removeBootstrapCacheFiles error:', e && e.message);
    }
  }

  // Ensure Laravel expected storage and cache directories exist and are writable
  ensureStorageDirs(appRoot) {
    try {
      const dirs = [
        path.join(appRoot, 'storage'),
        path.join(appRoot, 'storage', 'framework'),
        path.join(appRoot, 'storage', 'framework', 'views'),
        path.join(appRoot, 'storage', 'framework', 'cache'),
        path.join(appRoot, 'storage', 'logs'),
        path.join(appRoot, 'bootstrap', 'cache')
      ];
      for (const d of dirs) {
        try {
          if (!fs.existsSync(d)) {
            fs.mkdirSync(d, { recursive: true });
            console.log(`[LaravelServer] Created dir: ${d}`);
            this.writeDiagnosticLog(`[LaravelServer] Created dir: ${d}`);
          } else {
            this.writeDiagnosticLog(`[LaravelServer] Dir exists: ${d}`);
          }
        } catch (e) {
          console.log('[LaravelServer] Failed to ensure dir', d, e && e.message);
          this.writeDiagnosticLog(`[LaravelServer] Failed to ensure dir ${d}: ${e && e.message}`);
        }
      }
    } catch (e) {
      console.log('[LaravelServer] ensureStorageDirs error:', e && e.message);
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
