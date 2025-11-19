<!-- Scanner Help Card - Collapsible -->
<div class="card mt-3 border-info" style="font-size: 13px;">
    <div class="card-header bg-info text-white" style="cursor: pointer; user-select: none; padding: 0.75rem 1rem;" onclick="toggleScannerHelp()" id="scannerHelpHeader">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="mb-0">
                <i class="bi bi-lightbulb"></i> Scanner Tips
                <span class="badge badge-light badge-pill ml-2">?</span>
            </h6>
            <i class="bi bi-chevron-down" id="scannerHelpToggle" style="transition: transform 0.3s; display: inline-block;"></i>
        </div>
    </div>
    <div class="card-body p-2" id="scannerHelpContent" style="display: none;">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary" style="font-size: 12px;">
                    <i class="bi bi-camera"></i> Camera Scanner
                </h6>
                <ul class="list-unstyled small" style="font-size: 11px; line-height: 1.4;">
                    <li><i class="bi bi-check text-success"></i> Click scanner icon next to search</li>
                    <li><i class="bi bi-check text-success"></i> Allow camera permission</li>
                    <li><i class="bi bi-check text-success"></i> Point camera at barcode</li>
                    <li><i class="bi bi-check text-success"></i> Hold device steady</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary" style="font-size: 12px;">
                    <i class="bi bi-keyboard"></i> USB/Bluetooth Scanner
                </h6>
                <ul class="list-unstyled small" style="font-size: 11px; line-height: 1.4;">
                    <li><i class="bi bi-check text-success"></i> Connect your scanner device</li>
                    <li><i class="bi bi-check text-success"></i> Click in search field</li>
                    <li><i class="bi bi-check text-success"></i> Scan barcode directly</li>
                    <li><i class="bi bi-check text-success"></i> Product will auto-add</li>
                </ul>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <div class="alert alert-light p-2 mb-0" style="font-size: 11px;">
                    <strong>⌨️ Shortcut:</strong> <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>S</kbd>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Simple toggle function for Scanner Tips
    function toggleScannerHelp() {
        const content = document.getElementById('scannerHelpContent');
        const toggle = document.getElementById('scannerHelpToggle');
        
        if (content.style.display === 'none' || !content.style.display) {
            // Show
            content.style.display = 'block';
            toggle.style.transform = 'rotate(180deg)';
        } else {
            // Hide
            content.style.display = 'none';
            toggle.style.transform = 'rotate(0deg)';
        }
    }
</script>