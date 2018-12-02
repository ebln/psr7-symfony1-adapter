Partial PSR-7 Adapters for Symfony 1.5
======================================

To enable the use of future-proof PSR-15 middlewares via partial PSR-7 adapers.

## Usage example
```php
// not fully PSR-7 compliant lazy adapters
$serverRequestAdapter = new \brnc\Symfony1\Message\Adapter\Request($sfWebRequest);
```
