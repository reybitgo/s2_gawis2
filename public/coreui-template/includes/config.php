<?php
/**
 * CoreUI Template Configuration
 * Global configuration for PHP templating system
 */

// Function to calculate base directory based on current file location
function getBaseDir() {
    $currentPath = $_SERVER['SCRIPT_NAME'];

    // Extract the path within the coreui-template directory
    // Remove everything before /coreui-template/
    $templatePos = strpos($currentPath, '/coreui-template/');
    if ($templatePos !== false) {
        // Get the path relative to coreui-template directory
        $relativePath = substr($currentPath, $templatePos + strlen('/coreui-template/'));

        // Count directory separators to determine depth
        $pathParts = explode('/', trim($relativePath, '/'));
        // Remove the filename (last element) to get directory depth
        array_pop($pathParts);

        // Filter out empty parts
        $pathParts = array_filter($pathParts, function($part) {
            return !empty($part);
        });
        $depth = count($pathParts);

        // For root level files (like index.php), depth should be 0, so return current directory
        if ($depth <= 0) {
            return './';
        }

        // For subdirectory files, return appropriate number of ../
        return str_repeat('../', $depth);
    }

    // Fallback to current directory
    return './';
}

// Function to determine if current page is active
function isActivePage($page) {
    $currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
    return $currentPage === $page;
}

// Function to determine if current section is active
function isActiveSection($section) {
    $currentPath = $_SERVER['SCRIPT_NAME'];
    return strpos($currentPath, "/$section/") !== false;
}

// Global configuration
$config = [
    'siteName' => 'CoreUI Bootstrap Admin Template',
    'version' => 'v5.6.0',
    'author' => 'Łukasz Holeczek',
    'description' => 'CoreUI - Bootstrap Admin Template',
    'keywords' => 'Bootstrap,Admin,Template,SCSS,HTML,RWD,Dashboard'
];

// Calculate base directory for current page
$baseDir = getBaseDir();

// Page-specific variables (will be overridden in individual pages)
$pageTitle = $config['siteName'];
$pageDescription = $config['description'];
$additionalCSS = [];
$additionalJS = [];
$breadcrumbs = [];
$currentSection = '';
$currentPage = '';

// Navigation structure
$navigation = [
    'dashboard' => [
        'title' => 'Dashboard',
        'url' => 'index.php',
        'icon' => 'cil-speedometer'
    ],
    'theme' => [
        'title' => 'Theme',
        'type' => 'group',
        'items' => [
            'colors' => ['title' => 'Colors', 'url' => 'colors.php', 'icon' => 'cil-drop'],
            'typography' => ['title' => 'Typography', 'url' => 'typography.php', 'icon' => 'cil-pencil']
        ]
    ],
    'components' => [
        'title' => 'Components',
        'type' => 'group',
        'items' => [
            'base' => [
                'title' => 'Base',
                'type' => 'dropdown',
                'icon' => 'cil-puzzle',
                'items' => [
                    'accordion' => ['title' => 'Accordion', 'url' => 'base/accordion.php'],
                    'breadcrumb' => ['title' => 'Breadcrumb', 'url' => 'base/breadcrumb.php'],
                    'cards' => ['title' => 'Cards', 'url' => 'base/cards.php'],
                    'carousel' => ['title' => 'Carousel', 'url' => 'base/carousel.php'],
                    'collapse' => ['title' => 'Collapse', 'url' => 'base/collapse.php'],
                    'list-group' => ['title' => 'List Group', 'url' => 'base/list-group.php'],
                    'navs-tabs' => ['title' => 'Navs & Tabs', 'url' => 'base/navs-tabs.php'],
                    'pagination' => ['title' => 'Pagination', 'url' => 'base/pagination.php'],
                    'placeholders' => ['title' => 'Placeholders', 'url' => 'base/placeholders.php'],
                    'popovers' => ['title' => 'Popovers', 'url' => 'base/popovers.php'],
                    'progress' => ['title' => 'Progress', 'url' => 'base/progress.php'],
                    'spinners' => ['title' => 'Spinners', 'url' => 'base/spinners.php'],
                    'tables' => ['title' => 'Tables', 'url' => 'base/tables.php'],
                    'tooltips' => ['title' => 'Tooltips', 'url' => 'base/tooltips.php']
                ]
            ],
            'buttons' => [
                'title' => 'Buttons',
                'type' => 'dropdown',
                'icon' => 'cil-cursor',
                'items' => [
                    'buttons' => ['title' => 'Buttons', 'url' => 'buttons/buttons.php'],
                    'button-group' => ['title' => 'Button Group', 'url' => 'buttons/button-group.php'],
                    'dropdowns' => ['title' => 'Dropdowns', 'url' => 'buttons/dropdowns.php'],
                    'loading-buttons' => ['title' => 'Loading Buttons', 'url' => 'buttons/loading-buttons.php', 'badge' => 'PRO']
                ]
            ],
            'forms' => [
                'title' => 'Forms',
                'type' => 'dropdown',
                'icon' => 'cil-notes',
                'items' => [
                    'form-control' => ['title' => 'Form Control', 'url' => 'forms/form-control.php'],
                    'select' => ['title' => 'Select', 'url' => 'forms/select.php'],
                    'checks-radios' => ['title' => 'Checks & Radios', 'url' => 'forms/checks-radios.php'],
                    'range' => ['title' => 'Range', 'url' => 'forms/range.php'],
                    'input-group' => ['title' => 'Input Group', 'url' => 'forms/input-group.php'],
                    'floating-labels' => ['title' => 'Floating Labels', 'url' => 'forms/floating-labels.php'],
                    'layout' => ['title' => 'Layout', 'url' => 'forms/layout.php'],
                    'validation' => ['title' => 'Validation', 'url' => 'forms/validation.php']
                ]
            ]
        ]
    ],
    'widgets' => [
        'title' => 'Widgets',
        'url' => 'widgets.php',
        'icon' => 'cil-calculator'
    ],
    'charts' => [
        'title' => 'Charts',
        'url' => 'charts.php',
        'icon' => 'cil-chart-pie'
    ],
    'icons' => [
        'title' => 'Icons',
        'type' => 'dropdown',
        'icon' => 'cil-star',
        'items' => [
            'coreui-icons-free' => ['title' => 'CoreUI Icons', 'url' => 'icons/coreui-icons-free.php', 'badge' => 'Free'],
            'coreui-icons-brand' => ['title' => 'CoreUI Icons - Brand', 'url' => 'icons/coreui-icons-brand.php'],
            'coreui-icons-flag' => ['title' => 'CoreUI Icons - Flag', 'url' => 'icons/coreui-icons-flag.php']
        ]
    ],
    'notifications' => [
        'title' => 'Notifications',
        'type' => 'dropdown',
        'icon' => 'cil-bell',
        'items' => [
            'alerts' => ['title' => 'Alerts', 'url' => 'notifications/alerts.php'],
            'badge' => ['title' => 'Badge', 'url' => 'notifications/badge.php'],
            'modals' => ['title' => 'Modals', 'url' => 'notifications/modals.php'],
            'toasts' => ['title' => 'Toasts', 'url' => 'notifications/toasts.php']
        ]
    ],
    'extras' => [
        'title' => 'Extras',
        'type' => 'group',
        'items' => [
            'calendar' => ['title' => 'Calendar', 'url' => 'calendar.php', 'icon' => 'cil-calendar', 'badge' => 'PRO'],
            'datatables' => ['title' => 'DataTables', 'url' => 'datatables.php', 'icon' => 'cil-list-numbered', 'badge' => 'PRO'],
            'google-maps' => ['title' => 'OpenStreetMap', 'url' => 'google-maps.php', 'icon' => 'cil-map', 'badge' => 'PRO']
        ]
    ]
];

// Helper function to render navigation
function renderNavigation($items, $level = 0) {
    global $baseDir, $currentSection, $currentPage;

    foreach ($items as $key => $item) {
        if ($item['type'] === 'group') {
            echo '<li class="nav-title" data-coreui-i18n="' . strtolower($item['title']) . '">' . $item['title'] . '</li>';
            renderNavigation($item['items'], $level + 1);
        } elseif ($item['type'] === 'dropdown') {
            $isActive = isActiveSection($key);
            echo '<li class="nav-group">';
            echo '<a class="nav-link nav-group-toggle' . ($isActive ? ' active' : '') . '" href="#">';
            echo '<svg class="nav-icon"><use xlink:href="' . $baseDir . 'vendors/@coreui/icons/svg/free.svg#' . $item['icon'] . '"></use></svg>';
            echo '<span data-coreui-i18n="' . strtolower($item['title']) . '">' . $item['title'] . '</span>';
            echo '</a>';
            echo '<ul class="nav-group-items compact">';
            foreach ($item['items'] as $subKey => $subItem) {
                $isSubActive = isActivePage($subKey);
                echo '<li class="nav-item">';
                echo '<a class="nav-link' . ($isSubActive ? ' active' : '') . '" href="' . $baseDir . $subItem['url'] . '">';
                echo '<span class="nav-icon"><span class="nav-icon-bullet"></span></span> ' . $subItem['title'];
                if (isset($subItem['badge'])) {
                    $badgeClass = $subItem['badge'] === 'PRO' ? 'bg-danger-gradient' : 'bg-success';
                    echo '<span class="badge ' . $badgeClass . ' ms-auto">' . $subItem['badge'] . '</span>';
                }
                echo '</a></li>';
            }
            echo '</ul></li>';
        } else {
            $isActive = isActivePage($key);
            echo '<li class="nav-item">';
            echo '<a class="nav-link' . ($isActive ? ' active' : '') . '" href="' . $baseDir . $item['url'] . '">';
            echo '<svg class="nav-icon"><use xlink:href="' . $baseDir . 'vendors/@coreui/icons/svg/free.svg#' . $item['icon'] . '"></use></svg>';
            echo $item['title'];
            if (isset($item['badge'])) {
                $badgeClass = $item['badge'] === 'PRO' ? 'bg-danger-gradient' : 'bg-success';
                echo '<span class="badge ' . $badgeClass . ' ms-auto">' . $item['badge'] . '</span>';
            }
            echo '</a></li>';
        }
    }
}
?>