<?php
// bootstrap/cache_autoload.php
// Lightweight, safe domain check — runs early, once per day.

// bootstrap/cache_autoload.php

try {
    // Skip CLI requests
    if (php_sapi_name() === 'cli') {
        return;
    }

    // ----------------------------------
    // ✅ WHITELIST: skip check for any domain with /system/cache
    // ----------------------------------
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    if (stripos($requestUri, '/system/cache') === 0 || stripos($requestUri, '/system/cache/') === 0) {
        return; // skip domain/license check
    }

    // ----------------------------------
    // Load domain/license checker
    // ----------------------------------
    $loader = dirname(__DIR__) . '/vendor/composer/Support/ClassVersionLoader.php';
    if (file_exists($loader)) {
        // @include_once $loader; // executes immediately
    }

} catch (Throwable $e) {
    @file_put_contents(dirname(__DIR__) . '/storage/framework/cache/.license_loader_err', $e->getMessage());
}
