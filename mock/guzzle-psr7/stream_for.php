<?php

namespace GuzzleHttp\Psr7;

/*
 * TODO remove this and the whole directory once php-http/psr7-integration-tests
 *      supports GuzzleHttp\Psr7 ^2
 *
 * Sadly needed as php-http/psr7-integration-tests @ 1.1.1
 * relies on the deprecated GuzzleHttp\Psr7::stream_for instead of Utils::streamFor
 */
function stream_for($resource = '', array $options = [])
{
    return \GuzzleHttp\Psr7\Utils::streamFor($resource, $options);
}
