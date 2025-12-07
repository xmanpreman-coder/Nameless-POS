<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/jquery.maskMoney.min.js') }}"></script>
@vite('resources/js/app.js')
<script defer src="{{ asset('js/pdfmake.min.js') }}"></script>
<script defer src="{{ asset('js/vfs_fonts.js') }}"></script>
<script defer src="{{ asset('js/datatables.min.js') }}"></script>
<script defer src="{{ asset('js/perfect-scrollbar.js') }}"></script>
<script defer src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>

@include('sweetalert::alert')

@yield('third_party_scripts')

@stack('page_scripts')

<script id="scanner-settings" type="application/json">{!! json_encode([
    'scanner_type' => settings()->scanner_type ?? 'camera',
    'enable_beep' => (bool) (settings()->enable_beep ?? true),
    'enable_vibration' => (bool) (settings()->enable_vibration ?? true),
]) !!}</script>

<script>
    // Read scanner settings from JSON script to avoid Blade tokens inside JS
    (function(){
        try {
            var el = document.getElementById('scanner-settings');
            window.scannerSettings = el ? JSON.parse(el.textContent) : { scanner_type: 'camera', enable_beep: true, enable_vibration: true };
        } catch (e) {
            window.scannerSettings = { scanner_type: 'camera', enable_beep: true, enable_vibration: true };
        }
    })();
</script>

<script>
    // Global tracking untuk print - prevent duplicates
    window.printInProgress = false;
    
    // Global function untuk print window (seperti print nota)
    // Uses iframe instead of popup for Tauri/WebView compatibility
    function openPrintWindow(type, id) {
        if (arguments.length === 1) {
            // Backward compatibility: jika hanya 1 parameter, anggap sebagai saleId
            id = type;
            type = 'sales';
        }
        
        // Prevent duplicate prints
        if (window.printInProgress) {
            console.log('Print already in progress');
            return;
        }
        
        const url = type === 'sales' 
            ? '/sales/pos/print/' + id
            : type === 'purchases'
            ? '/purchases/print/' + id
            : type === 'quotations'
            ? '/quotations/print/' + id
            : '';
        
        if (!url) return;
        
        window.printInProgress = true;
        
        // Remove existing print iframe if any
        let existingFrame = document.getElementById('print-frame');
        if (existingFrame) {
            existingFrame.remove();
        }
        
        // Create hidden iframe for printing (bypasses popup blocker in WebView/Tauri)
        const iframe = document.createElement('iframe');
        iframe.id = 'print-frame';
        iframe.name = 'print-frame';
        iframe.style.cssText = 'position:fixed;left:-9999px;top:0;width:500px;height:700px;border:none;';
        document.body.appendChild(iframe);
        
        // Load print URL in iframe
        iframe.src = url;
        
        // Wait for iframe to load then print
        iframe.onload = function() {
            setTimeout(function() {
                try {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                } catch(e) {
                    console.error('Print error:', e);
                    // Fallback: show in modal instead
                    showPrintModal(url);
                }
                
                // Cleanup after print dialog closes
                setTimeout(function() {
                    iframe.remove();
                    window.printInProgress = false;
                }, 2000);
            }, 500);
        };
        
        // Error fallback
        iframe.onerror = function() {
            console.error('Failed to load print iframe');
            window.printInProgress = false;
            iframe.remove();
            // Try modal fallback
            showPrintModal(url);
        };
        
        // Reset flag after timeout as backup
        setTimeout(function() {
            window.printInProgress = false;
        }, 15000);
    }
    
    // Fallback: Show print content in modal if iframe fails
    function showPrintModal(url) {
        // Create modal overlay
        const modalHtml = `
            <div id="print-modal-overlay" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:10000;display:flex;align-items:center;justify-content:center;">
                <div style="background:white;width:90%;max-width:450px;max-height:90vh;border-radius:8px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.3);">
                    <div style="padding:10px 15px;background:#007bff;color:white;display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-weight:bold;">Print Receipt</span>
                        <button onclick="document.getElementById('print-modal-overlay').remove()" style="background:none;border:none;color:white;font-size:20px;cursor:pointer;">&times;</button>
                    </div>
                    <iframe src="${url}" style="width:100%;height:70vh;border:none;"></iframe>
                    <div style="padding:10px;text-align:center;border-top:1px solid #ddd;">
                        <button onclick="document.getElementById('print-modal-overlay').querySelector('iframe').contentWindow.print()" style="padding:8px 20px;background:#28a745;color:white;border:none;border-radius:4px;cursor:pointer;margin-right:10px;">
                            Print
                        </button>
                        <button onclick="document.getElementById('print-modal-overlay').remove()" style="padding:8px 20px;background:#6c757d;color:white;border:none;border-radius:4px;cursor:pointer;">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
</script>

@livewireScripts


