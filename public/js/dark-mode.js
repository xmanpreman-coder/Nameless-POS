/* ============================================
   DARK MODE TOGGLE - Simple & Non-Invasive
   ============================================ */

class DarkModeToggle {
    constructor() {
        this.STORAGE_KEY = 'darkmode';
        this.DARK_CLASS = 'dark-mode';
        this.init();
    }

    init() {
        this.loadTheme();
        this.createButton();
        this.setupListeners();
    }

    loadTheme() {
        const isDark = localStorage.getItem(this.STORAGE_KEY) === 'true';
        if (isDark) {
            document.body.classList.add(this.DARK_CLASS);
        }
    }

    createButton() {
        const button = document.createElement('button');
        button.className = 'dark-mode-toggle';
        button.setAttribute('aria-label', 'Toggle dark mode');
        button.setAttribute('title', 'Toggle Dark Mode (Ctrl+Shift+D)');
        button.innerHTML = this.isDark() ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon"></i>';
        button.addEventListener('click', () => this.toggle());
        document.body.appendChild(button);
        this.button = button;
    }

    setupListeners() {
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                this.toggle();
            }
        });
    }

    toggle() {
        const isDark = this.isDark();
        if (isDark) {
            document.body.classList.remove(this.DARK_CLASS);
            localStorage.setItem(this.STORAGE_KEY, 'false');
            this.button.innerHTML = '<i class="bi bi-moon"></i>';
        } else {
            document.body.classList.add(this.DARK_CLASS);
            localStorage.setItem(this.STORAGE_KEY, 'true');
            this.button.innerHTML = '<i class="bi bi-sun"></i>';
        }
    }

    isDark() {
        return document.body.classList.contains(this.DARK_CLASS);
    }
}

// Restore theme IMMEDIATELY before DOM renders (prevent white flash on page load)
(function() {
    const isDark = localStorage.getItem('darkmode') === 'true';
    if (isDark) {
        document.documentElement.style.background = '#1a1a2e';
        document.documentElement.style.color = '#eaeaea';
        document.body.classList.add('dark-mode');
    }
})();

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.darkModeToggle = new DarkModeToggle();
    });
} else {
    window.darkModeToggle = new DarkModeToggle();
}
