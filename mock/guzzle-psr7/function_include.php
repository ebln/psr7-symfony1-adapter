<?php

declare(strict_types=1);

/*
 * TODO remove this and the whole directory once php-http/psr7-integration-tests
 *      supports GuzzleHttp\Psr7 ^2
 *
 * Sadly needed as php-http/psr7-integration-tests @ 1.1.1
 * relies on the deprecated GuzzleHttp\Psr7::stream_for instead of Utils::streamFor
 *
 * @internal
 */
if (!function_exists('GuzzleHttp\Psr7\stream_for')) {
    require __DIR__ . '/stream_for.php';
}
