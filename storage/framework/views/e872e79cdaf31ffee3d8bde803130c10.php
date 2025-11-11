

<?php $__env->startSection('title', 'Barcode Scanner'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Home</a></li>
        <li class="breadcrumb-item active">Scanner</li>
    </ol>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-upc-scan"></i> Barcode Scanner Dashboard</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="card bg-gradient-success text-white mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white">Quick Scan</h6>
                                            <p class="mb-0">Start scanning products immediately</p>
                                        </div>
                                        <div>
                                            <i class="bi bi-upc-scan" style="font-size: 2.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="<?php echo e(route('scanner.scan')); ?>" class="btn btn-light btn-sm">
                                        <i class="bi bi-play-fill"></i> Start Scanning
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="card bg-gradient-info text-white mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white">Scanner Settings</h6>
                                            <p class="mb-0">Configure camera and scanner options</p>
                                        </div>
                                        <div>
                                            <i class="bi bi-gear" style="font-size: 2.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="<?php echo e(route('scanner.settings')); ?>" class="btn btn-light btn-sm">
                                        <i class="bi bi-sliders"></i> Settings
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="card bg-gradient-warning text-white mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white">Test Camera</h6>
                                            <p class="mb-0">Test camera functionality</p>
                                        </div>
                                        <div>
                                            <i class="bi bi-camera" style="font-size: 2.5rem;"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="<?php echo e(route('scanner.test-camera')); ?>" class="btn btn-light btn-sm">
                                        <i class="bi bi-camera-fill"></i> Test Camera
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scanner Info -->
                    <div class="row mt-4">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Scanner Information</h6>
                                </div>
                                <div class="card-body">
                                    <h6>Supported Scanner Types:</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-center p-3 border rounded">
                                                <i class="bi bi-camera text-success" style="font-size: 2rem;"></i>
                                                <h6 class="mt-2">Camera Scanner</h6>
                                                <p class="text-muted small">Use laptop or mobile camera to scan barcodes</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-3 border rounded">
                                                <i class="bi bi-usb text-primary" style="font-size: 2rem;"></i>
                                                <h6 class="mt-2">USB Scanner</h6>
                                                <p class="text-muted small">Connect USB barcode scanner for fast scanning</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-3 border rounded">
                                                <i class="bi bi-bluetooth text-info" style="font-size: 2rem;"></i>
                                                <h6 class="mt-2">Bluetooth Scanner</h6>
                                                <p class="text-muted small">Wireless Bluetooth scanner support</p>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <h6>Supported Barcode Formats:</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><i class="bi bi-check text-success"></i> Code 128</li>
                                                <li><i class="bi bi-check text-success"></i> Code 39</li>
                                                <li><i class="bi bi-check text-success"></i> EAN-13</li>
                                                <li><i class="bi bi-check text-success"></i> EAN-8</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><i class="bi bi-check text-success"></i> UPC-A</li>
                                                <li><i class="bi bi-check text-success"></i> UPC-E</li>
                                                <li><i class="bi bi-check text-success"></i> Codabar</li>
                                                <li><i class="bi bi-check text-success"></i> ITF</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-gear"></i> Current Settings</h6>
                                </div>
                                <div class="card-body">
                                    <?php
                                        $settings = \Modules\Scanner\Entities\ScannerSetting::getSettings();
                                    ?>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Scanner Type:</strong></td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    <?php echo e(ucfirst($settings->scanner_type)); ?>

                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Scan Mode:</strong></td>
                                            <td><?php echo e(ucfirst($settings->scan_mode)); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Beep Sound:</strong></td>
                                            <td>
                                                <i class="bi bi-<?php echo e($settings->beep_sound ? 'check text-success' : 'x text-danger'); ?>"></i>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Vibration:</strong></td>
                                            <td>
                                                <i class="bi bi-<?php echo e($settings->vibration ? 'check text-success' : 'x text-danger'); ?>"></i>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Auto Focus:</strong></td>
                                            <td>
                                                <i class="bi bi-<?php echo e($settings->auto_focus ? 'check text-success' : 'x text-danger'); ?>"></i>
                                            </td>
                                        </tr>
                                    </table>

                                    <a href="<?php echo e(route('scanner.settings')); ?>" class="btn btn-primary btn-sm btn-block">
                                        <i class="bi bi-sliders"></i> Modify Settings
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project warnet\Nameless\Modules/Scanner\Resources/views/index.blade.php ENDPATH**/ ?>