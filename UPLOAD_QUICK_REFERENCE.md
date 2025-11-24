# 🚀 Upload System - Quick Reference

## 📸 At a Glance

```
User Avatars         │ Product Images
━━━━━━━━━━━━━━━━━━━━┿━━━━━━━━━━━━━━━━━━━━━
Max Input: 2MB       │ Max Input: 5MB
Output: 200x200px    │ Output: 500x500px
Quality: 85% JPEG    │ Quality: 85% JPEG
Size: 50-150KB       │ Size: 150-300KB
Folder: avatars/     │ Folder: products/
```

---

## 🔗 Storage Paths

### Database (Portable)
```sql
users.avatar = "avatars/1762866590_1234.jpg"
media.file_name = "1762866600_5678.jpg"
```

### File System
```
storage/app/public/avatars/    ← User photos
storage/app/public/products/   ← Product photos
public/storage → ../storage/app/public  [Symlink]
```

### URLs
```
http://localhost:8000/storage/avatars/filename.jpg
http://localhost:8000/storage/products/filename.jpg
```

---

## 🎯 Code Usage

### Upload Avatar
```php
use App\Services\ImageProcessor;

$processor = new ImageProcessor();
$path = $processor->processImage(
    file: $request->file('avatar'),
    folder: 'avatars',
    width: 200,
    height: 200
);
$user->update(['avatar' => $path]);
```

### Display Avatar
```blade
<img src="{{ asset('storage/' . $user->avatar) }}">
```

### Delete Avatar
```php
$processor->deleteImage($user->avatar, 'public');
```

---

## 📋 Validation Rules

```php
'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
```

---

## ⚙️ Configuration

**.env**
```
DB_DATABASE=database/database.sqlite
```

**config/database.php**
```php
'database' => base_path(env('DB_DATABASE')),
```

**config/filesystems.php**
```php
'public' => [
    'path' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
]
```

---

## 🧪 Testing

```bash
# Create user with avatar
POST /users
  - Upload file < 2MB

# Update user avatar  
PUT /users/{id}
  - Upload file < 2MB
  - Old avatar should be deleted

# Create product with images
POST /products
  - Upload 1-3 images < 5MB each

# Check files
ls -la storage/app/public/avatars/
ls -la storage/app/public/products/
```

---

## 🐛 Debugging

```bash
# Check file exists
ls -la storage/app/public/avatars/filename.jpg

# Check database path
php artisan tinker
>>> \App\Models\User::first()->avatar

# Check storage link
ls -la public/storage

# Check image info
>>> $processor->getImageInfo('avatars/filename.jpg')

# View logs
tail -f storage/logs/laravel.log | grep -i image
```

---

## ✅ Checklist

- [ ] `.env` has relative path: `DB_DATABASE=database/database.sqlite`
- [ ] `config/database.php` uses `base_path()`
- [ ] Storage link exists: `php artisan storage:link`
- [ ] Permissions set: `chmod 755 storage/app/public/`
- [ ] Directories exist: `avatars/`, `products/`
- [ ] Test upload works
- [ ] Images display correctly
- [ ] Cache cleared: `php artisan optimize:clear`

---

## 🚨 Common Issues

| Issue | Fix |
|-------|-----|
| Avatar not showing | Check storage link: `ls public/storage` |
| Upload fails | Check file size & type |
| Image blurry | Normal with 85% compression |
| Path not portable | Use relative paths, not absolute |
| File not found | Check symlink & permissions |

---

## 📞 Services

**ImageProcessor** - `app/Services/ImageProcessor.php`

```php
// Main method
processImage(file, folder, width, height, maxSizeKb, quality, disk)

// Helper methods
deleteImage(path, disk)
getImageInfo(path, disk)
```

---

## 🔐 Security

✅ MIME type validation  
✅ Extension whitelist: jpg, jpeg, png, gif, webp  
✅ File size limits: 2MB avatars, 5MB products  
✅ Path traversal protection  
✅ Unique filename generation  

---

## 📊 Performance

| Operation | Time | Output |
|-----------|------|--------|
| Avatar | 200-500ms | 80-150KB |
| Product | 500-1000ms | 150-300KB |
| Batch 10 | 5-15s | ~2.5MB |

---

## 📚 Full Documentation

- `UPLOAD_SYSTEM_DOCUMENTATION.md` - Complete reference
- `UPLOAD_TESTING_GUIDE.md` - Test procedures
- `UPLOAD_SYSTEM_FIX_REPORT.md` - What was fixed

---

**Version:** 1.0  
**Date:** November 20, 2025
