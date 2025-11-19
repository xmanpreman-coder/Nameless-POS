<script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
@vite('resources/js/app.js')
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script defer src="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-1.13.5/b-2.4.1/b-html5-2.4.1/b-print-2.4.1/sl-1.7.0/datatables.min.js"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/jquery.perfect-scrollbar/1.4.0/perfect-scrollbar.js"></script>
<script defer src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>

@include('sweetalert::alert')

@yield('third_party_scripts')

@stack('page_scripts')

<script>
    // Global tracking untuk print windows - prevent duplicates
    window.printWindows = window.printWindows || {};
    
    // Global function untuk print window (seperti print nota)
    function openPrintWindow(type, id) {
        if (arguments.length === 1) {
            // Backward compatibility: jika hanya 1 parameter, anggap sebagai saleId
            id = type;
            type = 'sales';
        }
        
        const url = type === 'sales' 
            ? '/sales/pos/print/' + id
            : type === 'purchases'
            ? '/purchases/print/' + id
            : type === 'quotations'
            ? '/quotations/print/' + id
            : '';
        
        if (url) {
            // Prevent duplicate print windows for same document
            const windowKey = type + '_' + id;
            
            // Check if window already open for this document
            if (window.printWindows[windowKey] && !window.printWindows[windowKey].closed) {
                // Window already open, bring it to focus
                window.printWindows[windowKey].focus();
                return;
            }
            
            // Close any previous window for this document
            if (window.printWindows[windowKey]) {
                window.printWindows[windowKey].close();
            }
            
            // Open new print window
            const printWindow = window.open(url, '_blank', 'width=500,height=700');
            if (printWindow) {
                // Store reference
                window.printWindows[windowKey] = printWindow;
                
                // Auto-print when ready
                printWindow.onload = function() {
                    setTimeout(function() {
                        printWindow.print();
                    }, 500);
                };
                
                // Clean up reference when window closes
                printWindow.onbeforeunload = function() {
                    // Clear reference after a short delay to allow print dialog to complete
                    setTimeout(function() {
                        if (window.printWindows[windowKey] === printWindow) {
                            window.printWindows[windowKey] = null;
                        }
                    }, 100);
                };
            }
        }
    }
</script>

@livewireScripts

<script>
    // Ensure sidebar dropdown toggles work properly on all pages
    // This event handler uses capture phase to prevent event blocking
    document.addEventListener('click', function(e) {
        const dropdownToggle = e.target.closest('.c-sidebar-nav-dropdown-toggle');
        if (dropdownToggle) {
            e.preventDefault();
            e.stopPropagation();
            const parentDropdown = dropdownToggle.closest('.c-sidebar-nav-dropdown');
            if (parentDropdown) {
                parentDropdown.classList.toggle('c-show');
            }
            return false;
        }
    }, true); // Use capture phase to ensure it runs before other handlers
</script>
