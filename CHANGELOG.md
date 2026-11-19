Changelog
=========

Intended to follow [«Keep a Changelog»](https://keepachangelog.com/en/)

----

## [Unreleased] (meant as staging area)

### Changed
- Development environment only, no change to the package:
  - CI runs in the official `php:*-cli-alpine` images and `composer:2`, pinned by digest in `.github/ci/compose.yml`,
    instead of installing PHP on the runner. It tests PHP 7.4 to 8.5, and PHP 7.4 with the lowest dependencies.
  - CI runs once per change: on pushes to `master`, on pull requests, monthly and on demand.
    The job "CI passed" fails unless every test job succeeded.
  - CI runs `composer audit` on both locks. Dependabot updates the actions and the CI images monthly, not the Composer packages.
  - `config.platform.php` is `7.4.33`, so every resolve locks versions that install on PHP 7.4.
  - Dev tools: phpunit 9.6.39, phpstan 2, psalm 5.26.1, php-cs-fixer 3.95 in its own lock under `tools/php-cs-fixer`.
    The dev lock no longer holds versions with advisories (phpunit, symfony/config, symfony/filesystem).
  - Removed `roave/security-advisories` and `composer/package-versions-deprecated` from the dev dependencies.
  - PHPUnit fails on an empty test suite and on an incomplete test.


## TODO
- Clone the CookieDispatcher-Approach for the ResponseAdapter
  - but instead of reading CookieContainer from options → in-situ intercept them → onStatusSent
- Cookies: Write to response
- Cookies: Read from request
- Cookies: write and overwrite to request
- Refactor `Request::withUri` to be closer to PSR-7
- Refactor `BodyStreamHook::addBodyFromResponse` using WeakMap for a PHP >=8.0 (or with [polyfill](https://github.com/BenMorel/weakmap-polyfill))
- Allow configurable StreamFactory instead of hardcoded used
  - or try `php-http/discovery`
- Change namespace from brnc to ebln, with version ^2

----

## [1.8.0]  - 2026-11-19

This release changes three things that callers can see. Check code that relies on them:
- `getHeader()` and `getHeaders()` return list headers such as `Accept` as one value; see "Fixed".
- `getHeaderLine()` returns `bar, baz` instead of `bar,baz` after `withHeader()` or `withAddedHeader()` with several values; see "Changed".
- Options set to `false` are now off; see "Fixed".

### Fixed
- Header values are no longer split on `,`.
  Values that contain a comma came back broken: `If-Modified-Since: Wed, 21 Oct 2015 07:28:00 GMT` came back as `['Wed', '21 Oct 2015 07:28:00 GMT']`.
  - `getHeader()` and `getHeaders()` return one value per header line that symfony holds.
  - This also applies to list headers such as `Accept`, `Cache-Control`, `Vary` or `X-Forwarded-For`:
    `['no-cache, private']` instead of `['no-cache', 'private']`. Code that searches these arrays for one entry has to split the line itself.
  - Values given to `withHeader()` or `withAddedHeader()` come back as separate values, while symfony still holds their joined line.
    After a change outside the adapter (e.g. `sfWebResponse::setHttpHeader()`), the line comes back as one value.
  - Each value is still trimmed of spaces and tabs. `getHeaderLine()` still returns symfony's line unchanged.
- Adapter options are read by their value. A missing key and `null` give the default.
  - `Request::OPTION_BODY_USE_STREAM` and `Response::OPTION_SEND_BODY_ON_204` set to `false` switched the option on.
  - `OPTION_IMMUTABLE_VIOLATION` of `Request` and `Response` was switched off only by `false`. Now `0`, `''` and `'0'` switch it off too.
- `CookieDispatcher` called `str_starts_with()`, which does not exist on PHP 7.4 without `symfony/polyfill-php80`.

### Changed
- `withHeader()` and `withAddedHeader()` join several values with `, ` instead of `,`.
  `getHeaderLine()` then returns `bar, baz` instead of `bar,baz`, and symfony holds and sends that line.
- Development environment only, no change to the package:
  - The dev image installs PHP 7.4 from packages.sury.org on Debian bookworm. The official `php:7.4-cli` image is based on Debian bullseye,
    and `apt-get` fails there, as the Release file of bullseye-security has expired.
  - The `makefile` calls `docker compose` (Compose v2) instead of `docker-compose`.
  - CI runs on `ubuntu-24.04` instead of `ubuntu-latest`. On ubuntu-26.04, `nanasess/setup-php` finds no packages for PHP 7.4 to 8.3.

### Known issues
- One `Set-Cookie` header per `Response`: several `Set-Cookie` values are joined into one line, and browsers keep at most the first cookie.
  Use `sfWebResponse::setCookie()` or the `ResponseTranscriptor` with a cookie transcriptor; see README, "Known limitations".
- `CookieHeaderTranscriptor` reads each `Set-Cookie` value as one cookie, so it cannot split such a joined line.
- With guzzlehttp/psr7 2.10.2 or later, a malformed `Host` header (e.g. `foo bar`) throws guzzle's `MalformedUriException`
  in `Request::fromSfWebRequest()`, as in earlier versions. To answer with a `400`, catch it there. Older guzzle versions accept such a host.

## [1.7.1]  - 2026-09-01

### Fixed
- Support `webmozart/assert` 1.x and 2.x side by side (`src/Compatibility/`), as their `reportInvalidArgument()` signatures differ.

## [1.7.0]  - 2026-08-31

### Added
- Two new CookieTranscriptors
  - `CookieHeaderTranscriptor`; squeezing `Set-Cookie` headers from PSR-7 into the cookie management of `sfWebResponse`
  - `AbstractCookieDispatchTranscriptor`; more powerful, extensible, yet more dangerous approach – using black magic.
    - Comes with new `CookieDispatcher`, `DispatchSubstitutor` and a bunch of other cookie related classes

### Changed
- Dependencies
  - added `psr/clock` `^1.0`, used by `CookieHeaderTranscriptor`
  - allow `guzzlehttp/psr7` `^3.0` next to `^2.4.5`
  - allow `webmozart/assert` `^2.0` next to `^1.11`
- Run CI also for pull requests
- General maintenance: annotations, spelling
- Improve test coverage calculation
  - using `@covers` & `@uses` annotations
  - enforce using new phpunit settings
- Improve test coverage
- Improve DX (docker, Makefile, gitattributes, cs-fixer rules)
- Extend mocks, due to new functionality

## [1.6.1]  - 2024-06-07

### Changed
- Changed package name to `ebln/psr7-symfony1-adapter`

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

### Security

- fixed downstream vulnerabilities by bumping `guzzlehttp/psr7`

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
- Renamed "Utility" to `brnc\Symfony1\Message\Utility`, technically [BREAKING] yet was never supposed to be used in user land

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
