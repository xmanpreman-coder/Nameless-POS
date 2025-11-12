# Quick Debug Commands - Copy & Paste di Browser Console

Sekarang saya tahu html2canvas loaded successfully, mari kita test manual:

## Command 1: Test Manual Download Function
```javascript
// Test apakah function downloadBarcodesFromDOM bisa dipanggil manual
console.log('Testing manual download...');
if (typeof downloadBarcodesFromDOM === 'function') {
    downloadBarcodesFromDOM();
} else {
    console.error('downloadBarcodesFromDOM function not found');
}
```

## Command 2: Check Button dan Event
```javascript
// Find download button
const btn = document.querySelector('button[wire\\:click="downloadImage"]');
console.log('Download button found:', btn);
if (btn) {
    console.log('Button text:', btn.textContent.trim());
    
    // Click button manually
    btn.click();
    console.log('Button clicked manually');
}
```

## Command 3: Check Barcode Containers
```javascript
// Check barcode containers
const containers = document.querySelectorAll('.barcode-item-container');
console.log('Barcode containers found:', containers.length);
if (containers.length > 0) {
    console.log('Sample container:', containers[0]);
    console.log('Container HTML:', containers[0].outerHTML.substring(0, 200) + '...');
}
```

## Command 4: Test Livewire Event Manual
```javascript
// Manual trigger Livewire event
console.log('Testing Livewire event...');
if (window.Livewire) {
    // Try different event methods
    try {
        window.Livewire.dispatch('download-barcode-images');
        console.log('Livewire.dispatch sent');
    } catch(e) {
        console.log('Livewire.dispatch failed:', e);
    }
    
    try {
        window.Livewire.emit('download-barcode-images');
        console.log('Livewire.emit sent');
    } catch(e) {
        console.log('Livewire.emit failed:', e);
    }
}

// Custom event
const event = new CustomEvent('livewire:download-barcode-images');
document.dispatchEvent(event);
console.log('Custom event dispatched');
```

## Command 5: Test Simple html2canvas
```javascript
// Test html2canvas dengan element sederhana
console.log('Testing html2canvas with simple element...');
const testDiv = document.createElement('div');
testDiv.innerHTML = 'TEST DOWNLOAD';
testDiv.style.width = '200px';
testDiv.style.height = '100px';
testDiv.style.backgroundColor = 'red';
testDiv.style.color = 'white';
testDiv.style.padding = '20px';
document.body.appendChild(testDiv);

html2canvas(testDiv).then(canvas => {
    console.log('html2canvas works! Canvas:', canvas);
    
    // Try download
    const link = document.createElement('a');
    link.download = 'test-html2canvas.png';
    link.href = canvas.toDataURL();
    link.click();
    console.log('Test download triggered');
    
    document.body.removeChild(testDiv);
}).catch(err => {
    console.error('html2canvas failed:', err);
    document.body.removeChild(testDiv);
});
```

---

## Jalankan Commands Ini Berurutan:

1. **Pertama**: Command 3 - pastikan ada barcode containers
2. **Kedua**: Command 1 - test manual download function
3. **Ketiga**: Command 5 - test html2canvas works
4. **Keempat**: Command 2 - test button click
5. **Kelima**: Command 4 - test Livewire events

## Expected Results:

**Jika Command 1 berhasil**: Akan muncul console log download process dan file PNG download
**Jika Command 1 gagal**: Kita tahu masalahnya di function atau selectors
**Jika Command 5 gagal**: Masalah di html2canvas atau browser permissions
**Jika Command 2 gagal**: Masalah di button click atau Livewire event

Copy paste dan jalankan command ini satu per satu, kemudian report hasilnya!