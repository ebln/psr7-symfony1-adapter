<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Utility;

use brnc\Symfony1\Message\Compatibility\Assert as CompatibilityAssert;
use Webmozart\Assert\Assert as WebmozartAssert;

// webmozart/assert return types are incompatible between 1.x and 2.x
if ((new \ReflectionMethod(WebmozartAssert::class, 'reportInvalidArgument'))->hasReturnType()) {
    require __DIR__ . '/../Compatibility/Assert2x.php'; // webmozart/assert 2.x (PHP >= 8.2)
} else {
    require __DIR__ . '/../Compatibility/Assert1x.php'; // webmozart/assert 1.x
}

class Assert extends CompatibilityAssert {}
