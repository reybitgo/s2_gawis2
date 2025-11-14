# CoreUI PHP Templating System

This documentation explains the PHP templating system implemented to reduce code redundancy across the CoreUI admin template.

## Overview

The templating system breaks down the HTML structure into reusable PHP components:

- **Header** (`includes/head.php`) - HTML head section with meta tags, CSS includes, and favicons
- **Sidebar** (`includes/sidebar.php`) - Left navigation sidebar
- **Header** (`includes/header.php`) - Top header/topbar with navigation and user menu
- **Footer** (`includes/footer.php`) - Bottom footer section
- **Scripts** (`includes/scripts.php`) - JavaScript includes and initialization

## File Structure

```
coreui/
├── includes/
│   ├── config.php          # Global configuration and helper functions
│   ├── head.php            # HTML head template
│   ├── sidebar.php         # Sidebar navigation template
│   ├── header.php          # Header/topbar template
│   ├── footer.php          # Footer template
│   └── scripts.php         # JavaScript includes template
├── index.php               # Example converted page
├── maps.php               # OpenStreetMap page (renamed from google-maps.php)
└── README_PHP_TEMPLATING.md # This documentation
```

## Usage

### 1. Create a PHP Page

Start each PHP page with the configuration:

```php
<?php
// Page configuration
require_once 'includes/config.php';

$pageTitle = 'Page Title - ' . $config['siteName'];
$pageDescription = 'Page description for meta tags';
$currentPage = 'page-name';
$currentSection = 'section-name'; // For nested pages

// Additional CSS files specific to this page
$additionalCSS = [
    'vendors/some-plugin/css/plugin.css'
];

// Additional JavaScript files specific to this page
$additionalJS = [
    'vendors/some-plugin/js/plugin.js',
    'js/page-specific.js'
];

// Breadcrumbs for navigation
$breadcrumbs = [
    ['title' => 'Section', 'url' => 'section/index.php'],
    ['title' => 'Current Page']
];

// Include the head
include 'includes/head.php';
?>
```

### 2. Include Templates

Use the templates in this order:

```php
<?php include 'includes/sidebar.php'; ?>
    <div class="wrapper d-flex flex-column min-vh-100">
<?php include 'includes/header.php'; ?>
      <div class="body flex-grow-1">
        <!-- Your page content here -->
      </div>
<?php include 'includes/footer.php'; ?>
    </div>
<?php include 'includes/scripts.php'; ?>
```

## Configuration Variables

### Global Variables (set in config.php)

- `$config` - Array with site-wide settings
- `$baseDir` - Automatically calculated relative path to assets
- `$navigation` - Navigation structure array

### Page-Specific Variables

Set these variables before including `head.php`:

| Variable | Type | Description | Example |
|----------|------|-------------|---------|
| `$pageTitle` | string | Page title for `<title>` tag | `'Dashboard - CoreUI'` |
| `$pageDescription` | string | Meta description | `'Admin dashboard page'` |
| `$currentPage` | string | Current page identifier for active nav | `'dashboard'` |
| `$currentSection` | string | Current section for nested navigation | `'forms'` |
| `$additionalCSS` | array | Additional CSS files | `['vendors/plugin.css']` |
| `$additionalJS` | array | Additional JavaScript files | `['js/custom.js']` |
| `$breadcrumbs` | array | Breadcrumb navigation | `[['title' => 'Home', 'url' => 'index.php']]` |
| `$inlineJS` | string | Inline JavaScript code | `'console.log("test");'` |

## Helper Functions

### Available Functions (from config.php)

- `getBaseDir()` - Calculates relative path based on current file location
- `isActivePage($page)` - Checks if current page matches the given page name
- `isActiveSection($section)` - Checks if current section matches the given section name

## Navigation System

The sidebar navigation automatically highlights active items based on:

- `$currentPage` - For individual pages
- `$currentSection` - For section-based navigation

Example:
```php
$currentPage = 'form-control';      // Highlights "Form Control" nav item
$currentSection = 'forms';          // Expands "Forms" section
```

## Asset Path Resolution

The system automatically calculates the correct relative paths for assets based on the current file's location:

- Root level files: `assets/`, `vendors/`, `js/`
- One level deep: `../assets/`, `../vendors/`, `../js/`
- Two levels deep: `../../assets/`, `../../vendors/`, `../../js/`

## Benefits

1. **Reduced Code Duplication** - Common elements are defined once
2. **Consistent Navigation** - Active states handled automatically
3. **Easy Maintenance** - Update navigation in one place
4. **Path Management** - Automatic relative path calculation
5. **Flexible Structure** - Easy to add new sections and pages

## Converting HTML to PHP

To convert an existing HTML file to PHP:

1. Copy the HTML file and rename with `.php` extension
2. Add the PHP configuration at the top
3. Replace the HTML head section with `<?php include 'includes/head.php'; ?>`
4. Replace the sidebar with `<?php include 'includes/sidebar.php'; ?>`
5. Replace the header with `<?php include 'includes/header.php'; ?>`
6. Replace the footer with `<?php include 'includes/footer.php'; ?>`
7. Replace the scripts section with `<?php include 'includes/scripts.php'; ?>`
8. Update asset paths to use `<?= $baseDir ?>` where needed

## Example Page Structure

```php
<?php
require_once 'includes/config.php';
$pageTitle = 'Example Page';
$currentPage = 'example';
include 'includes/head.php';
?>

<?php include 'includes/sidebar.php'; ?>
    <div class="wrapper d-flex flex-column min-vh-100">
<?php include 'includes/header.php'; ?>
      <div class="body flex-grow-1">
        <div class="container-lg h-auto px-4">
          <!-- Your page content -->
          <h1><?= $pageTitle ?></h1>
          <p>This is an example page using the PHP templating system.</p>
        </div>
      </div>
<?php include 'includes/footer.php'; ?>
    </div>
<?php include 'includes/scripts.php'; ?>
```

This templating system significantly reduces code redundancy while maintaining the full functionality and appearance of the original CoreUI template.