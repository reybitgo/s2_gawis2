/**
 * --------------------------------------------------------------------------
 * Logo Switcher for Dark/Light Theme
 * --------------------------------------------------------------------------
 */

const LogoSwitcher = {
  init() {
    console.log('LogoSwitcher: Initializing...');

    // Set initial logo based on current theme
    this.updateLogo();

    // Listen for theme changes (CoreUI event)
    document.documentElement.addEventListener('ColorSchemeChange', () => {
      console.log('LogoSwitcher: Theme change detected');
      this.updateLogo();
    });

    // Also listen for attribute changes on html element
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === 'attributes' && mutation.attributeName === 'data-coreui-theme') {
          console.log('LogoSwitcher: Theme attribute changed to:', document.documentElement.getAttribute('data-coreui-theme'));
          this.updateLogo();
        }
      });
    });

    observer.observe(document.documentElement, {
      attributes: true,
      attributeFilter: ['data-coreui-theme']
    });
  },

  updateLogo() {
    const logoElements = document.querySelectorAll('.sidebar-brand-full, .sidebar-brand img, img[src*="gawis_logo"]');
    const isDark = this.isDarkTheme();

    console.log('LogoSwitcher: Updating logo, isDark:', isDark);
    console.log('LogoSwitcher: Found logo elements:', logoElements.length);

    logoElements.forEach((logo, index) => {
      console.log('LogoSwitcher: Processing logo element', index, 'current src:', logo.src);

      if (isDark) {
        // Switch to light logo for dark theme
        if (logo.src.includes('gawis_logo.png') && !logo.src.includes('gawis_logo_light.png')) {
          logo.src = logo.src.replace('gawis_logo.png', 'gawis_logo_light.png');
          console.log('LogoSwitcher: Switched to light logo for dark theme:', logo.src);
        }
      } else {
        // Switch to dark logo for light theme (this is correct - dark logo shows on light background)
        if (logo.src.includes('gawis_logo_light.png')) {
          logo.src = logo.src.replace('gawis_logo_light.png', 'gawis_logo.png');
          console.log('LogoSwitcher: Switched to dark logo for light theme:', logo.src);
        }
        // IMPORTANT: Ensure we start with dark logo on light theme
        else if (logo.src.includes('gawis_logo.png') && !logo.src.includes('gawis_logo_light.png')) {
          // Keep dark logo for light theme - this is correct
          console.log('LogoSwitcher: Dark logo correctly showing for light theme:', logo.src);
        }
      }
    });
  },

  isDarkTheme() {
    // Check if CoreUI dark theme is active
    const htmlElement = document.documentElement;

    // Check data-coreui-theme attribute
    const theme = htmlElement.getAttribute('data-coreui-theme');
    console.log('LogoSwitcher: Current theme attribute:', theme);

    if (theme === 'dark') return true;
    if (theme === 'light') return false;

    // Check for dark class (alternative method)
    if (htmlElement.classList.contains('dark')) return true;

    // If theme is 'auto' or not set, check system preference
    if (theme === 'auto' || !theme) {
      const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      console.log('LogoSwitcher: System prefers dark:', prefersDark);
      return prefersDark;
    }

    return false;
  }
};

// Enhanced initialization
function initLogoSwitcher() {
  console.log('LogoSwitcher: Starting initialization...');
  LogoSwitcher.init();
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initLogoSwitcher);
} else {
  initLogoSwitcher();
}

// Also initialize after a short delay to ensure CoreUI theme system is loaded
setTimeout(() => {
  console.log('LogoSwitcher: Delayed initialization for theme system...');
  LogoSwitcher.init();
}, 1500);

// Additional initialization after theme system setup (longer delay)
setTimeout(() => {
  console.log('LogoSwitcher: Final theme initialization check...');
  LogoSwitcher.updateLogo();
}, 2000);

// Make LogoSwitcher globally available for debugging
window.LogoSwitcher = LogoSwitcher;