// File: public/js/tauri-download-helper.js
// Helper untuk menangani download di Tauri

/**
 * Download file menggunakan Tauri API
 * @param {string} url - URL file yang akan didownload
 * @param {string} defaultFilename - Nama file default
 */
async function downloadFileInTauri(url, defaultFilename = 'download.csv') {
    console.log('=== TAURI DOWNLOAD START ===');
    console.log('URL:', url);
    console.log('Filename:', defaultFilename);
    
    try {
        // Cek apakah berjalan di Tauri
        if (typeof window.__TAURI__ === 'undefined') {
            console.log('Not in Tauri, using browser fallback');
            window.open(url, '_blank');
            return;
        }

        console.log('In Tauri environment');
        
        // Import Tauri APIs
        const dialog = window.__TAURI__.dialog;
        const fs = window.__TAURI__.fs;
        
        if (!dialog || !fs) {
            console.error('Tauri plugins not available');
            alert('Tauri plugins (dialog/fs) tidak tersedia. Menggunakan browser download.');
            window.open(url, '_blank');
            return;
        }

        console.log('Showing save dialog...');
        
        // Tampilkan dialog save file
        const savePath = await dialog.save({
            defaultPath: defaultFilename,
            filters: [
                {
                    name: 'CSV Files',
                    extensions: ['csv']
                },
                {
                    name: 'Excel Files',
                    extensions: ['xlsx', 'xls']
                },
                {
                    name: 'All Files',
                    extensions: ['*']
                }
            ]
        });
        
        if (!savePath) {
            console.log('User cancelled download');
            return;
        }
        
        console.log('Save path:', savePath);
        console.log('Fetching from server...');
        
        // Fetch file dari server dengan error handling lebih baik
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'include', // Include cookies for Laravel session
            headers: {
                'Accept': 'text/csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, */*'
            }
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', [...response.headers.entries()]);
        
        if (!response.ok) {
            throw new Error(`Server error: ${response.status} ${response.statusText}`);
        }
        
        // Cek content type
        const contentType = response.headers.get('content-type');
        console.log('Content-Type:', contentType);
        
        // Baca response
        let content;
        if (contentType && contentType.includes('application/vnd.openxmlformats')) {
            // Excel file - read as array buffer then convert to base64
            console.log('Reading as Excel (binary)...');
            const arrayBuffer = await response.arrayBuffer();
            const uint8Array = new Uint8Array(arrayBuffer);
            content = uint8Array;
            console.log('Content size:', content.length, 'bytes');
            
            // Write binary file
            await fs.writeBinaryFile(savePath, content);
        } else {
            // CSV or text file
            console.log('Reading as text...');
            content = await response.text();
            console.log('Content size:', content.length, 'characters');
            
            if (!content || content.length === 0) {
                throw new Error('Response body is empty');
            }
            
            // Write text file
            await fs.writeTextFile(savePath, content);
        }
        
        console.log('File saved successfully!');
        alert('✅ File berhasil didownload!\n\nLokasi: ' + savePath);
        
    } catch (error) {
        console.error('=== DOWNLOAD ERROR ===');
        console.error('Error:', error);
        console.error('Error message:', error?.message || 'Unknown error');
        console.error('Error stack:', error?.stack);
        
        // Show detailed error to user
        const errorMsg = error?.message || error?.toString() || 'Unknown error';
        alert('❌ Gagal download file!\n\nError: ' + errorMsg + '\n\nMencoba browser download...');
        
        // Fallback ke browser download
        console.log('Falling back to browser download');
        window.open(url, '_blank');
    }
}

// Export untuk digunakan di global scope
window.downloadFileInTauri = downloadFileInTauri;

// Log when loaded
console.log('✅ Tauri download helper loaded');
if (typeof window.__TAURI__ !== 'undefined') {
    console.log('✅ Tauri API available');
    console.log('- dialog:', typeof window.__TAURI__.dialog);
    console.log('- fs:', typeof window.__TAURI__.fs);
} else {
    console.log('⚠️  Tauri API not available (running in browser)');
}
