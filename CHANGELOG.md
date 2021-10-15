Changelog
=========

Intended to follow [«Keep a Changelog»](https://keepachangelog.com/en/)

----

## [Unreleased] (meant as staging area)

### Added

- `ResponseFactory` to enable PSR-15
- Dependency to `psr/http-factory`
- Started a changelog
- composer-normalize

### Changed

- Updated dev dependencies
- Renamed "Utillity" to `brnc\Symfony1\Message\Utility`,
    technically [BREAKING] yet was never supposed to be used in user land

### Fixed

- DX: makefile, dockerfile & xdebug

### TODO

- ResponseFactory
- ResponseTranscriptor
- Cookies: Write to response
- Cookies: Read from request
- Cookies: write and overwrite to request
- Refactor `Request::withUri` to be closer to PSR-7
- Refactor `BodyStreamHook::addBodyFromResponse` using WeakMap for a PHP >=8.0 (or with [polyfill](https://github.com/BenMorel/weakmap-polyfill))

----
No changelog before October 2021, the following was only casually reconstructed

----

## [1.3.3] - 2021-09-18

### Fixed

- Support for status codes greater than 500 (yet less than 600)

## [1.2.1] - 2021-09-18

### Fixed

- Support for status codes greater than 500 (yet less than 600)

## [1.3.2] - 2021-07-10

### Changed

- Bumped `guzzlehttp/psr7` dependency to `1.7 || 2.0`

## [1.2.0] - 2021-07-10

### Removed

- Dropped support for PHP <7.4

----

## [0.0.0] - 1970-01-01 Template

### Added

- Feature A

### Changed

### Deprecated

### Removed

### Fixed

### Security