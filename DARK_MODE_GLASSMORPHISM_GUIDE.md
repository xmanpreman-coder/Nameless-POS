# 🌓 Dark/Light Mode & Glassmorphism Theme Implementation

## Overview
Implementasi lengkap Dark/Light Mode dengan Glassmorphism UI design untuk Nameless POS.

## Fitur Utama

### 1. **Dark/Light Mode Toggle**
- ✅ Toggle button di pojok kanan bawah (fixed position)
- ✅ Auto-detect system preference (prefers-color-scheme)
- ✅ Persistent storage (localStorage)
- ✅ Keyboard shortcut: `Ctrl+Shift+D` atau `Cmd+Shift+D`
- ✅ Smooth transition antar tema
- ✅ Respons pada perubahan sistem theme

### 2. **Glassmorphism Design**
- ✅ Frosted glass effect dengan backdrop-filter
- ✅ Semi-transparent backgrounds
- ✅ Subtle borders dan shadows
- ✅ Modern, elegant appearance
- ✅ Support untuk semua komponen

### 3. **CSS Variables Based**
Semua warna menggunakan CSS custom properties untuk fleksibilitas maksimal:
```css
:root {
    --primary-color: #6366f1;
    --bg-primary: #ffffff;
    --text-primary: #1f2937;
    /* ... dll */
}

body.dark-mode {
    --primary-color: #818cf8;
    --bg-primary: #1f2937;
    /* ... etc */
}
```

## Instalasi & Setup

### File-File yang Ditambahkan:
1. **`resources/css/theme-switcher.css`** - Stylesheet untuk tema
2. **`resources/js/theme-switcher.js`** - JavaScript untuk toggle

### File-File yang Diupdate:
1. **`resources/views/includes/main-css.blade.php`** - Include CSS
2. **`resources/views/includes/main-js.blade.php`** - Include JS

## Cara Kerja

### CSS Variables Sistem
```css
/* Light Mode (Default) */
:root {
    --primary-color: #6366f1;
    --bg-primary: #ffffff;
    --bg-secondary: #f3f4f6;
    --text-primary: #1f2937;
    /* ... */
}

/* Dark Mode */
body.dark-mode {
    --primary-color: #818cf8;
    --bg-primary: #1f2937;
    --bg-secondary: #111827;
    --text-primary: #f3f4f6;
    /* ... */
}
```

### Glassmorphism Effect
```css
.glass-card {
    background: var(--glass-bg);           /* rgba(255, 255, 255, 0.7) */
    backdrop-filter: var(--glass-blur);    /* blur(10px) */
    border: 1px solid var(--glass-border); /* rgba(255, 255, 255, 0.5) */
}
```

### JavaScript Theme Switcher
```javascript
// Toggle theme
window.themeSwitcher.toggle();

// Set specific theme
window.themeSwitcher.setTheme('dark');

// Get current theme
window.themeSwitcher.getCurrentTheme();

// Check if dark mode
window.themeSwitcher.isDarkMode();

// Listen for theme changes
document.addEventListener('themechange', (e) => {
    console.log('Theme changed to:', e.detail.theme);
});
```

## Komponen yang Terpengaruh

### Sidebar
- ✅ Glassmorphism effect
- ✅ Dynamic text color
- ✅ Responsive hover states

### Cards
- ✅ Dynamic background
- ✅ Gradient headers
- ✅ Smooth shadows

### Forms & Inputs
- ✅ Dark/light backgrounds
- ✅ Focus states dengan primary color
- ✅ Better readability

### Buttons
- ✅ Gradient backgrounds
- ✅ Hover effects
- ✅ Glassmorphism borders

### Tables
- ✅ Dynamic borders
- ✅ Hover row highlighting
- ✅ Better contrast

### Alerts
- ✅ Gradient backgrounds
- ✅ Colored left borders
- ✅ Color-coded messages

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl+Shift+D` | Toggle Dark/Light Mode |
| `Cmd+Shift+D` | Toggle Dark/Light Mode (Mac) |

## Customization

### Mengubah Primary Color
Edit di `theme-switcher.css`:
```css
:root {
    --primary-color: #6366f1;  /* Light mode */
}

body.dark-mode {
    --primary-color: #818cf8;  /* Dark mode */
}
```

### Mengubah Glassmorphism Blur
```css
:root {
    --glass-blur: blur(10px);  /* Default */
    --glass-blur: blur(15px);  /* More blur */
    --glass-blur: blur(5px);   /* Less blur */
}
```

### Menambah Komponen ke Tema
Tambahkan class atau selector baru ke CSS:
```css
.custom-element {
    background-color: var(--bg-primary);
    color: var(--text-primary);
    border-color: var(--border-color);
}
```

## Browser Support

| Browser | Support | Notes |
|---------|---------|-------|
| Chrome | ✅ | Full support |
| Firefox | ✅ | Full support |
| Safari | ✅ | Full support |
| Edge | ✅ | Full support |
| IE 11 | ❌ | CSS variables tidak support |

## Performance

- ✅ CSS variables (no runtime calculations)
- ✅ Hardware accelerated transitions
- ✅ Minimal JavaScript
- ✅ Efficient storage (localStorage)
- ✅ No performance impact

## Accessibility

- ✅ Respects `prefers-color-scheme`
- ✅ Keyboard accessible
- ✅ ARIA labels pada toggle button
- ✅ Sufficient contrast ratios
- ✅ Reduced motion support

## Troubleshooting

### Theme tidak berubah
1. Clear localStorage: `localStorage.clear()`
2. Hard refresh: `Ctrl+Shift+R`
3. Check console untuk errors

### Toggle button tidak muncul
1. Pastikan `theme-switcher.js` sudah loaded
2. Check browser console
3. Ensure DOM ready sebelum script run

### Warna tidak benar di dark mode
1. Check CSS override di component styles
2. Ensure class names menggunakan variables
3. Priority: CSS variables > inline styles > defaults

## Development

### Testing Theme Changes
```javascript
// Toggle dari console
window.themeSwitcher.toggle();

// Check current theme
window.themeSwitcher.getCurrentTheme();

// Force dark mode
window.themeSwitcher.setTheme('dark');

// Listen untuk changes
document.addEventListener('themechange', (e) => {
    console.log('Changed to:', e.detail.theme);
});
```

## Future Enhancements

- [ ] Auto-switch based on time of day
- [ ] Multiple theme presets (Purple, Blue, Green, etc)
- [ ] Custom color picker
- [ ] Theme sync across tabs
- [ ] Animation preferences
- [ ] Customizable glassmorphism intensity

## Support

Untuk questions atau issues, check:
1. Browser console untuk errors
2. CSS cascade dan specificity
3. CSS variables inheritance

---

**Last Updated:** November 21, 2025
**Version:** 1.0.0
