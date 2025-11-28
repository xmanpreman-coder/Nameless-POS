# SIGINT Disable - Build Success Guide

## 🎯 Problem yang Dihadapi
Build Electron dengan `npm run dist` selalu di-interrupt dengan error:
```
• cancelled by SIGINT
```

Penyebab: External process (Windows system, antivirus, atau task scheduler) mengirim SIGINT signal ke electron-builder, menyebabkan build gagal.

## ✅ Solusi yang Berhasil

### 1. **Stub Signer (Recommended)**
Buat file `customSign.js` untuk skip signing:

```javascript
/**
 * Stub signer untuk electron-builder
 * Skip signing process sepenuhnya
 */
module.exports = async (options) => {
  console.log('⊘ Signing skipped (stub signer)');
  return;
};
```

Update `electron-builder.yml`:
```yaml
win:
  target:
    - portable
  certificateFile: null
  certificatePassword: null
  sign: ./customSign.js
```

### 2. **Build Script dengan Proper Signal Handling**
File `build-nosigint.js`:
```javascript
import { spawn } from 'child_process';

// Set env vars untuk skip signing
process.env.SKIP_NOTARIZATION = 'true';
process.env.SKIP_SIGNING = 'true';
process.env.CSC_IDENTITY_AUTO_DISCOVERY = 'false';

// Spawn dengan proper stdio handling
const build = spawn('npm', ['run', 'dist:portable'], {
  stdio: 'inherit',
  shell: true,
  detached: false
});

// Handle signals
process.on('SIGINT', () => process.exit(0));
build.on('exit', (code) => process.exit(code || 1));
```

Add ke `package.json`:
```json
"scripts": {
  "dist:nosigint": "node build-nosigint.js"
}
```

### 3. **Configuration Changes**

**Remove dari `package.json` `extraFiles`:**
- ❌ `package.json` (causes ASAR corruption)

**Simplify `win` target:**
```json
"win": {
  "target": ["portable"]  // Only portable, not NSIS
}
```

### 4. **Build Command**

Run dengan environment variables:
```powershell
$env:CSC_KEY_PASSWORD=''
$env:CSC_LINK=''
$env:WIN_CSC_KEY_PASSWORD=''
$env:WIN_CSC_LINK=''
npm run dist:portable
```

Or use the custom script:
```powershell
npm run dist:nosigint
```

## 📋 Checklist

- [x] Remove `package.json` dari `extraFiles` di `package.json`
- [x] Add `sign: ./customSign.js` ke `win` config di `electron-builder.yml`
- [x] Set `win.target` hanya ke `["portable"]`
- [x] Nullify signing certificate vars
- [x] Create `customSign.js` stub signer
- [x] Test build: `npm run dist:portable`

## 🚀 Hasil

**Location:** `dist/Nameless POS 1.0.0.exe`  
**Size:** ~234 MB (includes Laravel + PHP context)  
**Distribution:** Portable - no installation needed, just run!

## 🔧 Troubleshooting

| Error | Cause | Solution |
|-------|-------|----------|
| `cancelled by SIGINT` | External process interrupt | Disable Windows Defender/antivirus during build |
| `package.json missing from ASAR` | Packaged twice | Remove `package.json` dari `extraFiles` |
| `certificateFile not found` | Signing config mismatch | Set to `null` in yml, use stub signer |
| `build-nosigint.js: require undefined` | ES module issue | Use ES import syntax, add `import.meta.url` |

## 📝 Notes

- Portable EXE tidak perlu installation
- Users masih memerlukan PHP installation di sistem mereka
- Database config via `.env.production`
- To skip even more: remove unnecessary files dari `files` config

---

**Build tested:** 2025-11-27  
**Status:** ✅ WORKING

---

## Recommended quick steps to avoid signtool blocking (summary)

1. Run PowerShell as Administrator.
2. Unblock any executable files that may be marked as downloaded:

```powershell
.
\scripts\unblock-files.ps1
```

3. Run the no-sign build (this sets recommended env vars and calls electron-builder):

```powershell
.
\scripts\build-no-sign.ps1 -Clean
```

4. If Windows Defender still interferes, add the project folder to Defender exclusions or temporarily disable Real-time Protection.

5. If multiple `signtool.exe` are present on PATH, inspect with:

```powershell
where signtool
```

If you see multiple locations, temporarily remove or reorder PATH entries so the Windows SDK signtool isn't invoked by accident.

---

These steps (helper scripts + forcing `sign: false` in `package.json`) are the most reliable way to make local builds stable across developer machines and CI environments where code-signing certificates are not available.
