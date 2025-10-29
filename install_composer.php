<?php
/**
 * Composer Installation Script for Repair Records System
 * This script helps set up Composer and PHPSpreadsheet for Excel export functionality
 */

echo "<h2>Composer Setup for Repair Records System</h2>\n";
echo "<p>This script will help you set up Composer and PHPSpreadsheet for Excel export functionality.</p>\n";

// Check if Composer is already installed
if (file_exists('vendor/autoload.php')) {
    echo "<div style='color: green; font-weight: bold;'>✓ Composer is already installed!</div>\n";
    echo "<p>You can now use Excel export functionality.</p>\n";
    exit;
}

echo "<h3>Step 1: Download Composer</h3>\n";
echo "<p>Please download and install Composer from: <a href='https://getcomposer.org/download/' target='_blank'>https://getcomposer.org/download/</a></p>\n";

echo "<h3>Step 2: Install Dependencies</h3>\n";
echo "<p>After installing Composer, run the following command in your project directory:</p>\n";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>composer install</pre>\n";

echo "<h3>Step 3: Verify Installation</h3>\n";
echo "<p>After running 'composer install', refresh this page to verify the installation.</p>\n";

echo "<h3>Alternative: Manual Setup</h3>\n";
echo "<p>If you prefer not to use Composer, the system will work with CSV export functionality instead of Excel.</p>\n";
echo "<p>CSV files can be opened in Excel and provide the same data with basic formatting.</p>\n";

echo "<hr>\n";
echo "<p><strong>Note:</strong> The repair records system will work without Composer, but Excel export with advanced formatting requires PHPSpreadsheet.</p>\n";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Composer Setup - Repair Records</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        pre { background: #f5f5f5; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        a { color: #007cba; }
    </style>
</head>
<body>
    <div style="margin-top: 20px;">
        <a href="reports.php">← Back to Reports</a> | 
        <a href="index.php">← Back to Main Records</a>
    </div>
</body>
</html>
