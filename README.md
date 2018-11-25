Partial PSR-7 Adapters for Symfony 1.5
======================================

To enable the use of future-proof PSR-15 middlewares via partial PSR-7 adapers.

## Usage example
```php
$requestProxy        = \brnc\Symfony1\Message\Obligation\SfWebRequestSubsetProxy::create($sfWebRequest);
$subsetServerRequest = new \brnc\Symfony1\Message\Adapter\ReadMinimalRequestHeadAdapter($requestProxy);
```
