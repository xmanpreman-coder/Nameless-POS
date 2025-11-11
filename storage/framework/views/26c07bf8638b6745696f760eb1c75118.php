

<?php $__env->startSection('title', 'Camera Test'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('scanner.index')); ?>">Scanner</a></li>
        <li class="breadcrumb-item active">Camera Test</li>
    </ol>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-camera"></i> Camera Test</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div id="camera-container" style="position: relative; max-width: 640px; margin: 0 auto;">
                            <video id="test-video" style="width: 100%; height: 400px; background: #000; border-radius: 8px;"></video>
                            <div id="camera-info" style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; border-radius: 4px; font-size: 12px;"></div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <div id="camera-status" class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Click "Test Camera" to check your camera functionality
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <button id="start-test" class="btn btn-success">
                            <i class="bi bi-camera"></i> Test Camera
                        </button>
                        <button id="stop-test" class="btn btn-danger" style="display: none;">
                            <i class="bi bi-camera-video-off"></i> Stop Test
                        </button>
                        <button id="switch-camera-test" class="btn btn-info" style="display: none;">
                            <i class="bi bi-arrow-repeat"></i> Switch Camera
                        </button>
                        <button id="capture-test" class="btn btn-warning" style="display: none;">
                            <i class="bi bi-camera-fill"></i> Capture
                        </button>
                    </div>

                    <!-- Captured Image Display -->
                    <div id="capture-result" class="mt-4" style="display: none;">
                        <h6>Captured Image:</h6>
                        <img id="captured-image" class="img-thumbnail" style="max-width: 300px;">
                    </div>

                    <!-- Camera Information -->
                    <div class="mt-4">
                        <h6>Camera Information:</h6>
                        <div id="device-list" class="alert alert-light">
                            <em>Start camera test to see available devices...</em>
                        </div>
                    </div>

                    <!-- Back to Scanner -->
                    <div class="text-center mt-4">
                        <a href="<?php echo e(route('scanner.scan')); ?>" class="btn btn-primary">
                            <i class="bi bi-arrow-left"></i> Back to Scanner
                        </a>
                        <a href="<?php echo e(route('scanner.settings')); ?>" class="btn btn-secondary">
                            <i class="bi bi-gear"></i> Scanner Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('page_scripts'); ?>
<script>
class CameraTester {
    constructor() {
        this.video = document.getElementById('test-video');
        this.canvas = document.createElement('canvas');
        this.stream = null;
        this.currentCamera = 0;
        this.availableDevices = [];
        this.isRunning = false;

        this.bindEvents();
        this.getAvailableDevices();
    }

    bindEvents() {
        document.getElementById('start-test').addEventListener('click', () => {
            this.startCamera();
        });

        document.getElementById('stop-test').addEventListener('click', () => {
            this.stopCamera();
        });

        document.getElementById('switch-camera-test').addEventListener('click', () => {
            this.switchCamera();
        });

        document.getElementById('capture-test').addEventListener('click', () => {
            this.captureImage();
        });
    }

    async getAvailableDevices() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            this.availableDevices = devices.filter(device => device.kind === 'videoinput');
            
            this.updateDeviceList();
        } catch (error) {
            console.error('Error getting devices:', error);
            this.showStatus('Error getting available cameras', 'danger');
        }
    }

    updateDeviceList() {
        const container = document.getElementById('device-list');
        
        if (this.availableDevices.length === 0) {
            container.innerHTML = '<em class="text-muted">No cameras detected</em>';
            return;
        }

        let html = '<strong>Available Cameras:</strong><br>';
        this.availableDevices.forEach((device, index) => {
            const label = device.label || `Camera ${index + 1}`;
            html += `<span class="badge badge-${index === this.currentCamera ? 'primary' : 'secondary'} mr-1">${label}</span>`;
        });

        container.innerHTML = html;
    }

    async startCamera() {
        try {
            this.showStatus('Starting camera...', 'info');

            // Get current device
            const deviceId = this.availableDevices[this.currentCamera]?.deviceId;
            
            const constraints = {
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            };

            // Add device constraint if available
            if (deviceId) {
                constraints.video.deviceId = { exact: deviceId };
            } else {
                // Fallback to facingMode
                constraints.video.facingMode = 'environment';
            }

            this.stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.video.srcObject = this.stream;
            
            await this.video.play();
            this.isRunning = true;

            // Update UI
            document.getElementById('start-test').style.display = 'none';
            document.getElementById('stop-test').style.display = 'inline-block';
            document.getElementById('capture-test').style.display = 'inline-block';
            
            if (this.availableDevices.length > 1) {
                document.getElementById('switch-camera-test').style.display = 'inline-block';
            }

            // Show camera info
            this.showCameraInfo();
            this.updateDeviceList();
            this.showStatus('Camera started successfully!', 'success');

        } catch (error) {
            console.error('Camera start error:', error);
            this.showStatus(`Camera error: ${error.message}`, 'danger');
        }
    }

    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }

        this.video.srcObject = null;
        this.isRunning = false;

        // Update UI
        document.getElementById('start-test').style.display = 'inline-block';
        document.getElementById('stop-test').style.display = 'none';
        document.getElementById('switch-camera-test').style.display = 'none';
        document.getElementById('capture-test').style.display = 'none';

        // Hide camera info
        document.getElementById('camera-info').innerHTML = '';
        this.updateDeviceList();
        this.showStatus('Camera stopped', 'info');
    }

    async switchCamera() {
        if (this.availableDevices.length <= 1) return;

        this.currentCamera = (this.currentCamera + 1) % this.availableDevices.length;
        
        if (this.isRunning) {
            this.stopCamera();
            await new Promise(resolve => setTimeout(resolve, 500));
            this.startCamera();
        }
    }

    captureImage() {
        if (!this.isRunning) return;

        this.canvas.width = this.video.videoWidth;
        this.canvas.height = this.video.videoHeight;

        const ctx = this.canvas.getContext('2d');
        ctx.drawImage(this.video, 0, 0);

        const dataURL = this.canvas.toDataURL('image/jpeg', 0.8);
        
        document.getElementById('captured-image').src = dataURL;
        document.getElementById('capture-result').style.display = 'block';

        this.showStatus('Image captured successfully!', 'success');
    }

    showCameraInfo() {
        if (!this.video.videoWidth || !this.video.videoHeight) {
            setTimeout(() => this.showCameraInfo(), 100);
            return;
        }

        const info = `${this.video.videoWidth} x ${this.video.videoHeight}`;
        const deviceName = this.availableDevices[this.currentCamera]?.label || `Camera ${this.currentCamera + 1}`;
        
        document.getElementById('camera-info').innerHTML = `${deviceName}<br>${info}`;
    }

    showStatus(message, type = 'info') {
        const statusElement = document.getElementById('camera-status');
        statusElement.className = `alert alert-${type}`;
        statusElement.innerHTML = `<i class="bi bi-${this.getStatusIcon(type)}"></i> ${message}`;
    }

    getStatusIcon(type) {
        const icons = {
            'info': 'info-circle',
            'success': 'check-circle',
            'warning': 'exclamation-triangle',
            'danger': 'x-circle'
        };
        return icons[type] || 'info-circle';
    }
}

// Initialize camera tester when page loads
let cameraTester;
document.addEventListener('DOMContentLoaded', function() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        document.getElementById('camera-status').innerHTML = 
            '<i class="bi bi-x-circle"></i> <strong>Camera not supported</strong><br>Your browser or device does not support camera access.';
        document.getElementById('camera-status').className = 'alert alert-danger';
        document.getElementById('start-test').disabled = true;
        return;
    }

    cameraTester = new CameraTester();
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project warnet\Nameless\Modules/Scanner\Resources/views/test-camera.blade.php ENDPATH**/ ?>