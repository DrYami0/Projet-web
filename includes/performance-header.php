<?php
/**
 * Performance Optimization Header
 * Include this file at the start of PHP pages for automatic gzip compression
 * and cache control headers
 */

// Enable output compression
if (!ob_get_active()) {
    ob_start('ob_gzhandler');
}

// Set cache headers (1 hour for dynamic content)
if (empty($_SESSION)) {
    header('Cache-Control: public, max-age=3600');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
}

// Ensure Vary header for compressed responses
header('Vary: Accept-Encoding');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Register shutdown function to flush output
register_shutdown_function(function() {
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
});
?>
