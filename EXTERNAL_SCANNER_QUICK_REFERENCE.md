# External Scanner Setup - Quick Reference

## For Users

### How to Setup External Scanner
1. Go to **Scanner Settings** page
2. Select **Scanner Type**: "External Scanner Setup (Mobile App)"
3. Use the QR code to configure your mobile app automatically, OR
4. Manually configure your scanner app with these settings:
   - **Server URL**: Your POS system URL
   - **Endpoint**: `/scanner/external/receive`
   - **Method**: POST
   - **Parameter**: `barcode`

### Recommended Apps
- **Barcode to PC** (Primary recommendation)
- **QR & Barcode Scanner** (Alternative)
- Any app that supports HTTP POST requests

### Testing Connection
- Use the "Test Connection" button in settings to verify setup
- App will send test barcode to verify communication

## For Developers

### API Endpoint
```
POST /scanner/external/receive
Content-Type: application/x-www-form-urlencoded
```

### Request Format
```
barcode=123456789&source=external_app
```

### Response Format (Success)
```json
{
  "success": true,
  "message": "Product found",
  "data": {
    "barcode": "123456789",
    "actual_barcode": "8123456789",
    "reconstructed": true,
    "product": {
      "id": 1,
      "name": "Product Name",
      "code": "PROD001",
      "barcode": "8123456789",
      "price": 10000,
      "stock": 50,
      "image": "http://pos.local/storage/products/image.jpg",
      "category": "Category Name"
    }
  }
}
```

### Response Format (Not Found)
```json
{
  "success": false,
  "message": "Product not found",
  "data": {
    "barcode": "123456789",
    "suggestions": []
  }
}
```

### Features
- ✅ CSRF protection bypassed for external requests
- ✅ Barcode reconstruction for missing first digits
- ✅ Product search with fallback suggestions
- ✅ Comprehensive logging for debugging
- ✅ Error handling with appropriate HTTP status codes
- ✅ Test connection functionality

### Security Notes
- Endpoint is public (no authentication required)
- Input validation prevents malicious data
- Rate limiting should be considered for production
- All requests are logged for audit purposes

## Configuration Files Modified
- `Modules/Scanner/Resources/views/settings.blade.php` - UI
- `Modules/Scanner/Routes/web.php` - Routes
- `Modules/Scanner/Http/Controllers/ScannerController.php` - Logic
- `Modules/Scanner/Entities/ScannerSetting.php` - Model
- `app/Http/Middleware/VerifyCsrfToken.php` - Security
- Database migration for external_settings field