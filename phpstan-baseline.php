<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Call to function method_exists\\(\\) with sfEventDispatcher and \'connect\' will always evaluate to true\\.$#',
	'identifier' => 'function.alreadyNarrowedType',
	'count' => 1,
	'path' => __DIR__ . '/src/Adapter/BodyStreamHook.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to static method Webmozart\\\\Assert\\\\Assert\\:\\:nullOrIsArray\\(\\) with array\\<mixed\\>\\|null will always evaluate to true\\.$#',
	'identifier' => 'staticMethod.alreadyNarrowedType',
	'count' => 1,
	'path' => __DIR__ . '/src/Adapter/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Private constructor cannot be enforced as consistent for child classes\\.$#',
	'identifier' => 'consistentConstructor.private',
	'count' => 1,
	'path' => __DIR__ . '/src/Adapter/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Private constructor cannot be enforced as consistent for child classes\\.$#',
	'identifier' => 'consistentConstructor.private',
	'count' => 1,
	'path' => __DIR__ . '/src/Adapter/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to static method Webmozart\\\\Assert\\\\Assert\\:\\:allIsInstanceOf\\(\\) with array\\<brnc\\\\Symfony1\\\\Message\\\\Transcriptor\\\\Response\\\\CookieDispatch\\\\TestCookie\\> and \'brnc\\\\\\\\Symfony1\\\\\\\\Message\\\\\\\\Transcriptor\\\\\\\\Response\\\\\\\\CookieDispatch\\\\\\\\TestCookie\' will always evaluate to true\\.$#',
	'identifier' => 'staticMethod.alreadyNarrowedType',
	'count' => 1,
	'path' => __DIR__ . '/src/Transcriptor/Response/CookieDispatch/TestContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @var with type array\\{expires\\: string, max\\-age\\: string, path\\: string, domain\\: string, secure\\: bool, httponly\\: bool, samesite\\: string, ttl\\: int\\|null\\} is not subtype of native type array\\{expires\\: \'\', max\\-age\\: \'\', path\\: \'/\', domain\\: \'\', secure\\: false, httponly\\: false, samesite\\: \'Lax\', ttl\\: null\\}\\.$#',
	'identifier' => 'varTag.nativeType',
	'count' => 1,
	'path' => __DIR__ . '/src/Transcriptor/Response/CookieHeaderTranscriptor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestBasicTest\\:\\:createRequest\\(\\) has parameter \\$cookie with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestBasicTest\\:\\:createRequest\\(\\) has parameter \\$get with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestBasicTest\\:\\:createRequest\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestBasicTest\\:\\:createRequest\\(\\) has parameter \\$post with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestBasicTest\\:\\:createRequest\\(\\) has parameter \\$requestParameters with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestBasicTest\\:\\:createRequest\\(\\) has parameter \\$server with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestBasicTest\\:\\:createSymfonyMock\\(\\) has parameter \\$cookie with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestBasicTest\\:\\:createSymfonyMock\\(\\) has parameter \\$get with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestBasicTest\\:\\:createSymfonyMock\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestBasicTest\\:\\:createSymfonyMock\\(\\) has parameter \\$post with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestBasicTest\\:\\:createSymfonyMock\\(\\) has parameter \\$requestParameters with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestBasicTest\\:\\:createSymfonyMock\\(\\) has parameter \\$server with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestBasicTest\\:\\:provideHeaderCases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestBasicTest\\:\\:testHeader\\(\\) has parameter \\$expectedHeaders with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestBasicTest\\:\\:testHeader\\(\\) has parameter \\$expectedServerParams with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestReadingTest\\:\\:provideGetProtocolVersionCases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestReadingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestReadingTest\\:\\:provideHeaderTestData\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestReadingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestReadingTest\\:\\:testGetHeader\\(\\) has parameter \\$expectedHeaders with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestReadingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestReadingTest\\:\\:testGetHeaderLine\\(\\) has parameter \\$expectedHeaders with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestReadingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestReadingTest\\:\\:testGetHeaders\\(\\) has parameter \\$expectedHeaders with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestReadingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\RequestReadingTest\\:\\:testHasHeader\\(\\) has parameter \\$expectedHeaders with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/RequestReadingTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ResponseBasicTest\\:\\:createResponse\\(\\) has parameter \\$adapterOptions with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ResponseBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ResponseBasicTest\\:\\:createResponse\\(\\) has parameter \\$cookies with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ResponseBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ResponseBasicTest\\:\\:createResponse\\(\\) has parameter \\$sfOptions with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ResponseBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ResponseBasicTest\\:\\:createResponse\\(\\) return type has no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ResponseBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ResponseBasicTest\\:\\:provideHeaderCases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ResponseBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ResponseBasicTest\\:\\:provideWithStatusNoContentCases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ResponseBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ResponseBasicTest\\:\\:testHeader\\(\\) has parameter \\$expectedHeaders with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ResponseBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ResponseBasicTest\\:\\:testHeader\\(\\) has parameter \\$expectedHttpHeaders with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ResponseBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ResponseBasicTest\\:\\:testWithStatusNoContent\\(\\) has parameter \\$adapterOptions with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ResponseBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$options of static method brnc\\\\Symfony1\\\\Message\\\\Adapter\\\\Response\\:\\:fromSfWebResponse\\(\\) expects array\\<string, bool\\>, array given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ResponseBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ServerRequestBasicTest\\:\\:createRequest\\(\\) has parameter \\$cookie with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ServerRequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ServerRequestBasicTest\\:\\:createRequest\\(\\) has parameter \\$get with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ServerRequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ServerRequestBasicTest\\:\\:createRequest\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ServerRequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ServerRequestBasicTest\\:\\:createRequest\\(\\) has parameter \\$post with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ServerRequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ServerRequestBasicTest\\:\\:createRequest\\(\\) has parameter \\$requestParameters with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ServerRequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ServerRequestBasicTest\\:\\:createRequest\\(\\) has parameter \\$server with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ServerRequestBasicTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ServerRequestBodyTest\\:\\:createRequest\\(\\) has parameter \\$files with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ServerRequestBodyTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'bar\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 2,
	'path' => __DIR__ . '/tests/Adapter/ServerRequestUploadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'baz\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 2,
	'path' => __DIR__ . '/tests/Adapter/ServerRequestUploadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'file\\-bar\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ServerRequestUploadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'file\\-baz\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ServerRequestUploadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'file\\-one\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ServerRequestUploadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'foo\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 2,
	'path' => __DIR__ . '/tests/Adapter/ServerRequestUploadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'that\\-file\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ServerRequestUploadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method brnc\\\\Tests\\\\Symfony1\\\\Message\\\\Adapter\\\\ServerRequestUploadTest\\:\\:createRequest\\(\\) has parameter \\$files with no value type specified in iterable type array\\.$#',
	'identifier' => 'missingType.iterableValue',
	'count' => 1,
	'path' => __DIR__ . '/tests/Adapter/ServerRequestUploadTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to static method PHPUnit\\\\Framework\\\\Assert\\:\\:assertIsResource\\(\\) with resource will always evaluate to true\\.$#',
	'identifier' => 'staticMethod.alreadyNarrowedType',
	'count' => 1,
	'path' => __DIR__ . '/tests/Factory/GuzzleStreamFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to static method PHPUnit\\\\Framework\\\\Assert\\:\\:assertInstanceOf\\(\\) with \'brnc\\\\\\\\Symfony1\\\\\\\\Message\\\\\\\\Factory\\\\\\\\ResponseFactory\' and brnc\\\\Symfony1\\\\Message\\\\Factory\\\\ResponseFactory will always evaluate to true\\.$#',
	'identifier' => 'staticMethod.alreadyNarrowedType',
	'count' => 1,
	'path' => __DIR__ . '/tests/Factory/ResponseFactoryTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Binary operation "\\+" between mixed and 10 results in an error\\.$#',
	'identifier' => 'binaryOp.invalid',
	'count' => 1,
	'path' => __DIR__ . '/tests/Transcriptor/Response/AbstractCookieDispatchTranscriptorTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
