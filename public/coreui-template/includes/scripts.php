    <!-- CoreUI and necessary plugins-->
    <script src="<?= $baseDir ?>vendors/%40coreui/coreui-pro/js/coreui.bundle.min.js"></script>
    <script src="<?= $baseDir ?>vendors/simplebar/js/simplebar.min.js"></script>
    <script src="<?= $baseDir ?>vendors/i18next/js/i18next.min.js"></script>
    <script src="<?= $baseDir ?>vendors/i18next-http-backend/js/i18nextHttpBackend.js"></script>
    <script src="<?= $baseDir ?>vendors/i18next-browser-languagedetector/js/i18nextBrowserLanguageDetector.js"></script>
    <script src="<?= $baseDir ?>js/i18next.js"></script>
    <script src="<?= $baseDir ?>js/logo-switcher.js"></script>
    <script>
      const header = document.querySelector("header.header");

      document.addEventListener("scroll", () => {
        if (header) {
          header.classList.toggle(
            "shadow-sm",
            document.documentElement.scrollTop > 0
          );
        }
      });

      // Initialize CoreUI theme system
      document.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing CoreUI theme system...');

        // Get stored theme or default to light
        const storedTheme = localStorage.getItem('coreui-theme') || 'light';
        console.log('Stored theme:', storedTheme);

        // Set initial theme
        document.documentElement.setAttribute('data-coreui-theme', storedTheme);

        // Initialize theme switcher buttons
        const themeButtons = document.querySelectorAll('[data-coreui-theme-value]');
        console.log('Found theme buttons:', themeButtons.length);

        themeButtons.forEach(button => {
          button.addEventListener('click', function() {
            const theme = this.getAttribute('data-coreui-theme-value');
            console.log('Theme button clicked:', theme);

            // Set theme attribute
            document.documentElement.setAttribute('data-coreui-theme', theme);

            // Store in localStorage
            localStorage.setItem('coreui-theme', theme);

            // Dispatch custom event for theme change
            const event = new CustomEvent('ColorSchemeChange', {
              detail: { theme: theme }
            });
            document.documentElement.dispatchEvent(event);

            console.log('Theme changed to:', theme);
          });
        });

        // Update active button state
        function updateActiveThemeButton(theme) {
          themeButtons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-coreui-theme-value') === theme) {
              btn.classList.add('active');
            }
          });
        }

        // Set initial active state
        updateActiveThemeButton(storedTheme);

        // Listen for theme changes to update button states
        document.documentElement.addEventListener('ColorSchemeChange', (e) => {
          updateActiveThemeButton(e.detail.theme);
        });

        console.log('CoreUI theme system initialized');

        // Initialize sidebar to ensure it starts in full width mode
        // CoreUI's unfoldable toggle will handle the narrow mode with hover expand
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
          // Remove any narrow classes that might be present
          sidebar.classList.remove('sidebar-narrow');
          sidebar.classList.remove('sidebar-narrow-unfoldable');
          console.log('Sidebar initialized in full width mode for CoreUI unfoldable toggle');
        }
      });
    </script>

    <!-- Additional JavaScript files -->
    <?php if (!empty($additionalJS)): ?>
      <?php foreach ($additionalJS as $js): ?>
        <script src="<?= $baseDir ?><?= $js ?>"></script>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- Page-specific inline JavaScript -->
    <?php if (!empty($inlineJS)): ?>
      <script><?= $inlineJS ?></script>
    <?php endif; ?>

    <script
      defer=""
      src="<?= $baseDir ?>js/beacon.min.js"
      integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ=="
      data-cf-beacon='{"version":"2024.11.0","token":"496f8c1a159448ef82d6c94971e63824","server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}'
      crossorigin="anonymous"
    ></script>
  </body>
</html>