<?php

echo "<h2>Apache Module Test</h2>";

if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "<p style='color: green;'><strong>✅ mod_rewrite is ENABLED</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ mod_rewrite is NOT enabled</strong></p>";
    }

    echo "<h3>All Apache Modules:</h3>";
    echo "<ul>";
    foreach ($modules as $module) {
        echo "<li>" . $module . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: orange;'><strong>⚠️ Cannot determine if mod_rewrite is enabled (function not available)</strong></p>";
    echo "<p>This might mean:</p>";
    echo "<ul>";
    echo "<li>Running on a different web server (like Nginx)</li>";
    echo "<li>PHP is running in CGI mode</li>";
    echo "<li>Apache function access is restricted</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<h3>PHP Info:</h3>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Current URL:</strong> " . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>";

?>