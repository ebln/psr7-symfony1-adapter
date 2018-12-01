Partial PSR-7 Adapters for Symfony 1.5
======================================

To enable the use of future-proof PSR-15 middlewares via partial PSR-7 adapers.

## Usage example
```php
// wrapper for sfWebRequest to allow self-sufficent type hinting
$requestProxy         = \brnc\Symfony1\Message\Obligation\SfWebRequestSubsetProxy::create($sfWebRequest);
// not fully PSR-7 compliant lazy adapters
$serverRequestAdapter = new \brnc\Symfony1\Message\Adapter\Request($requestProxy);
```
