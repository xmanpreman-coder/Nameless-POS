# Test Download Manually - Quick Commands

## Run These Commands in Browser Console

### 1. Check if Download Function Exists
```javascript
console.log('downloadBarcodesFromDOM function available:', typeof downloadBarcodesFromDOM);
```

### 2. Manually Trigger Download Function
```javascript
// Force trigger the download function
if (typeof downloadBarcodesFromDOM === 'function') {
    console.log('🚀 MANUAL TRIGGER: Starting download...');
    downloadBarcodesFromDOM();
} else {
    console.error('❌ downloadBarcodesFromDOM function not found');
}
```

### 3. Find All Download Buttons
```javascript
// Find download buttons
const buttons = document.querySelectorAll('button, .btn');
console.log('All buttons on page:');
buttons.forEach((btn, i) => {
    const text = btn.textContent.trim();
    const wireClick = btn.getAttribute('wire:click');
    if (text.toLowerCase().includes('download') || 
        text.toLowerCase().includes('png') || 
        text.toLowerCase().includes('image') ||
        wireClick) {
        console.log(`Button ${i}: "${text}" wire:click="${wireClick}"`);
        console.log(btn);
    }
});
```

### 4. Find Barcode Elements
```javascript
// Check for barcode elements
const selectors = [
    '.barcode-item-container',
    '.barcode-container',
    '[class*="barcode"]',
    'svg',
    '.card',
    '.row .col'
];

selectors.forEach(selector => {
    const found = document.querySelectorAll(selector);
    if (found.length > 0) {
        console.log(`✅ ${selector}: ${found.length} elements`);
        if (found.length <= 3) {
            console.log('Sample:', found[0]);
        }
    }
});
```

### 5. Test Livewire Event Manually
```javascript
// Manually dispatch the Livewire event
console.log('🧪 Testing Livewire event dispatch...');

// Method 1: Livewire dispatch
if (window.Livewire) {
    window.Livewire.dispatch('download-barcode-images');
    console.log('✅ Livewire.dispatch sent');
}

// Method 2: Custom event
const event = new CustomEvent('download-barcode-images');
document.dispatchEvent(event);
console.log('✅ Custom event dispatched');

// Method 3: Window event
window.dispatchEvent(new CustomEvent('download-barcode-images'));
console.log('✅ Window event dispatched');
```

### 6. Test html2canvas Directly
```javascript
// Test html2canvas with simple element
if (typeof html2canvas !== 'undefined') {
    console.log('🧪 Testing html2canvas with body element...');
    
    const testElement = document.querySelector('body');
    html2canvas(testElement, { 
        width: 200, 
        height: 200,
        scale: 1 
    }).then(canvas => {
        console.log('✅ html2canvas works! Canvas created:', canvas);
        
        // Try to download test canvas
        const link = document.createElement('a');
        link.download = 'test-canvas.png';
        link.href = canvas.toDataURL();
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        console.log('✅ Test download triggered');
        
    }).catch(err => {
        console.error('❌ html2canvas failed:', err);
    });
} else {
    console.error('❌ html2canvas not available');
}
```

## Expected Results

### If Download Function Works:
```
🚀 MANUAL TRIGGER: Starting download...
=== BARCODE DOWNLOAD DEBUG START ===
🔍 Found barcode containers: X
SUCCESS: html2canvas library available, starting download process...
```

### If Elements Not Found:
```
🔍 Found barcode containers: 0
⚠️ Primary selector failed, trying alternatives...
✅ Found X elements with selector "svg" (SVG elements)
```

### If Button Events Work:
```
🔥 Livewire v3: download-barcode-images event received
🖱️ MANUAL: Download button clicked!
⏰ MANUAL: Triggering download after 1s delay...
```

## Quick Test Sequence

1. **Go to Print Barcode page**
2. **Generate some barcodes first** 
3. **Open browser console (F12)**
4. **Copy and paste commands above one by one**
5. **Check which step fails**

## Most Likely Issues

### Issue 1: Button Click Not Triggering Event
**Test**: Run command #3 to find buttons, then #5 to test events manually

### Issue 2: Wrong Barcode Element Selector
**Test**: Run command #4 to find actual barcode elements

### Issue 3: Download Function Not Accessible
**Test**: Run command #1 and #2 to test function directly

### Issue 4: html2canvas Issues
**Test**: Run command #6 to test html2canvas directly

---

## Quick Fix Commands

### If you find the right selector:
```javascript
// Update selector and test
const correctContainers = document.querySelectorAll('CORRECT_SELECTOR_HERE');
console.log('Found with correct selector:', correctContainers.length);
```

### If button found but not triggering:
```javascript
// Click button manually
const btn = document.querySelector('button[wire\\:click*="download"]');
if (btn) {
    btn.click();
    console.log('Button clicked manually');
}
```

**Run these commands to identify the exact issue!**