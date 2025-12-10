// Universal download handler untuk Electron dan Tauri
// Dipanggil dari handleTauriDownload() di views

async function handleElectronDownload(url, filename) {
    try {
        console.log('🔽 Download requested:', { url, filename });
        
        // Check if Electron API available
        const hasElectron = typeof window.electronAPI !== 'undefined';
        const hasTauri = typeof window.__TAURI__ !== 'undefined';
        
        if (!hasElectron && !hasTauri) {
            // Fallback to browser download
            console.log('No desktop environment detected, using browser download');
            window.location.href = url;
            return;
        }
        
        // Fetch file content
        const response = await fetch(url, { credentials: 'include' });
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const blob = await response.blob();
        console.log('✓ Blob received, size:', blob.size, 'bytes', 'type:', blob.type);
        
        // Try Electron first
        if (hasElectron) {
            console.log('📱 Using Electron for download');
            
            // Determine if file is text or binary based on extension and MIME type
            const isTextFile = /\.(csv|txt|json|xml|html|log)$/i.test(filename) || 
                              blob.type.startsWith('text/');
            
            let content;
            
            if (isTextFile) {
                // For text files, get text directly and don't base64 encode
                content = await blob.text();
                console.log('📄 Text file detected, size:', content.length, 'characters');
                
                try {
                    const result = await window.electronAPI.saveFile(filename, content, false);
                    
                    if (result.success) {
                        console.log('✅ File saved via Electron:', result.path);
                        return;
                    } else if (result.cancelled) {
                        console.log('⚠️ User cancelled save dialog');
                        return;
                    } else {
                        throw new Error(result.error || 'Unknown error');
                    }
                } catch (electronError) {
                    console.error('❌ Electron text save failed:', electronError.message);
                }
            } else {
                // For binary files, convert to base64
                const arrayBuffer = await blob.arrayBuffer();
                const uint8Array = new Uint8Array(arrayBuffer);
                const binaryString = String.fromCharCode.apply(null, uint8Array);
                content = btoa(binaryString);
                console.log('📦 Binary file detected, base64 size:', content.length, 'characters');
                
                try {
                    const result = await window.electronAPI.saveFile(filename, content, true);
                    
                    if (result.success) {
                        console.log('✅ File saved via Electron:', result.path);
                        return;
                    } else if (result.cancelled) {
                        console.log('⚠️ User cancelled save dialog');
                        return;
                    } else {
                        throw new Error(result.error || 'Unknown error');
                    }
                } catch (electronError) {
                    console.error('❌ Electron binary save failed:', electronError.message);
                }
            }
        }
        
        // Fallback to browser download
        console.log('💾 Falling back to browser download');
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
        console.log('✓ Browser download initiated');
        
    } catch (error) {
        console.error('❌ Download error:', error);
        alert('Download gagal: ' + error.message);
    }
}

// Make it globally available
window.downloadFileInElectron = handleElectronDownload;
window.downloadFileInTauri = handleElectronDownload;  // Alias for backward compatibility
