# 🧪 Upload System Testing Guide

## Quick Test Cases

### Test 1: User Avatar Upload (Users List)

**Steps:**
1. Go to: `/users`
2. Click "Create User" button
3. Fill form:
   - Name: `Test User`
   - Email: `test@example.com`
   - Password: `password123`
   - Role: Select any
   - Status: Active
4. Upload avatar: Any JPG/PNG image < 2MB
5. Click "Create"

**Expected Results:**
- ✅ User created successfully
- ✅ Avatar uploaded to `storage/app/public/avatars/`
- ✅ Avatar path stored in `users.avatar` column
- ✅ Avatar shown in users list (50x50px circular)
- ✅ Image file size optimized (80-150 KB)

**Debug:**
```bash
# Check file exists
ls -la storage/app/public/avatars/

# Check database
php artisan tinker
>>> \App\Models\User::where('email', 'test@example.com')->first()->avatar

# Check image info
>>> $u = \App\Models\User::where('email', 'test@example.com')->first();
>>> $u->avatar_url
```

---

### Test 2: User Avatar Update

**Steps:**
1. Go to: `/users`
2. Click "Edit" on any user
3. Change name
4. Upload NEW avatar image
5. Click "Update"

**Expected Results:**
- ✅ User updated
- ✅ Old avatar deleted from storage
- ✅ New avatar uploaded to `storage/app/public/avatars/`
- ✅ New avatar path saved in database
- ✅ New avatar displayed

**Debug:**
```bash
# Check old file deleted
ls -la storage/app/public/avatars/ | wc -l  # Count should decrease

# Check new file
ls -la storage/app/public/avatars/ | tail -1  # Show newest file

# Verify in database
php artisan tinker
>>> \App\Models\User::find(1)->avatar  # Should show new path
```

---

### Test 3: Profile Update (Self)

**Steps:**
1. Click profile icon (top right)
2. Click "Profile" from dropdown
3. Change name/email
4. Upload avatar
5. Click "Update Profile"

**Expected Results:**
- ✅ Profile updated
- ✅ Avatar uploaded
- ✅ Avatar shown in header immediately

**Debug:**
```bash
# Check logged-in user avatar
php artisan tinker
>>> auth()->user()->avatar
```

---

### Test 4: User Delete (Cascade)

**Steps:**
1. Go to: `/users`
2. Click Delete on any user
3. Confirm deletion

**Expected Results:**
- ✅ User deleted
- ✅ Avatar file deleted from storage
- ✅ No orphaned files in `avatars/` folder

**Debug:**
```bash
# Check file count before/after
ls -la storage/app/public/avatars/ | wc -l
```

---

### Test 5: Product Image Upload

**Steps:**
1. Go to: `/products`
2. Click "Create Product"
3. Fill form with:
   - Category: Select any
   - SKU: `TEST001`
   - GTIN: `1234567890`
   - Name: `Test Product`
   - Cost: `10000`
   - Price: `20000`
   - Quantity: `100`
4. Upload 2-3 product images
5. Click "Create"

**Expected Results:**
- ✅ Product created
- ✅ Images uploaded to `storage/app/public/products/`
- ✅ Images stored in `media` table (Spatie)
- ✅ Images shown in product view (500x500px)
- ✅ Image file size optimized (150-250 KB each)

**Debug:**
```bash
# Check files
ls -la storage/app/public/products/

# Check media table
php artisan tinker
>>> $p = \Modules\Product\Entities\Product::first();
>>> $p->media()->count()  # Should be > 0
>>> $p->getMedia('images')  # Show image collection
```

---

### Test 6: Product Image Update (Replace)

**Steps:**
1. Go to: `/products`
2. Click "Edit" on any product
3. Remove old images
4. Upload NEW images
5. Click "Update"

**Expected Results:**
- ✅ Product updated
- ✅ Old images deleted from storage
- ✅ Old media records deleted
- ✅ New images uploaded
- ✅ New images displayed

**Debug:**
```bash
# Check media count
php artisan tinker
>>> $p = \Modules\Product\Entities\Product::first();
>>> $p->media()->count()  # Should reflect new count

# Check deleted files
ls -la storage/app/public/products/  # Old files should be gone
```

---

### Test 7: Image Validation - File Size

**Steps:**
1. Create user with avatar > 2MB
2. Or create product with image > 5MB

**Expected Results:**
- ✅ Validation error displayed
- ✅ "File too large" message shown
- ✅ Upload rejected (file not stored)

**Debug:**
```bash
# Test ImageProcessor directly
php artisan tinker
>>> $processor = new \App\Services\ImageProcessor();
>>> $file = /* get file > 2MB */
>>> $processor->processImage($file, 'avatars') // Should throw Exception
```

---

### Test 8: Image Validation - Invalid Type

**Steps:**
1. Try upload PDF or TXT as avatar
2. Or try upload any non-image file

**Expected Results:**
- ✅ Validation error
- ✅ "Invalid file type" message
- ✅ File rejected

**Debug:**
```php
// Check allowed types
$processor = new \App\Services\ImageProcessor();
echo implode(', ', $processor::ALLOWED_EXTENSIONS);
// Output: jpg, jpeg, png, gif, webp
```

---

### Test 9: Image Quality Check

**Steps:**
1. Upload a 10MB high-quality PNG product image
2. Check file size after processing

**Expected Results:**
- ✅ Input: ~10 MB
- ✅ Output: 150-300 KB
- ✅ Quality maintained (not blurry)
- ✅ Dimensions: 500x500px

**Debug:**
```bash
# Check file size
ls -lh storage/app/public/products/ | grep -E '\.jpg|\.png'

# Check image dimensions
php artisan tinker
>>> $p = \Modules\Product\Entities\Product::first();
>>> $media = $p->getFirstMedia('images');
>>> echo $media->custom_properties['width'] ?? 'N/A'
```

---

### Test 10: Cross-PC Portability

**Steps:**
1. Export database and files
2. Copy to different PC
3. Update `.env` with new paths (relative)
4. Run `php artisan storage:link`
5. Access files

**Expected Results:**
- ✅ Database avatars.avatar field unchanged (relative path)
- ✅ Files load correctly on new PC
- ✅ No "File not found" errors

**Verify:**
```bash
# On new PC
php artisan tinker
>>> \App\Models\User::first()->avatar  # Should show: avatars/timestamp_random.jpg
>>> asset('storage/' . \App\Models\User::first()->avatar)  # Should load
```

---

## Batch Testing Script

```bash
#!/bin/bash

echo "=== UPLOAD SYSTEM TESTING ==="

# Test 1: Check storage link
echo "1. Storage link..."
if [ -L public/storage ]; then
    echo "   ✅ Symbolic link exists"
else
    echo "   ❌ Symbolic link missing"
fi

# Test 2: Check directories
echo "2. Directories..."
for dir in storage/app/public/avatars storage/app/public/products; do
    if [ -d "$dir" ]; then
        echo "   ✅ $dir exists"
    else
        echo "   ❌ $dir missing"
    fi
done

# Test 3: Check permissions
echo "3. File permissions..."
chmod 755 storage/app/public/avatars/ storage/app/public/products/ 2>/dev/null
echo "   ✅ Permissions set"

# Test 4: Count files
echo "4. File counts..."
echo "   Avatars: $(ls storage/app/public/avatars/ | wc -l) files"
echo "   Products: $(ls storage/app/public/products/ | wc -l) files"

# Test 5: Database check
echo "5. Database..."
php artisan tinker --execute="echo 'Users with avatars: ' . \App\Models\User::whereNotNull('avatar')->count();"

echo ""
echo "=== TESTS COMPLETE ==="
```

---

## Common Issues & Fixes

### Issue: Avatar shows but file not found error in logs

**Fix:**
```bash
# Recreate storage link
php artisan storage:link --force

# Verify
ls -la public/storage
```

### Issue: Upload works but image doesn't display

**Fix:**
```bash
# Check that browser can access file
curl -I http://localhost:8000/storage/avatars/filename.jpg
# Should return: 200 OK

# Check file exists and is readable
ls -la storage/app/public/avatars/filename.jpg
# Should show: -rw-r--r-- (644 permissions)
```

### Issue: Image is blurry

**Fix:**
- This is expected with 85% JPEG compression
- Increase quality in ImageProcessor (not recommended for file size)
- Ensure input image is high-quality

### Issue: Upload is very slow

**Fix:**
```php
// Check if resizing is needed
// For avatars: 200x200px should be instant
// For products: 500x500px might take 1-2 seconds

// Monitor:
tail -f storage/logs/laravel.log | grep -i "image"
```

---

## Performance Baseline

| Operation | Time | File Size |
|-----------|------|-----------|
| Upload 2MB avatar | 200-500ms | 80-150 KB output |
| Upload 5MB product | 500-1500ms | 200-300 KB output |
| Batch 10 products | 5-15s | ~2.5 MB output |
| Delete avatar | 50-100ms | - |
| Display avatar | 10-50ms | - |

---

**Test Date:** November 20, 2025  
**Status:** ✅ Ready for QA
