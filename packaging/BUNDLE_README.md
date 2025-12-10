Bundle strategy for Nameless POS

Overview
--------
This document describes the recommended approach to bundle the PHP Laravel server and its assets into the Electron desktop application so the produced EXE installer can be distributed to customer PCs without requiring PHP or MySQL to be pre-installed.

High-level approach
-------------------
1. Include a PHP runtime for Windows (php.exe and required extensions) inside `resources/bundle/php/`.
2. Include a lightweight local database (SQLite by default) or bundle MariaDB portable for customers who need MySQL features. Default path: `%PROGRAMFILES%\\Nameless\\data\\database.sqlite`.
3. Build the Laravel app into a `public/` static folder for Electron, but keep a minimal PHP router script for API endpoints that require server-side processing.
4. Electron main process will spawn the bundled PHP built-in server or PHP-CGI (via `php -S localhost:PORT -t path/to/public router.php`) on app start and stop it when app exits. On Windows you may use `php-cgi.exe` with a lightweight supervisor if needed.
5. The installer will register an optional Windows Service using `nssm.exe` (if present) or create a Scheduled Task as fallback for auto-start.

Security & production notes
---------------------------
- Use environment-specific `.env` in the bundle: `resources/bundle/.env.production` — do NOT store secrets in the bundle repository.
- Provide instructions for generating and embedding a code-signing certificate for the final EXE/installer.
- Carefully audit any exec/spawn code and restrict exposed ports to `127.0.0.1`.

Files added by this repo change
-----------------------------
- `packaging/php-windows/` — placeholder for instructions and helpers to fetch PHP runtime.
- `packaging/electron-builder-placeholders/` — sample `electron-builder` config snippets.
- `packaging/README-BUILD.md` — build steps for bundling.

Next steps (I can implement):
- Add Electron helper `src/main/spawn-php.js` to start/stop the bundled PHP process.
- Add `packaging/scripts/fetch-windows-php.ps1` to download and prepare a PHP runtime for bundling.
- Update `electron-builder` config to include `resources/bundle/` in `extraResources`.

If you want, I can proceed and add the helper scripts and Electron main-process code now.
