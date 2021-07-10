<?php

declare(strict_types=1);

/*
 * Sadly needed as php-http/psr7-integration-tests @ 1.1.1
 * relies on the deprecated GuzzleHttp\Psr7::stream_for instead of Utils::streamFor
 */
if (!function_exists('GuzzleHttp\Psr7\stream_for')) {
    require __DIR__ . '/streamFor.php';
}
