/**
 * Scanner Utility Functions
 * Provides common functionality for barcode scanning across the application
 */

class ScannerUtils {
    static playBeep() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gain = audioContext.createGain();
            
            oscillator.connect(gain);
            gain.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'square';
            gain.gain.value = 0.1;
            
            oscillator.start();
            setTimeout(() => oscillator.stop(), 200);
        } catch (error) {
            console.warn('Audio not supported or permission denied');
        }
    }

    static vibrate(duration = 200) {
        if ('vibrate' in navigator) {
            navigator.vibrate(duration);
        }
    }

    static validateBarcode(barcode) {
        // Remove any whitespace and check if valid
        barcode = barcode.trim();
        
        // Basic validation - must be at least 4 characters
        if (barcode.length < 4) {
            return false;
        }

        // Check for common barcode patterns
        const patterns = [
            /^\d{8}$/, // EAN-8
            /^\d{12}$/, // UPC-A
            /^\d{13}$/, // EAN-13
            /^[0-9A-Za-z\-\.]+$/ // Code 128, Code 39, etc.
        ];

        return patterns.some(pattern => pattern.test(barcode));
    }

    static formatBarcode(barcode) {
        return barcode.trim().toUpperCase();
    }

    static showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show scanner-notification`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideInRight 0.3s ease-out;
        `;
        
        notification.innerHTML = `
            <strong>${this.getNotificationIcon(type)}</strong> ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    static getNotificationIcon(type) {
        const icons = {
            'success': '✓',
            'error': '✗',
            'warning': '⚠',
            'info': 'ℹ'
        };
        return icons[type] || 'ℹ';
    }

    static getCameraConstraints(preferredCamera = 'back') {
        return {
            video: {
                facingMode: preferredCamera === 'back' ? 'environment' : 'user',
                width: { ideal: 640 },
                height: { ideal: 480 },
                frameRate: { ideal: 30 }
            }
        };
    }

    static async checkCameraPermission() {
        try {
            const result = await navigator.permissions.query({ name: 'camera' });
            return result.state;
        } catch (error) {
            return 'unknown';
        }
    }

    static async getAvailableCameras() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            return devices.filter(device => device.kind === 'videoinput');
        } catch (error) {
            console.error('Error getting camera devices:', error);
            return [];
        }
    }
}

// Add CSS for notifications
if (!document.getElementById('scanner-utils-styles')) {
    const style = document.createElement('style');
    style.id = 'scanner-utils-styles';
    style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .scanner-notification {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-radius: 8px;
    }
`;
    document.head.appendChild(style);
}

// Make available globally
window.ScannerUtils = ScannerUtils;