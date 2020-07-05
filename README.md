Partial PSR-7 Adapters for Symfony 1.5
======================================

To enable the use of future-proof PSR-15 middlewares via partial PSR-7 adapers.

## Quickstart
```php
// not fully PSR-7 compliant lazy adapters
$serverRequestAdapter = \brnc\Symfony1\Message\Adapter\Request::fromSfWebRequest($sfWebRequest);
$responseAdapter      = \brnc\Symfony1\Message\Adapter\Response::fromSfWebResponse($sfWebResponse);
```

## ServerRequest
Please mind the following PSR-7 violation which is enabled by default:
### No immutability by default
As this is just an adapter for `\sfWebRequest` which cannot just be switched with another instance;

This adapter – by default – also returns the very same instance when calling `with*()` methods.
For the same reason calls to methods which cannot act on and alter the underlying `\sfWebRequest`
will throw an `\brnc\Symfony1\Message\Exception\LogicException`.

This default behaviour can by changes by creating the `Request` using 
the `Request::OPTION_IMMUTABLE_VIOLATION` option set to `true`.
The `Request`-adapter will then always return a close when used
with `with*()` methods and won't throw exceptions
which cannot transparently act on the `\sfWebRequest`- object


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
    \GuzzleHttp\Psr7\stream_for(
        '<html><head><title>Hello World!</title></head><body><h1>PSR-7 Adapters!</h1></body></html>'
    )
);
$newestInstance  = $newInstance->withBody(
    \GuzzleHttp\Psr7\stream_for(
        '<html><head><body><h1>dead end</h1></body></html>'
    )
);

// selects the content of $newInstance to be send instead of the most recent instance's one (i.e. $newestInstance)
$newInstance->preSend();

$sfWebResponse->send();

```
