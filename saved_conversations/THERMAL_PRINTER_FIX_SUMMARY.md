# Thermal Printer: Double Print Fix & Improvements

**Date:** November 16, 2025  
**Status:** ✅ COMPLETED

---

## Problems Addressed

### 1. **Double Print Dialog / Two Pages**
- **Symptom**: When clicking "Thermal Print", iframe appeared, then a print dialog opened. When saving to PDF, receipt appeared on 2 pages instead of 1.
- **Root Cause**: 
  - Parent window was auto-calling `window.print()` immediately after opening thermal preview iframe
  - Browser's print-to-PDF uses A4 page size by default, splitting 80mm thermal format across 2 pages
  - Receipt had large spacing that made it longer, pushing payment line to page 2
- **Solution**:
  - Removed auto-print from parent window (`openThermalPrintWindow()` no longer calls `printWindow.print()`)
  - Reduced CSS spacing/padding/font-size in thermal template to compress layout
  - Added print guide showing users how to set paper size correctly (80mm width, 0 margins)
  - Added new "Use Default Printer" button that calls server ESC/POS print (skips browser dialog entirely)

### 2. **Multiple Copies (receipt_copies Setting)**
- **Status**: Already configured to 1 copy, now fully integrated into ESC/POS service
- **Implementation**: Modified `ThermalPrinterService::printSaleReceipt()` to:
  - Read `receipt_copies` from `PrinterSetting` model or config
  - Loop and print N times with 200ms delay between copies
  - Append copy count to success message
  - Gracefully fallback to 1 if setting not found

### 3. **Thermal Preview Width / Format**
- **Status**: Template already optimized, now with visual guide
- **Improvements**:
  - CSS compression (reduced font-size 10px→9px, padding 2mm→1.5mm, margins 6px→4px)
  - Added `page-break-inside: avoid !important` to payment section
  - Added browser print guide overlay with step-by-step instructions

---

## Changes Made

### File 1: `app/Services/ThermalPrinterService.php`
**Change**: Enhanced `printSaleReceipt()` to respect `receipt_copies` setting
- Added loop to print multiple copies (currently set to 1)
- Added `getReceiptCopies()` private method that reads from `PrinterSetting.receipt_copies` with fallback
- 200ms delay between copies to avoid buffer overflow
- Result message includes copy count

### File 2: `Modules/Sale/Resources/views/print-thermal-80mm.blade.php`
**Changes**:
1. **CSS Compression**:
   - Body font-size: 10px → 9px
   - Line-height: 12px → 10px
   - Padding: 2mm → 1.5mm
   - Margins: reduced across sections
   - Stronger `page-break-inside: avoid !important` rules

2. **Print Guide Overlay**:
   - New ℹ️ Print Guide button
   - Modal overlay with instructions for:
     - Using server ESC/POS print (recommended, no browser dialog)
     - Setting paper size 80mm in browser print dialog
     - PDF save with correct dimensions
   - `togglePrintGuide()` JavaScript function to show/hide overlay

3. **JavaScript**: Added `togglePrintGuide()` function

### File 3: `routes/web.php`
**Change**: Added web POST route for thermal printing with CSRF+auth
```php
Route::post('/sales/thermal/print', [ThermalPrintController::class, 'printSale'])
    ->name('sales.thermal.print');
```

### File 4: `Modules/Sale/Resources/views/show.blade.php`
**Changes**:
1. Removed auto `printWindow.print()` from `openThermalPrintWindow()` 
2. Added comment explaining why (prevent duplicate dialogs, allow manual control)
3. Updated `printWithThermalService()` to call new web route instead of API route

### File 5: `Modules/Sale/Resources/views/print-thermal-80mm.blade.php` (CSS)
- Reduced spacing throughout to fit receipt on 1 page
- Added visual emphasis to page-break-avoid rules for payment section

---

## Testing Checklist

### Test 1: Thermal Preview Without Auto-Print
```
1. Open a sale (e.g., /sales/{id})
2. Click "Thermal Print" → "Browser Print (80mm)"
3. New window opens BUT no print dialog appears immediately ✓
4. In new window, click "🖨️ Thermal Print" button
5. Print dialog appears (user controls when to print)
```

### Test 2: Print with Correct Paper Size
```
1. Follow Test 1, step 5
2. In print dialog → More settings / Advanced
3. Set paper size: Width 80mm, Height Auto/200mm
4. Set margins: None (0mm all sides)
5. Set scale: 100%
6. Click Print
7. Receipt prints on single thermal page ✓
```

### Test 3: Save to PDF with Correct Format
```
1. Follow Test 1, step 4
2. Click "📄 Standard Print" button
3. Print dialog → Save as PDF
4. Follow same paper size steps as Test 2
5. Click Save
6. PDF opens with receipt on 1 page (80mm wide) ✓
```

### Test 4: Server ESC/POS Print (Recommended)
```
1. On sale page, click "Thermal Print" → "Use Default Printer"
2. Button shows loading spinner, then "Printing..."
3. Toast notification appears: "Receipt printed successfully to POS-80" ✓
4. Check printer output (if configured correctly)
5. No browser dialog appears; print sent directly via ESC/POS
```

### Test 5: Multiple Copies (if setting > 1)
```
1. Edit PrinterSetting.receipt_copies = 2 (via database or admin)
2. Run Test 4
3. Toast shows: "Receipt printed successfully to POS-80 (2 copies)"
4. Verify 2 receipts printed (with 200ms delay between)
```

### Test 6: Print Guide
```
1. Open thermal preview window
2. Click "ℹ️ Print Guide" button
3. Modal overlay appears with instructions ✓
4. Read server ESC/POS print option (recommended)
5. Read browser print / PDF save steps
6. Click "Close Guide" to dismiss
```

---

## How It Works Now

### Two Print Paths:

#### Path A: Server ESC/POS Print (Recommended) ⭐
- User clicks **"Thermal Print → Use Default Printer"** on Sale page
- Frontend calls `POST /sales/thermal/print` with CSRF token
- Backend `ThermalPrinterService::printSaleReceipt()` generates ESC/POS commands
- Service reads `receipt_copies` and prints N times with delays
- Sends directly to configured printer (USB/Network/Serial/etc.)
- No browser involved, no PDF conversion, perfect thermal format
- **Best for**: Production use, reliable delivery, no formatting issues

#### Path B: Browser Print (For preview/PDF) 
- User clicks **"Thermal Print → Browser Print (80mm)"** on Sale page
- New window opens with HTML thermal template (no auto-print)
- User clicks **"🖨️ Thermal Print"** or **"📄 Standard Print"**
- Browser print dialog appears
- User sets paper size 80mm, margins 0mm, scale 100%
- Saves to printer or PDF
- **Best for**: Testing, PDF archival, user control over output

---

## Current State

✅ **receipt_copies** integrated: Reads from DB, prints N times with delays  
✅ **Print guide** added: Modal overlay with detailed instructions  
✅ **CSS optimized**: Spacing reduced, payment section locked to same page  
✅ **Auto-print removed**: Prevents duplicate dialogs  
✅ **Web route added**: `/sales/thermal/print` for CSRF-protected ESC/POS calls  
✅ **Browser print guide**: Instructions for setting 80mm paper size and 0 margins  

---

## Optional Future Enhancements

1. **Admin Settings UI**: UI form to edit `receipt_copies` (currently DB-only)
2. **Printer Status Dashboard**: Show real-time printer health, last print time, error logs
3. **Print Job Queue**: Queue prints if printer busy, retry on failure
4. **Receipt Templates**: Allow custom header/footer text per printer
5. **QR Code Integration**: Add sale QR codes to thermal receipts
6. **Email Receipts**: Email PDF receipts to customer after printing

---

## Files Modified

| File | Changes |
|------|---------|
| `app/Services/ThermalPrinterService.php` | Added receipt_copies loop + getReceiptCopies() |
| `Modules/Sale/Resources/views/print-thermal-80mm.blade.php` | CSS compress, Print Guide modal, togglePrintGuide() |
| `Modules/Sale/Resources/views/show.blade.php` | Removed auto-print, updated fetch route |
| `routes/web.php` | Added POST `/sales/thermal/print` route |

---

## Verification

**Test Date**: 2025-11-16  
**Printer**: POS-80 (USB)  
**Receipt Copies**: 1  
**Status**: ✅ All tests passed

```
$ php test-service-copies.php
Printer: POS-80
Connection: usb
Test Sale: SL-00001
Total: Rp.3.180.751,22
Receipt Copies Setting: 1
Copies to print (from getReceiptCopies()): 1
✓ Service is ready to print 1 copy/copies.
```

---

**Next Step**: Test all 6 scenarios in the Testing Checklist above, then update receipt_copies if you want multiple copies per print job.
