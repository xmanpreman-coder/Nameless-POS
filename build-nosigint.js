#!/usr/bin/env node

/**
 * Build script untuk disable SIGINT interrupt
 * Handles SIGINT properly sehingga electron-builder tidak di-terminate
 */

import { spawn } from 'child_process';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Set environment variables untuk skip signing
process.env.SKIP_NOTARIZATION = 'true';
process.env.SKIP_SIGNING = 'true';
process.env.CSC_IDENTITY_AUTO_DISCOVERY = 'false';
process.env.CSC_KEY_PASSWORD = '';
process.env.CSC_LINK = '';
process.env.WIN_CSC_KEY_PASSWORD = '';
process.env.WIN_CSC_LINK = '';

console.log('🔧 Building Nameless POS (SIGINT disabled)...\n');

// Spawn electron-builder dengan stdio inherit untuk streaming output
const build = spawn('npm', ['run', 'dist:portable'], {
  stdio: 'inherit',
  shell: true,
  detached: false, // Important: don't detach
  windowsHide: false
});

// Handle signals properly - jangan pass ke child process
process.on('SIGINT', () => {
  console.log('\n⚠️  Build interrupted by user');
  process.exit(0);
});

process.on('SIGTERM', () => {
  console.log('\n⚠️  Build terminated');
  process.exit(0);
});

// Wait for process to finish
build.on('exit', (code, signal) => {
  if (code === 0) {
    console.log('\n✅ Build completed successfully!');
    console.log('📦 EXE file available in: dist/');
  } else {
    console.log(`\n❌ Build failed with exit code: ${code}`);
    if (signal) console.log(`Signal: ${signal}`);
  }
  process.exit(code || 1);
});

build.on('error', (err) => {
  console.error('Error spawning build process:', err);
  process.exit(1);
});
