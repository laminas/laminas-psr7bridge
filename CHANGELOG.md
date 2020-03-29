# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.2.2 - 2020-03-29

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixed `replace` version constraint in composer.json so repository can be used as replacement of `zendframework/zend-psr7bridge:^1.2.0`.

## 1.2.1 - 2020-01-24

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#2](https://github.com/laminas/laminas-psr7bridge/pull/2) Fixes error on attempt to convert laminas-http request with null body content

## 1.2.0 - 2018-12-20

### Added

- [zendframework/zend-psr7bridge#41](https://github.com/zendframework/zend-psr7bridge/pull/41) adds support for PHP 7.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-psr7bridge#41](https://github.com/zendframework/zend-psr7bridge/pull/41) removes support for laminas-stdlib v2 releases.

### Fixed

- Nothing.

## 1.1.1 - 2018-12-20

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-psr7bridge#40](https://github.com/zendframework/zend-psr7bridge/pull/40) fixes how headers are translated from PSR-7 to laminas-http. Previously, they
  were always cast to `GenericHeader` instances; now, the bridge uses `Psr7Response::psr7HeadersToString` 
  to pass them to `Laminas\Http\Headers::fromString()`,  ensuring that the more
  specific laminas-http `HeaderInterface` instance types are created.

## 1.1.0 - 2018-09-27

### Added

- Nothing.

### Changed

- [zendframework/zend-psr7bridge#38](https://github.com/zendframework/zend-psr7bridge/pull/38) updates the zendframework/zend-diactoros constraint to allow either the
  1.Y or 2.Y series, as they are compatible for the purposes of this package.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.2 - 2018-02-14

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-psr7bridge#35](https://github.com/zendframework/zend-psr7bridge/pull/35) fixes the
  Response from a PSR-7 Stream object with php://memory stream

## 1.0.1 - 2017-12-18

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-psr7bridge#23](https://github.com/zendframework/zend-psr7bridge/pull/23) fixed the
  upload of a file when error is not UPLOAD_ERR_OK
- [zendframework/zend-psr7bridge#26](https://github.com/zendframework/zend-psr7bridge/pull/26) fixes the
  Stream response from a PSR-7 Stream object
- [zendframework/zend-psr7bridge#28](https://github.com/zendframework/zend-psr7bridge/pull/28) fixes the
  baseUrl from a PSR-7 Server request

## 1.0.0 - 2017-08-02

### Added

- [zendframework/zend-psr7bridge#19](https://github.com/zendframework/zend-psr7bridge/pull/19) adds support
  for PHP 7.1.

- [zendframework/zend-psr7bridge#19](https://github.com/zendframework/zend-psr7bridge/pull/19) adds support
  for PHP 7.2.

### Changed

- [zendframework/zend-psr7bridge#15](https://github.com/zendframework/zend-psr7bridge/pull/15) updates the
  behavior of `Psr7ServerRequest::fromLaminas()` to check if the request is a
  `Laminas\Http\PhpEnvironment\Request` and, if so, use the return value of its
  `getServer()` method to seed the PSR-7 request's server parameters.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-psr7bridge#19](https://github.com/zendframework/zend-psr7bridge/pull/19) removes
  support for PHP 5.5.

- [zendframework/zend-psr7bridge#19](https://github.com/zendframework/zend-psr7bridge/pull/19) removes
  support for HHVM.

### Fixed

- Nothing.

## 0.2.2 - 2016-05-10

### Added

- [zendframework/zend-psr7bridge#8](https://github.com/zendframework/zend-psr7bridge/pull/8) adds and
  publishes the documentation to https://docs.laminas.dev/laminas-psr7bridge/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-psr7bridge#7](https://github.com/zendframework/zend-psr7bridge/pull/7) fixes
  the logic in `Psr7ServerRequest::convertUploadedFiles()` to ensure that the
  `tmp_name` is provided to the `$_FILES` structure from the PSR-7 uploaded
  files.
- [zendframework/zend-psr7bridge#7](https://github.com/zendframework/zend-psr7bridge/pull/7) fixes
  the logic in `Psr7ServerRequest::convertFilesToUploaded()` to iterate the
  entire value provided it, instead of a fictitious `file` key.

## 0.2.1 - 2015-12-15

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-psr7bridge#5](https://github.com/zendframework/zend-psr7bridge/pull/5) Updates
  `Psr7ServerRequest::fromLaminas()` to inject the generated PSR-7 request
  instance with the laminas-http cookies.

## 0.2.0 - 2015-09-28

### Added

- [zendframework/zend-psr7bridge#3](https://github.com/zendframework/zend-psr7bridge/pull/3) Adds support for
  laminas-http -&gt; PSR-7 request tanslation.
- [zendframework/zend-psr7bridge#3](https://github.com/zendframework/zend-psr7bridge/pull/3) Adds support for
  PSR-7 &lt;-&gt; laminas-http response tanslation.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.1 - 2015-08-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-psr7bridge#2](https://github.com/zendframework/zend-psr7bridge/pull/2) updates
  `Laminas\Psr7Bridge\Laminas\Request`'s constructor to call `setUri()` instead of
  `setRequestUri()`.

## 0.1.0 - 2015-08-06

Initial release!

### Added

- `Laminas\Psr7Bridge\Psr7ServerRequest::toLaminas($request, $shallow = false)` allows
  converting a `Psr\Http\Message\ServerRequestInterface` to a
  `Laminas\Http\PhpEnvironment\Request` instance. The `$shallow` flag, when
  enabled, will omit the body content, body parameters, and upload files from
  the laminas-http request (e.g., for routing purposes).

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
