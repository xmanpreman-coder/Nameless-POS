// Electron-specific download handler using native save dialogs
async function saveFileWithElectron(blob, defaultFilename = 'download.bin') {
  try {
    const isElectron = typeof window.electronAPI !== 'undefined';
    if (!isElectron) {
      console.warn('Not running in Electron; falling back to browser download');
      return { success: false, error: 'Not in Electron', fallback: true };
    }

    // Convert blob to base64 for transfer
    console.log('Saving file via Electron:', defaultFilename);
    const arrayBuffer = await blob.arrayBuffer();
    const uint8Array = new Uint8Array(arrayBuffer);
    const binaryString = String.fromCharCode.apply(null, uint8Array);
    const base64Content = btoa(binaryString);

    // Use Electron's file save dialog and write via IPC
    const result = await window.electronAPI.saveFile(defaultFilename, base64Content, true);

    if (result.success) {
      console.log('✅ File saved successfully:', result.path);
      return { success: true, path: result.path };
    } else if (result.cancelled) {
      console.log('User cancelled save dialog');
      return { success: false, cancelled: true };
    } else {
      console.error('Save failed:', result.error);
      return { success: false, error: result.error, fallback: true };
    }
  } catch (err) {
    const errorMsg = err instanceof Error ? err.message : String(err);
    console.error('Electron save failed:', errorMsg, err);
    return { success: false, error: errorMsg || 'Unknown error', fallback: true };
  }
}

function looksLikeDownloadHref(href) {
  return href && /\.(xlsx|xls|csv|pdf|zip)$/i.test(href);
}

export function attachElectronDownloadInterceptor() {
  // Only run when Electron API is available
  const isElectron = typeof window.electronAPI !== 'undefined';
  if (!isElectron) {
    console.debug('Electron interceptor: not in Electron environment');
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
        const res = await saveFileWithElectron(blob, defaultFilename);
        
        if (res.success) {
          console.log('Electron save successful:', res.path);
        } else if (res.cancelled) {
          console.log('User cancelled save dialog');
        } else if (res.fallback) {
          console.warn('Electron save failed, falling back to browser download:', res.error);
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
        console.error('Electron download interceptor error:', msg, err);
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
      const res = await saveFileWithElectron(blob, defaultFilename);
      
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
      console.error('Electron form-download error:', msg, err);
      alert('Download gagal: ' + msg);
    }
  }, { capture: true });
}

// auto-attach if in Electron and DOM ready
if (typeof window !== 'undefined') {
  window.addEventListener('DOMContentLoaded', () => {
    try { attachElectronDownloadInterceptor(); } catch (e) { console.warn('electron-download attach failed', e); }
  });
}
