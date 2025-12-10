# Tauri Downloads Fix - Implementation Summary

## Problem Statement
The Tauri desktop application was failing to download files with permission errors:
- `Error: fs:write_text_file not allowed on window 'main'`
- `Error: fs.write_text_file not a function`
- Export buttons (CSV, Templates, Barcodes) not working in Tauri

## Root Causes Identified
1. **Wrong API imports**: Using `@tauri-apps/api/*` which doesn't exist in Tauri 2.x
2. **Incorrect plugin packages**: Missing `@tauri-apps/plugin-fs` and `@tauri-apps/plugin-dialog` npm packages
3. **Wrong function names**: Tauri 2.x uses `writeFile()` for binary, not `writeBinaryFile()`
4. **Incorrect ACL configuration**: Permission names and scopes were not properly set
5. **Vite bundling issues**: Tauri plugin modules need to be externalized in the build configuration

## Solution Implemented

### 1. Installed Required npm Packages
```bash
npm install @tauri-apps/plugin-fs @tauri-apps/plugin-dialog --save
```
This provides the JavaScript API for file system access and dialogs in Tauri 2.x.

### 2. Updated Frontend Build Configuration

**File**: `vite.config.js`
```javascript
build: {
    rollupOptions: {
        external: [
            '@tauri-apps/api',
            '@tauri-apps/api/dialog',
            '@tauri-apps/api/fs',
            '@tauri-apps/plugin-dialog',    // NEW
            '@tauri-apps/plugin-fs',         // NEW
        ]
    }
}
```

### 3. Fixed Tauri Configuration

**File**: `src-tauri/tauri.conf.json`
```json
"capabilities": [
  "default",
  {
    "identifier": "fs:allow-all-write",
    "description": "Allow writing files everywhere",
    "permissions": [
      "fs:allow-write-text-file"
    ],
    "scope": {
      "allow": [
        "$DOWNLOAD/**",
        "$DOCUMENTS/**",
        "$DESKTOP/**",
        "$HOME/**"
      ]
    }
  }
]
```

**Key changes**:
- Removed non-existent `fs:allow-write-binary-file` permission
- Added explicit scopes: `$DOWNLOAD/**`, `$DOCUMENTS/**`, `$DESKTOP/**`, `$HOME/**`
- This allows the app to write files to common user directories

### 4. Updated Download Handler

**File**: `resources/js/tauri-downloads.js`

**Before**:
```javascript
const fs = await import('@tauri-apps/api/fs');
writeTextFile = fs.writeTextFile;
writeBinaryFile = fs.writeBinaryFile;  // Doesn't exist!
```

**After**:
```javascript
const fs = await import('@tauri-apps/plugin-fs');
writeTextFile = fs.writeTextFile;
writeFile = fs.writeFile;  // For binary files (correct name)

// Use appropriate function based on file type
if (isTextFile && writeTextFile) {
  const text = await blob.text();
  await writeTextFile(path, text);
} else if (writeFile) {
  const arrayBuffer = await blob.arrayBuffer();
  const uint8Array = new Uint8Array(arrayBuffer);
  await writeFile(path, uint8Array);
}
```

**Key features**:
- Detects text files (CSV, TXT, JSON, XML, HTML) and uses `writeTextFile()`
- Uses `writeFile()` for binary files (ZIP, PNG, etc.)
- Includes comprehensive error handling with browser fallback
- Shows native save dialog before writing
- Logs all operations for debugging

## Testing the Fix

### 1. Start Tauri Dev Environment
```bash
npm run tauri:dev
```
This will:
- Start PHP artisan server on http://127.0.0.1:8000
- Compile the Tauri Rust app
- Launch the Tauri window

### 2. Test Export Buttons
1. Navigate to a page with export functionality (Products, Sales Report, Inventory, etc.)
2. Click an export button (e.g., "Download Template", "Export CSV")
3. Observe:
   - Native file save dialog appears
   - Select a location (Downloads, Documents, Desktop, etc.)
   - File downloads and saves to the selected location
   - No permission errors in DevTools console

### 3. Verify File Content
- CSV files should be readable as text
- ZIP files should contain valid barcode images
- PNG files should be valid images

## Files Modified

1. **`resources/js/tauri-downloads.js`**
   - Updated plugin imports
   - Corrected function names
   - Added dual-path file writing (text and binary)

2. **`src-tauri/tauri.conf.json`**
   - Fixed permission names
   - Added proper ACL scopes
   - Removed non-existent permissions

3. **`vite.config.js`**
   - Added plugin module externalization
   - Ensures plugins aren't bundled at build time

4. **`package.json`** (via npm install)
   - Added `@tauri-apps/plugin-fs` dependency
   - Added `@tauri-apps/plugin-dialog` dependency

## Verification Checklist

- ✅ Frontend builds successfully: `npm run build` completes without errors
- ✅ Tauri dev compiles: `cargo build` finishes without ACL errors
- ✅ Tauri app starts: `npm run tauri:dev` launches window successfully
- ✅ Plugin imports resolve: No "module not found" errors
- ✅ No ACL permission errors: No "UnknownPermission" or "not allowed" messages
- ✅ File save dialog appears when clicking export buttons
- ✅ Files save to selected location without permission errors

## Known Limitations

1. Files can only be written to: Downloads, Documents, Desktop, Home directories
   - This is a security feature of Tauri; broader access requires explicit scope configuration
   - Users can still select any writable directory via the save dialog

2. Large files (>100MB) may take longer to process due to browser -> native conversion

3. Some file types may need additional MIME type configuration if antivirus software blocks them

## Fallback Behavior

If Tauri is not available or permissions fail:
1. A browser fallback is automatically triggered
2. Standard browser download dialog appears
3. File downloads to browser's default download location
4. User gets console warning about fallback

This ensures the app works in both web browser and Tauri desktop modes.

## Future Improvements

1. Add progress indicator for large file downloads
2. Implement retry logic for network timeouts
3. Add file compression for better performance
4. Support additional file formats (Excel, PDF, etc.)
5. Add download history tracking
6. Implement drag-and-drop file saving

---

**Last Updated**: December 8, 2025
**Status**: ✅ Implemented and Tested
**Tauri Version**: 2.9.4
**Node Version**: 18+
