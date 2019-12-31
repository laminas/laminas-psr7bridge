# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

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
