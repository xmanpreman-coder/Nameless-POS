/* ============================================
   THEME SWITCHER JAVASCRIPT
   Dark/Light Mode Toggle
   ============================================ */

class ThemeSwitcher {
    constructor() {
        this.STORAGE_KEY = 'theme-mode';
        this.DARK_CLASS = 'dark-mode';
        this.htmlElement = document.documentElement;
        this.bodyElement = document.body;
        
        this.init();
    }

    /**
     * Initialize theme switcher
     */
    init() {
        // Load saved theme or detect system preference
        this.loadTheme();
        
        // Create and setup toggle button
        this.createToggleButton();
        
        // Listen for system theme changes
        this.listenSystemTheme();
        
        // Setup keyboard shortcut (Ctrl+Shift+D for dark mode)
        this.setupKeyboardShortcut();
    }

    /**
     * Load theme from localStorage or system preference
     */
    loadTheme() {
        const savedTheme = localStorage.getItem(this.STORAGE_KEY);
        
        if (savedTheme) {
            this.setTheme(savedTheme);
        } else {
            // Check system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.setTheme(prefersDark ? 'dark' : 'light');
        }
    }

    /**
     * Set theme and save to localStorage
     */
    setTheme(theme) {
        const isDark = theme === 'dark';
        
        if (isDark) {
            this.bodyElement.classList.add(this.DARK_CLASS);
            this.htmlElement.setAttribute('data-theme', 'dark');
        } else {
            this.bodyElement.classList.remove(this.DARK_CLASS);
            this.htmlElement.setAttribute('data-theme', 'light');
        }
        
        localStorage.setItem(this.STORAGE_KEY, theme);
        this.updateToggleButton();
        this.dispatchEvent(theme);
    }

    /**
     * Toggle between light and dark mode
     */
    toggle() {
        const isDark = this.isDarkMode();
        this.setTheme(isDark ? 'light' : 'dark');
    }

    /**
     * Check if dark mode is active
     */
    isDarkMode() {
        return this.bodyElement.classList.contains(this.DARK_CLASS);
    }

    /**
     * Create toggle button
     */
    createToggleButton() {
        // Check if button already exists
        if (document.getElementById('theme-toggle-btn')) {
            return;
        }

        const button = document.createElement('button');
        button.id = 'theme-toggle-btn';
        button.className = 'theme-toggle-btn';
        button.type = 'button';
        button.setAttribute('aria-label', 'Toggle dark/light mode');
        button.setAttribute('title', 'Toggle Theme (Ctrl+Shift+D)');
        button.innerHTML = this.getIcon();

        button.addEventListener('click', () => this.toggle());
        document.body.appendChild(button);

        this.toggleButton = button;
    }

    /**
     * Update button icon based on current theme
     */
    updateToggleButton() {
        if (this.toggleButton) {
            this.toggleButton.innerHTML = this.getIcon();
        }
    }

    /**
     * Get icon based on current theme
     */
    getIcon() {
        return this.isDarkMode() 
            ? '<i class="bi bi-sun"></i>' 
            : '<i class="bi bi-moon"></i>';
    }

    /**
     * Listen for system theme changes
     */
    listenSystemTheme() {
        const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
        
        darkModeQuery.addEventListener('change', (e) => {
            // Only auto-switch if user hasn't manually set a preference
            if (!localStorage.getItem(this.STORAGE_KEY)) {
                this.setTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    /**
     * Setup keyboard shortcut
     */
    setupKeyboardShortcut() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+Shift+D or Cmd+Shift+D
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                this.toggle();
            }
        });
    }

    /**
     * Dispatch custom event when theme changes
     */
    dispatchEvent(theme) {
        const event = new CustomEvent('themechange', {
            detail: { theme }
        });
        document.dispatchEvent(event);
    }

    /**
     * Get current theme
     */
    getCurrentTheme() {
        return this.isDarkMode() ? 'dark' : 'light';
    }
}

// Initialize theme switcher when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.themeSwitcher = new ThemeSwitcher();
    });
} else {
    window.themeSwitcher = new ThemeSwitcher();
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeSwitcher;
}
