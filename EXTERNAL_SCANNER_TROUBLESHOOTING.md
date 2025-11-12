# External Scanner Setup - Troubleshooting Guide

## Common Issues and Solutions

### 1. Route [scanner-settings.index] not defined

**Problem**: Getting RouteNotFoundException when accessing scanner settings

**Solution**: ✅ Fixed
- Added missing route definition in `routes/web.php`
- Route now properly defined as both `scanner-settings.index` and `scanner.settings`

**Code Fix**:
```php
// Added to routes/web.php
Route::get('/scanner-settings', 'ScannerSettingsController@index')->name('scanner-settings.index');
```

### 2. 500 Internal Server Error on Scanner Settings Page

**Symptoms**: 
- Page crashes when loading scanner settings
- JavaScript errors about missing routes

**Solution**: ✅ Fixed
- Fixed all route references in views
- Added proper external scanner endpoint
- Updated CSRF exception list

**Verification**:
```bash
php artisan route:list --name=scanner
php artisan route:clear
```

### 3. External Scanner Connection Test Fails

**Possible Causes**:
- CSRF protection blocking requests
- Route not accessible
- Network connectivity issues

**Solution Checklist**:
- [✅] CSRF exception added for `scanner/external/receive`
- [✅] Route properly defined without auth middleware
- [✅] Controller method handles external requests
- [✅] Proper error handling and logging implemented

### 4. QR Code Not Generating

**Possible Causes**:
- CDN library not loading
- JavaScript errors preventing execution
- Missing container element

**Solutions**:
- Verify QRCode.js library loads from CDN
- Check browser console for JavaScript errors
- Ensure `external-qr-code` div exists in DOM

**Fallback**: Manual configuration using displayed URLs

### 5. Mobile App Cannot Connect

**Troubleshooting Steps**:

1. **Verify Network Connection**
   ```bash
   # Test from mobile device browser
   curl -X POST http://YOUR_POS_IP/scanner/external/receive \
        -d "barcode=TEST_CONNECTION"
   ```

2. **Check Firewall Settings**
   - Ensure port 8000 (or your port) is open
   - Allow incoming connections from mobile device IP

3. **Verify App Configuration**
   - Server URL: `http://YOUR_POS_IP:8000`
   - Endpoint: `/scanner/external/receive`
   - Method: POST
   - Parameter: `barcode`

4. **Test with Browser**
   - Open POS system URL in mobile browser
   - Verify accessibility before configuring app

### 6. Database Migration Issues

**Problem**: external_settings column not found

**Solution**:
```bash
php artisan migrate --path=Modules/Scanner/Database/Migrations/2025_01_15_000000_add_external_settings_to_scanner_settings_table.php
```

**Verify**:
```sql
DESCRIBE scanner_settings;
-- Should show external_settings column as JSON nullable
```

## Debug Tools

### 1. Route Testing
```bash
# List all scanner routes
php artisan route:list --name=scanner

# Clear route cache
php artisan route:clear
```

### 2. Connection Testing
```bash
# Test external endpoint
curl -X POST http://localhost:8000/scanner/external/receive \
     -H "Content-Type: application/x-www-form-urlencoded" \
     -d "barcode=TEST_CONNECTION&source=debug"
```

### 3. Log Monitoring
```bash
# Monitor Laravel logs
tail -f storage/logs/laravel.log

# Filter for scanner-related entries
grep -i scanner storage/logs/laravel.log
```

### 4. Network Diagnostics
```bash
# Check if server is accessible from network
netstat -an | grep :8000

# Test from another device on same network
ping YOUR_POS_IP
telnet YOUR_POS_IP 8000
```

## Mobile App Specific Issues

### Barcode to PC App

**Configuration**:
- Server: `http://YOUR_POS_IP:8000`
- Port: `8000` (or your port)
- Path: `/scanner/external/receive`
- Parameter: `barcode`

**Common Issues**:
- App shows "Connection Failed"
  - Check WiFi connectivity
  - Verify server is running
  - Test with browser first

### QR & Barcode Scanner App

**Configuration**:
- Full URL: `http://YOUR_POS_IP:8000/scanner/external/receive`
- Method: POST
- Custom parameter name: `barcode`

**Common Issues**:
- HTTP requests blocked by app
  - Enable "Allow HTTP" in app settings
  - Use HTTPS if available

## Performance Issues

### Slow Response Times

**Causes**:
- Large product database
- Network latency
- Barcode reconstruction overhead

**Solutions**:
- Add database indexes on barcode fields
- Optimize product search queries
- Consider caching frequently scanned items

### Memory Issues

**Symptoms**:
- PHP memory limit errors
- Slow scanner response

**Solutions**:
- Increase PHP memory limit
- Optimize product search logic
- Limit search result size

## Security Considerations

### 1. Network Security
- Use HTTPS when possible
- Restrict access to trusted networks
- Consider VPN for remote access

### 2. Input Validation
- All barcode inputs are validated
- SQL injection protection in place
- Rate limiting recommended for production

### 3. Logging and Monitoring
- All external scanner requests logged
- Failed attempts tracked
- Regular log rotation implemented

## Support Contacts

For additional support:
1. Check Laravel logs for specific error messages
2. Verify network connectivity between devices
3. Test with different mobile apps
4. Review this troubleshooting guide
5. Contact system administrator if issues persist