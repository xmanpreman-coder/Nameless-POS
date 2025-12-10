Release checklist — Build Electron EXE & Installer (Nameless POS)

This file describes the exact steps to produce a distributable Electron EXE and NSIS installer that installs the bundled Laravel server and creates shortcuts/service.

Prerequisites (Windows build machine)
- Node.js (LTS) + npm
- PHP (for composer) and Composer
- makensis (NSIS) in PATH to compile the NSIS installer (optional)
- Optional: `nssm.exe` if you want installer to install a Windows Service (place in `installer/tools/nssm/`)
- Optional: Code signing PFX file for signing installers (set `CSC_LINK` and `CSC_KEY_PASSWORD` environment variables)

1) Prepare repository

Run these commands in PowerShell from the repository root:

```powershell
git checkout refactor/product-sku
npm ci
composer install --no-dev --optimize-autoloader
npm run build       # build vite frontend
```

2) Prepare production bundle

Run:

```powershell
# copy production env template for bundling (edit values as needed)
copy .env.production resources\bundle\.env.production

# copy public assets into bundle
robocopy .\public .\resources\bundle\public /MIR
```

3) Fetch Windows PHP runtime into the bundle (if you bundle the server)

```powershell
.\packaging\scripts\fetch-windows-php.ps1 -phpVersion '8.1.30' -arch 'x64'
```

4) (Optional) Place `nssm.exe` into `installer\tools\nssm\` if you want the installer to create a Windows Service for the app.

5) Build Electron & package

```powershell
# helper script will run the electron build and optionally compile NSIS installer
.\packaging\scripts\build-release.ps1 -Clean
```

6) Artifacts
- Portable exe and installer will be in `dist/` and `releases/<timestamp>/` (if build helper collected artifacts).

7) Test the installer on a clean Windows VM (important)
- Run the installer: verify shortcuts, desktop/start menu icons, scheduled task or service created.
- Launch the app: verify the app starts, Laravel server spawns and UI responds.
- Verify database created and default admin seeded.

8) Code signing (recommended)
- To sign installers, set `CSC_LINK` and `CSC_KEY_PASSWORD` environment variables (see `packaging/SIGNING.md`) and run the same build command.

9) Release
- Upload `dist/` artifacts to GitHub Releases (if using `electron-updater`) or your update server.

Notes
- The packaged app runs a bundled PHP server bound to `127.0.0.1:8000`. Ensure your firewall rules allow localhost traffic.
- If you do not bundle PHP, ensure the target PC has PHP and DB per the app requirements or provide Docker packaging.
