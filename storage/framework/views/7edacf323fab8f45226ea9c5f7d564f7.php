

<?php $__env->startSection('title', 'Scanner Settings'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('scanner.index')); ?>">Scanner</a></li>
        <li class="breadcrumb-item active">Settings</li>
    </ol>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <?php echo $__env->make('utils.alerts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-upc-scan"></i> Scanner Settings</h5>
                </div>
                <div class="card-body">
                    <?php
                        $settings = \Modules\Scanner\Entities\ScannerSetting::getSettings();
                    ?>
                    
                    <form action="<?php echo e(route('scanner.settings.update')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        
                        <!-- Scanner Type Selection -->
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="scanner_type">Scanner Type <span class="text-danger">*</span></label>
                                    <select name="scanner_type" id="scanner_type" class="form-control" required>
                                        <option <?php echo e($settings->scanner_type == 'camera' ? 'selected' : ''); ?> value="camera">
                                            Camera Scanner (Laptop/Phone)
                                        </option>
                                        <option <?php echo e($settings->scanner_type == 'usb' ? 'selected' : ''); ?> value="usb">
                                            USB Barcode Scanner
                                        </option>
                                        <option <?php echo e($settings->scanner_type == 'bluetooth' ? 'selected' : ''); ?> value="bluetooth">
                                            Bluetooth Scanner
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="scan_mode">Scan Mode <span class="text-danger">*</span></label>
                                    <select name="scan_mode" id="scan_mode" class="form-control" required>
                                        <option <?php echo e($settings->scan_mode == 'auto' ? 'selected' : ''); ?> value="auto">
                                            Automatic Scan
                                        </option>
                                        <option <?php echo e($settings->scan_mode == 'manual' ? 'selected' : ''); ?> value="manual">
                                            Manual Capture
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Camera Settings -->
                        <div id="camera-settings" class="scanner-settings" style="<?php echo e($settings->scanner_type !== 'camera' ? 'display: none;' : ''); ?>">
                            <h6 class="text-primary mt-4"><i class="bi bi-camera"></i> Camera Settings</h6>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="preferred_camera">Preferred Camera</label>
                                        <select name="preferred_camera" id="preferred_camera" class="form-control">
                                            <option <?php echo e($settings->preferred_camera == 'back' ? 'selected' : ''); ?> value="back">
                                                Back Camera (Recommended)
                                            </option>
                                            <option <?php echo e($settings->preferred_camera == 'front' ? 'selected' : ''); ?> value="front">
                                                Front Camera
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="scan_timeout">Scan Timeout (seconds)</label>
                                        <input type="number" name="scan_timeout" id="scan_timeout" 
                                               class="form-control" value="<?php echo e($settings->scan_timeout); ?>" 
                                               min="5" max="120">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="auto_focus" id="auto_focus" 
                                               class="form-check-input" <?php echo e($settings->auto_focus ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="auto_focus">
                                            Auto Focus (Recommended)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Camera Tips:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Use back camera for better barcode scanning quality</li>
                                    <li>Ensure good lighting for optimal scanning</li>
                                    <li>Keep device steady when scanning</li>
                                </ul>
                            </div>
                        </div>

                        <!-- USB Scanner Settings -->
                        <div id="usb-settings" class="scanner-settings" style="<?php echo e($settings->scanner_type !== 'usb' ? 'display: none;' : ''); ?>">
                            <h6 class="text-primary mt-4"><i class="bi bi-usb"></i> USB Scanner Settings</h6>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>USB Scanner Setup:</strong>
                                <ol class="mb-0 mt-2">
                                    <li>Connect your USB barcode scanner to the device</li>
                                    <li>Most USB scanners work as keyboard input devices</li>
                                    <li>Scanner should be automatically detected</li>
                                    <li>Test scanner functionality below</li>
                                </ol>
                            </div>
                        </div>

                        <!-- Bluetooth Scanner Settings -->
                        <div id="bluetooth-settings" class="scanner-settings" style="<?php echo e($settings->scanner_type !== 'bluetooth' ? 'display: none;' : ''); ?>">
                            <h6 class="text-primary mt-4"><i class="bi bi-bluetooth"></i> Bluetooth Scanner Settings</h6>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Bluetooth Scanner Setup:</strong>
                                <ol class="mb-0 mt-2">
                                    <li>Pair your Bluetooth scanner with this device</li>
                                    <li>Ensure scanner is in keyboard emulation mode</li>
                                    <li>Scanner should appear as connected input device</li>
                                    <li>Test connection using the scanner test below</li>
                                </ol>
                            </div>
                        </div>

                        <!-- General Settings -->
                        <h6 class="text-primary mt-4"><i class="bi bi-gear"></i> General Settings</h6>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="beep_sound" id="beep_sound" 
                                           class="form-check-input" <?php echo e($settings->beep_sound ? 'checked' : ''); ?>>
                                    <label class="form-check-label" for="beep_sound">
                                        Beep Sound on Successful Scan
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="vibration" id="vibration" 
                                           class="form-check-input" <?php echo e($settings->vibration ? 'checked' : ''); ?>>
                                    <label class="form-check-label" for="vibration">
                                        Vibration Feedback (Mobile)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check"></i> Save Settings
                                </button>
                                <a href="<?php echo e(route('scanner.test-camera')); ?>" class="btn btn-info ml-2">
                                    <i class="bi bi-camera"></i> Test Camera
                                </a>
                            </div>
                            <a href="<?php echo e(route('scanner.scan')); ?>" class="btn btn-success">
                                <i class="bi bi-upc-scan"></i> Start Scanning
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('page_scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scannerType = document.getElementById('scanner_type');
    const cameraSettings = document.getElementById('camera-settings');
    const usbSettings = document.getElementById('usb-settings');
    const bluetoothSettings = document.getElementById('bluetooth-settings');

    function toggleScannerSettings() {
        // Hide all settings
        cameraSettings.style.display = 'none';
        usbSettings.style.display = 'none';
        bluetoothSettings.style.display = 'none';

        // Show relevant settings
        const selectedType = scannerType.value;
        if (selectedType === 'camera') {
            cameraSettings.style.display = 'block';
        } else if (selectedType === 'usb') {
            usbSettings.style.display = 'block';
        } else if (selectedType === 'bluetooth') {
            bluetoothSettings.style.display = 'block';
        }
    }

    scannerType.addEventListener('change', toggleScannerSettings);
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project warnet\Nameless\Modules/Scanner\Resources/views/settings.blade.php ENDPATH**/ ?>