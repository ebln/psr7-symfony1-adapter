Partial PSR-7 Adapters for Symfony 1.5
======================================

To enable the use of future-proof PSR-15 middlewares via partial PSR-7 adapters.

## Quickstart

```php
// not fully PSR-7 compliant lazy adapters
$serverRequestAdapter = \brnc\Symfony1\Message\Adapter\Request::fromSfWebRequest($sfWebRequest);
$responseAdapter      = \brnc\Symfony1\Message\Adapter\Response::fromSfWebResponse($sfWebResponse);
```

## ServerRequest

Please mind the following PSR-7 violation which is enabled by default:

### No immutability by default

as this is just an adapter for `\sfWebRequest` which cannot easily be replaced with another instance.

This adapter – by default – also returns the very same instance when calling `with*()` methods. For the same reason calls to methods which cannot act on and alter the underlying `\sfWebRequest`
will throw an `\brnc\Symfony1\Message\Exception\LogicException`.

This default behaviour can be changed by creating the `Request` using the `Request::OPTION_IMMUTABLE_VIOLATION` option set to `false`. The `Request`-adapter will then always return new instances when `with*()`-methods are called and won't throw exceptions on calls which cannot transparently act on the `\sfWebRequest`- object.

```php
use brnc\Symfony1\Message\Adapter\Request;

$serverRequestAdapter = Request::fromSfWebRequest(
    $sfWebRequest,
    [
        // If set to true a stream on php://input is used instead of creating one over sfWebRequest::getContent() → defaults to false
        Request::OPTION_BODY_USE_STREAM     => false,
        // sfWebRequest-compatibility mode – set to false if you need PSR-7's immutability
        Request::OPTION_IMMUTABLE_VIOLATION => true, 
    ]
);
```

## Response

Please mind the default to mutability!

```php
use brnc\Symfony1\Message\Adapter\Response;

$responseAdapter = Response::fromSfWebResponse(
    $sfWebResponse,
    [Response::OPTION_IMMUTABLE_VIOLATION => false]
);
$newInstance     = $responseAdapter->withBody(
    \GuzzleHttp\Psr7\Utils::streamFor(
        '<html><head><title>Hello World!</title></head><body><h1>PSR-7 Adapters!</h1></body></html>'
    )
);
$newestInstance  = $newInstance->withBody(
    \GuzzleHttp\Psr7\Utils::streamFor(
        '<html><head><body><h1>dead end</h1></body></html>'
    )
);

// selects the content of $newInstance to be send instead of the most recent instance's one (i.e. $newestInstance)
$newInstance->preSend();
// N.b. The stream of $newestInstance is still held in memory until $responseAdapter and all copies got destroyed!
//      This might change in the future when this will be refactored to use WeakMap.

$sfWebResponse->send();

```

## Pass it down to a PSR-15 sub-stack

You may use the `ResponseFactory` implementing `\Psr\Http\Message\ResponseFactoryInterface` in order to "spawn" responses within your PSR-15 sub-stack.

```php
$request         = \brnc\Symfony1\Message\Adapter\Request::fromSfWebRequest($sfWebRequest);
$responseFactory = new \brnc\Symfony1\Message\Factory\ResponseFactory($sfWebResponse);
// (dependency) inject the ResponseFactory to your dispatcher, middlewares, and handlers
$entryPoint      = new YourPSR15Dispatcher($responseFactory);
// Dispatch your sub-stack via PSR-15
$response        = $entryPoint->handler($response);
// As $response will be linked to $sfWebResponse you don't need to do anything
// if you are in the context of a Symfony1 action. Only call $response->getSfWebResponse() in dire need!
```

## Manually transcribe a PSR-7 Response to Symfony1

Assume you couldn't use other means, and you're confronted with an arbitrary PSR-7 response you can use the `ResponseTranscriptor` to copy the data from your PSR-7 response to your `\sfWebResponse`.

The `ResponseTranscriptor` by default uses `NoCookieTranscriptor`, which fails hard in the presence of `Set-Cookie'` headers.
Incorporating (present-day) Cookies into the `\sfWebResponse` is not strait-forward. However, you are free to implement your own Cookie-Handler implementing `CookieTranscriptorInterface` and pass it as an optional constructor argument.

```php
// Given arbitrary PSR-7 response…
$psr7response = $psr7responseFactory();
// …use the ResponseTranscriptor in order to–
$transcriptor = new \brnc\Symfony1\Message\Transcriptor\ResponseTranscriptor();
// copy the response's contents.
//   The returned object will be the same as in the argument!
$sfWebResponse = $transcriptor->transcribe($psr7response, $sfWebResponse);
```

### Implemented `CookieTranscriptorInterface`s

There are a few CookieTranscriptors already implemented; each come with their specific compromises.

#### `CookieHeaderTranscriptor`
Transcribes `Set-Cookie` headers from your PSR-7 response, into the cookie management of the Symfony1 response.
This comes with all downsides of the legacy signature of `setrawcookie()`. Foremost it's not supporting `SameSite`-attribute, nor everything else being `extension-av` as of RFC 265.

#### `AbstractCookieDispatchTranscriptor`
The (abstract) CookieDispatchTranscriptor uses reflection and swaps the response's EventDispatcher against a new one.
It is very tied against the original implementation of `sfWebResponse::sendHttpHeaders` especially its logging mechanism via events.
The `CookieDispatcher` puts itself between `sfWebResponse` and the original `sfEventDispatcher`, and fires the cookies from the PSR-7 response right before Symfony1 would have sent theirs.
You need to implement `AbstractCookieDispatchTranscriptor`'s `transcribeCookies()` method, depending on your source for the cookies being set. E.g. if your using a 3rd party library.
Your code eventually needs to return `CookieContainerInterface` full of `CookieInterface`s. There is already a `HeaderCookie`, that uses `header()` and expects an already crafted and complete `Set-Cookie`-headerline.
There are also `SetCookie` and `SetRawCookie` which will use the respective methods with the new signature – i.e. three arguments, with the options-array as a third one.

## Pass it down to http-foundation i.e. present-day Symfony

Combine this PSR7-Symfony1 Adapter and `symfony/psr-http-message-bridge` to connect your Symfony1 stack via PSR-7 to `symfony/http-foundation` objects and leverage using embedded (present-day) Symfony components.

```php
// Use this chain to create a http-foundation request from a Symfony1's \sfWebRequest
$psrRequest            = \brnc\Symfony1\Message\Adapter\Request::fromSfWebRequest($sfWebRequest);
$httpFoundationFactory = \Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory();
$symfonyRequest        = $httpFoundationFactory->createRequest($psrRequest);

// Handle the request with some present day Symfony component
$symfonyResponse = $httpKernel->handle($symfonyRequest);

// Possibly ResponseFactory is best created in the Symfony1 context
$responseFactory = new \brnc\Symfony1\Message\Factory\ResponseFactory($sfWebResponse);

// Obtain other PSR17 factories,
//   while only ResponseFactory & StreamFactory will be used (as of today)
$streamFactory   = \brnc\Symfony1\Message\Factory\GuzzleStreamFactory();
$decoyFactory    = \brnc\Symfony1\Message\Factory\DecoyHttpFactory();
// Construct the PsrHttpFactory from symfony/psr-http-message-bridge and translate…
$psrHttpFactory  = Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory(
    $decoyFactory, $streamFactory, $decoyFactory, $responseFactory
);
$psrResponse     = $psrHttpFactory->createResponse($symfonyResponse);
// As $psrResponse will be linked to $sfWebResponse as it was created through the
// ResponseFactory you don't need to do anything if you exit via an Symfony1 action.
// Only call $psrResponse->getSfWebResponse() in dire need!
```
