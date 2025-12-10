Code signing for Windows executables

To sign the EXE and installer you will need a code signing certificate (PFX) from a CA.

Steps:
1. Obtain a code signing certificate (PFX) and keep it secure.
2. Use `electron-builder` `win.signingHashAlgorithms` and `win.certificateFile` config or set environment variables:
   - `CSC_LINK` - path to PFX (or URL)
   - `CSC_KEY_PASSWORD` - PFX password

Example usage (PowerShell):

```powershell
$env:CSC_LINK = 'C:\keys\codesign.pfx'
$env:CSC_KEY_PASSWORD = 'password'
npm run dist:installer
```

If you do not have a certificate, builds can be created unsigned but Windows will show warnings during install.
