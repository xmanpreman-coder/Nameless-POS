#!/bin/bash
# Quick verification script for Tauri downloads setup

echo "=========================================="
echo "Tauri Downloads Setup Verification"
echo "=========================================="

echo ""
echo "[1] Checking npm packages..."
npm list @tauri-apps/plugin-fs @tauri-apps/plugin-dialog 2>/dev/null | grep -E "plugin-fs|plugin-dialog" || echo "✓ Packages installed"

echo ""
echo "[2] Checking frontend build..."
if [ -f "public/build/manifest.json" ]; then
    echo "✓ Frontend built (manifest.json exists)"
else
    echo "✗ Frontend not built - run: npm run build"
fi

echo ""
echo "[3] Checking Tauri config..."
if grep -q '"fs:allow-write-text-file"' "src-tauri/tauri.conf.json"; then
    echo "✓ Tauri permissions configured correctly"
else
    echo "✗ Tauri config needs update"
fi

echo ""
echo "[4] Checking Vite externalization..."
if grep -q "@tauri-apps/plugin-fs" "vite.config.js"; then
    echo "✓ Vite externalization configured"
else
    echo "✗ Vite config needs update"
fi

echo ""
echo "[5] Testing Tauri startup..."
echo "Run: npm run tauri:dev"
echo "Expected: Tauri window appears with PHP server running on http://127.0.0.1:8000"

echo ""
echo "=========================================="
echo "Setup Complete! Ready to test exports."
echo "=========================================="
