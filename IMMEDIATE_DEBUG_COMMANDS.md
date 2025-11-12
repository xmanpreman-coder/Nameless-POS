# Immediate Debug Commands - Test Sekarang!

## Command 1: Check Download Function
```javascript
console.log('=== TESTING DOWNLOAD FUNCTION ===');
console.log('downloadBarcodesFromDOM function available:', typeof downloadBarcodesFromDOM);
if (typeof downloadBarcodesFromDOM === 'function') {
    console.log('✅ Function exists, testing manual trigger...');
    downloadBarcodesFromDOM();
} else {
    console.error('❌ downloadBarcodesFromDOM function not found!');
}
```

## Command 2: Check Barcode Elements
```javascript
console.log('=== CHECKING BARCODE ELEMENTS ===');
const containers = document.querySelectorAll('.barcode-item-container');
console.log('Barcode containers found:', containers.length);

if (containers.length > 0) {
    console.log('✅ Containers exist!');
    console.log('Sample container:', containers[0]);
    console.log('Container classes:', containers[0].className);
    console.log('Container HTML preview:', containers[0].outerHTML.substring(0, 300) + '...');
} else {
    console.log('❌ No containers found, checking alternatives...');
    
    const alternatives = ['svg', '.card', '.row .col', '[class*="barcode"]'];
    alternatives.forEach(sel => {
        const found = document.querySelectorAll(sel);
        if (found.length > 0) {
            console.log(`Found ${found.length} elements with ${sel}`);
        }
    });
}
```

## Command 3: Check Button Click Event
```javascript
console.log('=== TESTING BUTTON CLICK ===');
const btn = document.querySelector('button[wire\\:click="downloadImage"]');
console.log('Download button found:', btn);
if (btn) {
    console.log('Button text:', btn.textContent.trim());
    console.log('Button wire:click:', btn.getAttribute('wire:click'));
    
    // Add click listener untuk debug
    btn.addEventListener('click', function() {
        console.log('🖱️ BUTTON CLICKED! Event detected');
    });
    
    console.log('✅ Click listener added. Now try clicking the button again.');
} else {
    console.log('❌ Button not found!');
}
```

## Command 4: Test Livewire Event Manually
```javascript
console.log('=== TESTING LIVEWIRE EVENTS ===');

// Check Livewire availability
if (window.Livewire) {
    console.log('✅ Livewire available');
    
    // Try dispatching event manually
    try {
        window.Livewire.dispatch('download-barcode-images');
        console.log('✅ Livewire.dispatch() called');
    } catch(e) {
        console.log('❌ Livewire.dispatch failed:', e);
        
        try {
            window.Livewire.emit('download-barcode-images');
            console.log('✅ Livewire.emit() called (fallback)');
        } catch(e2) {
            console.log('❌ Livewire.emit also failed:', e2);
        }
    }
} else {
    console.log('❌ Livewire not available!');
}

// Custom event fallback
const customEvent = new CustomEvent('download-barcode-images');
document.dispatchEvent(customEvent);
console.log('✅ Custom event dispatched');
```

## Command 5: Test Simple html2canvas
```javascript
console.log('=== TESTING HTML2CANVAS BASIC ===');

// Create test element
const testDiv = document.createElement('div');
testDiv.innerHTML = '<h3>TEST DOWNLOAD</h3><p>This is a test element</p>';
testDiv.style.width = '300px';
testDiv.style.height = '200px';
testDiv.style.backgroundColor = '#f0f0f0';
testDiv.style.border = '2px solid #333';
testDiv.style.padding = '20px';
testDiv.style.position = 'absolute';
testDiv.style.top = '10px';
testDiv.style.left = '10px';
testDiv.style.zIndex = '9999';
document.body.appendChild(testDiv);

console.log('Test element created, converting with html2canvas...');

html2canvas(testDiv).then(canvas => {
    console.log('✅ html2canvas SUCCESS! Canvas created:', canvas);
    console.log('Canvas size:', canvas.width + 'x' + canvas.height);
    
    // Try download
    try {
        const link = document.createElement('a');
        link.download = 'test-download.png';
        link.href = canvas.toDataURL('image/png');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        console.log('✅ TEST DOWNLOAD TRIGGERED! Check your downloads folder for test-download.png');
    } catch(e) {
        console.log('❌ Download failed:', e);
    }
    
    // Clean up
    document.body.removeChild(testDiv);
    
}).catch(err => {
    console.error('❌ html2canvas FAILED:', err);
    document.body.removeChild(testDiv);
});
```

---

## IMPORTANT: Run These Commands In Order

1. **First**: Command 2 (check elements)
2. **Second**: Command 1 (test function) 
3. **Third**: Command 5 (test html2canvas basic)
4. **Fourth**: Command 3 (test button click)
5. **Fifth**: Command 4 (test Livewire events)

## Expected Outcomes:

### If Command 1 Works:
- You'll see download process start
- Files should download

### If Command 1 Fails:
- Function doesn't exist or has error
- Need to check function definition

### If Command 5 Fails:
- html2canvas or browser issue
- Download mechanism problem

### If Commands Work But Real Download Doesn't:
- Issue with barcode element structure
- Selector problems
- Event not triggering function

**Copy and run these commands one by one and tell me the exact output! This will identify the exact problem.**