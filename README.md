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

This adapter – by default – also returns the very same instance when calling `with*()` methods.
For the same reason calls to methods which cannot act on and alter the underlying `\sfWebRequest`
will throw an `\brnc\Symfony1\Message\Exception\LogicException`.

This default behaviour can be changed by creating the `Request` using 
the `Request::OPTION_IMMUTABLE_VIOLATION` option set to `false`.
The `Request`-adapter will then always return new instances when `with*()`-methods are called and won't throw exceptions on calls which cannot transparently act on the `\sfWebRequest`- object.


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
```php
$request         = \brnc\Symfony1\Message\Adapter\Request::fromSfWebRequest($sfWebRequest);
$responseFactory = new \brnc\Symfony1\Message\Translator\ResponseFactory($sfWebResponse);
// (dependency) inject the ResponseFactory to your dispatcher, middlewares, and handlers
$entryPoint      = new YourPSR15Dispatcher($responseFactory);
// Dispatch your sub-stack via PSR-15
$response        = $entryPoint->handler($response);
// As $response will be linked to $sfWebResponse you don't need to do anything
// if you are in the context of a Symfony1 action. Only call $response->getSfWebResponse() in dire need!
```
