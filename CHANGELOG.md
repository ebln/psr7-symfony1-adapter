Changelog
=========

Intended to follow [«Keep a Changelog»](https://keepachangelog.com/en/)

----

## [Unreleased] (meant as staging area)

### Added
- …

### TODO
- Cookies: Write to response
- Cookies: Read from request
- Cookies: write and overwrite to request
- Refactor `Request::withUri` to be closer to PSR-7
- Refactor `BodyStreamHook::addBodyFromResponse` using WeakMap for a PHP >=8.0 (or with [polyfill](https://github.com/BenMorel/weakmap-polyfill))
- Allow configurable StreamFactory instead of hardcoded used
  - or try `php-http/discovery`

----

## [1.6.0]  - 2024-06-07

### Changed
- Dependencies
  - added:  `"psr/http-message": "^1.1 || ^2.0"` as a direct dependency!
  - removed: support for `guzzlehttp/psr7` < `2.4.5`
  - bumped to `webmozart/assert:^1.11`
- Running CI checks fro PHP 7.4 - 8.3
- Minor code fixes, due to psalm reports
- Update dev dependencies
- Update CI (GH actions)
- Fix local dev env / dockerfile
- Update code style and cs-fixer

## [1.5.0]  - 2023-04-21

### Changed
- Code
  - fixed `getRequestTarget` due to `RequestIntegrationTest::testGetRequestTargetInOriginFormNormalizesUriWithMultipleLeadingSlashesInPath`
  - Signature of `Factory\DecoyHttpFactory::createUploadedFile` changed slightly
  - `withRequestTarget` not accepting mixed anymore
- Remove `mock/guzzle-psr7` as `php-http/psr7-integration-tests` supports `GuzzleHttp\Psr7 ^2.0`
- Updated dev dependencies
- Update code style

### Fixed

- downstream vulnerabilities by bumping `guzzlehttp/psr7`

## [1.4.0]  - 2021-10-19

### Added

- PSR-17 `ResponseFactory` to enable PSR-15
- `ResponseTranscriptor` to transcribe PSR-7 responses directly to Symfony1 one's
- Dependency to PSR-17 i.e. `psr/http-factory`
- PSR-17 GuzzleStreamFactory and a DecoyHttpFactory to support `symfony/psr-http-message-bridge`
- Started a changelog
- composer-normalize

### Changed

- Updated dev dependencies
- Renamed "Utillity" to `brnc\Symfony1\Message\Utility`, technically [BREAKING] yet was never supposed to be used in user land

### Fixed

- DX: makefile, dockerfile & xdebug

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
