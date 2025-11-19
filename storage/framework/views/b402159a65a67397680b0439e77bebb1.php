<!-- Scanner Modal -->
<div class="modal fade" id="scannerModal" tabindex="-1" role="dialog" aria-labelledby="scannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="scannerModalLabel">
                    <i class="bi bi-upc-scan"></i> Barcode Scanner
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Scanner Type Tabs -->
                <ul class="nav nav-tabs mb-3" id="scannerTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="camera-tab" data-toggle="tab" href="#camera-scan" role="tab">
                            <i class="bi bi-camera"></i> Camera Scanner
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="manual-tab" data-toggle="tab" href="#manual-scan" role="tab">
                            <i class="bi bi-keyboard"></i> Manual Input
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="scannerTabContent">
                    <!-- Camera Scanner Tab -->
                    <div class="tab-pane fade show active" id="camera-scan" role="tabpanel">
                        <div class="text-center mb-3">
                            <div id="modal-camera-container" style="position: relative; max-width: 500px; margin: 0 auto;">
                                <video id="modal-scanner-video" style="width: 100%; height: 300px; background: #000; border-radius: 8px;" autoplay playsinline></video>
                                <div id="modal-scanner-overlay" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); border: 2px solid #ff0000; width: 200px; height: 100px; border-radius: 8px; pointer-events: none;"></div>
                            </div>
                            <canvas id="modal-scanner-canvas" style="display: none;"></canvas>
                        </div>
                        
                        <div class="text-center mb-3">
                            <div id="modal-scanner-status" class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Click "Start Camera" to begin scanning
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-2">
                            <button id="modal-start-camera" class="btn btn-success">
                                <i class="bi bi-camera"></i> Start Camera
                            </button>
                            <button id="modal-stop-camera" class="btn btn-danger" style="display: none;">
                                <i class="bi bi-camera-video-off"></i> Stop Camera
                            </button>
                            <button id="modal-switch-camera" class="btn btn-info" style="display: none;">
                                <i class="bi bi-arrow-repeat"></i> Switch Camera
                            </button>
                        </div>
                    </div>

                    <!-- Manual Input Tab -->
                    <div class="tab-pane fade" id="manual-scan" role="tabpanel">
                        <div class="form-group">
                            <label for="modal-manual-barcode">Enter Barcode</label>
                            <div class="input-group">
                                <input type="text" id="modal-manual-barcode" class="form-control form-control-lg" placeholder="Scan or type barcode here..." autofocus>
                                <div class="input-group-append">
                                    <button id="modal-search-manual" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                You can use a USB or Bluetooth scanner, or type the barcode manually
                            </small>
                        </div>

                        <!-- Recent Scans -->
                        <div class="mt-4">
                            <h6><i class="bi bi-clock-history"></i> Recent Scans</h6>
                            <div id="modal-recent-scans" class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                                <small class="text-muted">No recent scans</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scanner Result -->
                <div id="modal-scanner-result" class="mt-4" style="display: none;">
                    <div class="alert alert-success">
                        <h6><i class="bi bi-check-circle"></i> Product Found!</h6>
                        <div id="modal-result-content"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
                <a href="<?php echo e(route('scanner.settings')); ?>" class="btn btn-info" target="_blank">
                    <i class="bi bi-gear"></i> Scanner Settings
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.nav-tabs .nav-link {
    color: #6c757d;
}
.nav-tabs .nav-link.active {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}
.gap-2 > * + * {
    margin-left: 0.5rem;
}
</style><?php /**PATH D:\project warnet\Nameless\resources\views/includes/scanner-modal.blade.php ENDPATH**/ ?>