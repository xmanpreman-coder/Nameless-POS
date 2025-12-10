Bundling Laravel inside Electron — Quick Instructions

This file explains the minimal steps to bundle the Laravel server into the Electron package.

1) Prepare the Laravel bundle
- From project root run:
  - `php artisan config:cache` (use production env file copied to `resources/bundle/.env.production`)
  - `npm run build` (build frontend assets)
  - Copy `public/` and any `storage/` assets the Electron app needs into `resources/bundle/public/` and `resources/bundle/storage/`

2) Prepare PHP runtime
- Use helper script: `packaging/scripts/fetch-windows-php.ps1` to download and extract a Windows PHP runtime into `resources/bundle/php/`.
- Verify `php.exe` exists at `resources/bundle/php/php.exe` and that required extensions (sqlite3 or pdo_mysql) are present.

3) Configure server entry
- Provide a lightweight `router.php` in `resources/bundle/public/router.php` that loads `../bootstrap/autoload.php` or `../bootstrap/app.php` and dispatches requests — or use the built-in server with `-t` pointing to `resources/bundle/public`.

4) Electron: starting server
- Use the helper `electron/spawn-php.js` (added to repo) from `electron/main.js`:

```js
const { startPhpServer, stopPhpServer } = require('./spawn-php');

app.whenReady().then(async () => {
  await startPhpServer({ bundlePath: path.join(__dirname, '..', 'resources', 'bundle'), port: 8000 });
  createMainWindow();
});

app.on('before-quit', async () => {
  await stopPhpServer();
});
```

5) Packaging
- Ensure `resources/bundle/**` is included in `extraResources` in `electron-builder` configuration so it will be copied to the final install location.

6) Auto-start & Service
- NSIS installer can install `nssm.exe` and use it to create a Windows Service pointing to the bundled PHP server start script. If NSSM is not provided the installer will create a Scheduled Task instead (existing flow).

7) Database
- For portability prefer SQLite. Put the database file under `%PROGRAMFILES%\\Nameless\\data` or AppData per user choice.

8) Testing
- Test on a clean Windows VM (no PHP installed). Run the EXE; it should launch Electron and spawn the bundled PHP server on `127.0.0.1:8000`.

Notes
- This approach increases installer size (PHP runtime ~30-50 MB). Use electron-builder excludes to keep size reasonable.
- Do NOT commit production `.env` or sensitive keys. Use templates and let the installer write a real `.env` during install.
