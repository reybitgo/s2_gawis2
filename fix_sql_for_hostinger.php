<?php

/**
 * Fix SQL dump for Hostinger import
 * Removes DEFINER clauses that cause permission errors
 */

$inputFile = __DIR__ . '/public/gemini_gawis_db (4).sql';
$outputFile = __DIR__ . '/public/deploy_coreui_laravel_db_hostinger.sql';

echo "Reading SQL file...\n";
$sqlContent = file_get_contents($inputFile);

if ($sqlContent === false) {
    die("Error: Could not read input file.\n");
}

echo "Removing DEFINER clauses...\n";
// Remove DEFINER=`user`@`host` from CREATE statements
$sqlContent = preg_replace(
    '/DEFINER\s*=\s*`[^`]+`@`[^`]+`\s+/',
    '',
    $sqlContent
);

echo "Writing fixed SQL file...\n";
$result = file_put_contents($outputFile, $sqlContent);

if ($result === false) {
    die("Error: Could not write output file.\n");
}

echo "\n✅ Success!\n";
echo "Fixed SQL file created: " . basename($outputFile) . "\n";
echo "File size: " . number_format(strlen($sqlContent)) . " bytes\n\n";
echo "You can now import this file to Hostinger phpMyAdmin:\n";
echo "📁 " . $outputFile . "\n";
