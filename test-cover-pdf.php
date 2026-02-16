<?php
/**
 * Test Cover Page PDF Generation
 * Access via: /wp-content/plugins/quotation-form/test-cover-pdf.php?generate=1
 */

// Only run if explicitly triggered
if (!isset($_GET['generate'])) {
    echo '<h2>Cover Page PDF Test</h2>';
    echo '<p><a href="?generate=1">Generate PDF with cover page only</a></p>';
    exit;
}

// Find wkhtmltopdf
$wkhtmltopdf_paths = ['/usr/bin/wkhtmltopdf', '/usr/local/bin/wkhtmltopdf'];
$wkhtmltopdf_binary = null;
foreach ($wkhtmltopdf_paths as $path) {
    if (@is_executable($path)) {
        $wkhtmltopdf_binary = $path;
        break;
    }
}

if (!$wkhtmltopdf_binary) {
    die('wkhtmltopdf not found. Checked: ' . implode(', ', $wkhtmltopdf_paths));
}

// Cover page HTML
$cover_html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@page { margin: 0; padding: 0; }
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body { margin: 0; padding: 0; width: 100%; height: 100%; }
img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
</style>
</head>
<body>
<img src="https://cristalwindows.co.uk/cristal-windows-quote-cover-background.jpg">
</body>
</html>';

// Write temp file
$temp_html = sys_get_temp_dir() . '/test-cover-' . time() . '.html';
$temp_pdf = sys_get_temp_dir() . '/test-cover-' . time() . '.pdf';
file_put_contents($temp_html, $cover_html);

// Generate PDF with zero margins
$command = sprintf(
    '%s --page-size A4 --margin-top 0 --margin-right 0 --margin-bottom 0 --margin-left 0 --encoding UTF-8 --enable-local-file-access --quiet %s %s 2>&1',
    escapeshellarg($wkhtmltopdf_binary),
    escapeshellarg($temp_html),
    escapeshellarg($temp_pdf)
);

$output = shell_exec($command);

// Clean up temp HTML
@unlink($temp_html);

// Serve PDF
if (file_exists($temp_pdf) && filesize($temp_pdf) > 0) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="test-cover.pdf"');
    header('Content-Length: ' . filesize($temp_pdf));
    readfile($temp_pdf);
    @unlink($temp_pdf);
    exit;
}

// If we get here, it failed
@unlink($temp_pdf);
echo '<h2>PDF generation failed</h2>';
if ($output) {
    echo '<pre>' . htmlspecialchars($output) . '</pre>';
}
