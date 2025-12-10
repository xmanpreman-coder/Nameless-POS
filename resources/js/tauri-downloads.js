// Use dynamic imports for @tauri-apps/plugin-* so Vite doesn't attempt to pre-bundle
// when developing in a normal browser environment.
async function saveBlobWithTauri(blob, defaultFilename = 'download.bin') {
  try {
    const isTauri = typeof window.__TAURI__ !== 'undefined' || typeof window.__TAURI_IPC__ !== 'undefined';
    if (!isTauri) {
      console.warn('Not running inside Tauri; falling back to browser download');
      return { success: false, error: 'Not in Tauri', fallback: true };
    }

    let save, writeTextFile, writeFile;
    try {
      // Import from @tauri-apps/plugin-* instead of @tauri-apps/api
      const dialog = await import('@tauri-apps/plugin-dialog');
      const fs = await import('@tauri-apps/plugin-fs');
      save = dialog.save;
      writeTextFile = fs.writeTextFile;
      writeFile = fs.writeFile;  // For binary files
    } catch (importErr) {
      console.error('Failed to import Tauri plugin APIs:', importErr.message);
      return { success: false, error: `Tauri API import failed: ${importErr.message}`, fallback: true };
    }

    if (!save) {
      return { success: false, error: 'Tauri save dialog not available', fallback: true };
    }

    const path = await save({ defaultPath: defaultFilename });
    if (!path) return { success: false, cancelled: true };
    
    // Try to write file - use writeFile for binary (zip/png), writeTextFile for text (CSV/JSON/etc)
    console.log('Writing file via Tauri:', path);
    
    try {
      const isTextFile = /\.(csv|txt|json|xml|html)$/i.test(defaultFilename);
      
      if (isTextFile && writeTextFile) {
        // For text files, convert blob to text and write as text
        const text = await blob.text();
        await writeTextFile(path, text);
        console.log('File written as text:', path);
      } else if (writeFile) {
        // For binary files (zip, png, etc), use writeFile with Uint8Array
        const arrayBuffer = await blob.arrayBuffer();
        const uint8Array = new Uint8Array(arrayBuffer);
        await writeFile(path, uint8Array);
        console.log('File written as binary:', path);
      } else {
        throw new Error('No write function available (writeFile or writeTextFile)');
      }
      
      console.log('Tauri save successful:', path);
      return { success: true, path };
    } catch (writeErr) {
      console.error('Write function failed:', writeErr.message);
      throw writeErr;
    }
  } catch (err) {
    const errorMsg = err instanceof Error ? err.message : String(err);
    console.error('Tauri save failed:', errorMsg, err);
    return { success: false, error: errorMsg || 'Unknown error', fallback: true };
  }
}


function looksLikeDownloadHref(href) {
  return href && /\.(xlsx|xls|csv|pdf|zip)$/i.test(href);
}

export function attachTauriDownloadInterceptor() {
  // Only run when Tauri API is available
  const isTauri = typeof window.__TAURI__ !== 'undefined' || typeof window.__TAURI_IPC__ !== 'undefined';
  if (!isTauri) {
    console.debug('Tauri interceptor: not in Tauri environment');
    return;
  }

  document.addEventListener('click', async (ev) => {
    const el = ev.target.closest && ev.target.closest('a, button');
    if (!el) return;

    const href = el.getAttribute && (el.getAttribute('href') || el.dataset.href || el.dataset.url);
    const isExport = (el.className || '').includes('export-excel') || (el.dataset && el.dataset.export);
    const looksLike = looksLikeDownloadHref(href) || (href && href.includes('export'));

    if (!href && !isExport && !looksLike) return;

    // Intercept only if element likely triggers a download
    if (looksLike || isExport) {
      ev.preventDefault();
      try {
        el.disabled = true;
        const resp = await fetch(href, { credentials: 'include' });
        if (!resp.ok) throw new Error('HTTP ' + resp.status + ': ' + resp.statusText);
        const blob = await resp.blob();
        const defaultFilename = el.getAttribute('download') || el.dataset.filename || (href ? href.split('/').pop() : 'export.xlsx');
        const res = await saveBlobWithTauri(blob, defaultFilename);
        
        if (res.success) {
          console.log('Tauri save successful:', res.path);
        } else if (res.cancelled) {
          console.log('User cancelled save dialog');
        } else if (res.fallback) {
          console.warn('Tauri not available, falling back to browser download:', res.error);
          // Fallback: create a blob link and trigger download
          const link = document.createElement('a');
          link.href = URL.createObjectURL(blob);
          link.download = defaultFilename;
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          URL.revokeObjectURL(link.href);
        } else {
          throw new Error(res.error || 'Unknown save error');
        }
      } catch (err) {
        const msg = err instanceof Error ? err.message : String(err);
        console.error('Tauri download interceptor error:', msg, err);
        alert('Download gagal: ' + msg);
      } finally {
        el.disabled = false;
      }
    }
  }, { capture: true });

  // Intercept form submits that indicate they produce a file (use data-download="true")
  document.addEventListener('submit', async (ev) => {
    const form = ev.target;
    if (!form || !(form instanceof HTMLFormElement)) return;
    if (!form.dataset || !form.dataset.download) return; // only target forms marked with data-download

    ev.preventDefault();
    const url = form.action || window.location.href;
    const method = (form.method || 'GET').toUpperCase();
    const formData = new FormData(form);

    try {
      const options = { method, credentials: 'include' };
      if (method === 'POST') options.body = formData;
      const resp = await fetch(url, options);
      if (!resp.ok) throw new Error('HTTP ' + resp.status + ': ' + resp.statusText);
      const blob = await resp.blob();
      const defaultFilename = form.dataset.filename || 'export.xlsx';
      const res = await saveBlobWithTauri(blob, defaultFilename);
      
      if (!res.success && res.fallback) {
        // Fallback: browser download
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = defaultFilename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
      } else if (!res.success) {
        throw new Error(res.error || 'Unknown error');
      }
    } catch (err) {
      const msg = err instanceof Error ? err.message : String(err);
      console.error('Tauri form-download error:', msg, err);
      alert('Download gagal: ' + msg);
    }
  }, { capture: true });
}

// auto-attach if in Tauri and DOM ready
if (typeof window !== 'undefined') {
  window.addEventListener('DOMContentLoaded', () => {
    try { attachTauriDownloadInterceptor(); } catch (e) { console.warn('tauri-download attach failed', e); }
  });
}
