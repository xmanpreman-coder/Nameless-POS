# Language Implementation Guide

## Overview
Indonesian language support has been implemented in the application. The system now supports switching between English (EN) and Indonesian (ID).

## Features
- **Language Switcher**: A dropdown menu in the top-right header allows users to toggle between "EN" and "ID".
- **Session Persistence**: The selected language is stored in the session and persists across page reloads.
- **Translation Files**:
    - `resources/lang/id.json`: Contains simple key-value translations for application strings.
    - `resources/lang/id/`: Contains validation and auth message translations.

## How to Translate Additional Pages
To translate other pages in the application, follow these steps:

1.  **Identify Localizable Text**: Find text in your blade view files that needs translation.
    ```html
    <!-- BEFORE -->
    <label>Product Name</label>
    ```

2.  **Wrap in Translation Function**: Use the `__()` helper function.
    ```html
    <!-- AFTER -->
    <label>{{ __('Product Name') }}</label>
    ```

3.  **Add Translation to JSON**: Open `resources/lang/id.json` and add the mapping if it doesn't exist.
    ```json
    {
        "Product Name": "Nama Produk"
    }
    ```

## Files Modified/Created
- `app/Http/Middleware/SetLocale.php` (New)
- `app/Http/Controllers/LanguageController.php` (New)
- `app/Http/Kernel.php` (Registered Middleware)
- `routes/web.php` (Added `/lang/{locale}` route)
- `resources/lang/id.json` (New Translation Map)
- `resources/views/layouts/header.blade.php` (Added Switcher UI)
- `Modules/Product/Resources/views/products/create.blade.php` (Updated for Demo)
