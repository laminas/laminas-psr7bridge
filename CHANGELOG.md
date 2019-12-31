# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 0.2.2 - 2016-05-10

### Added

- [zendframework/zend-psr7bridge#8](https://github.com/zendframework/zend-psr8bridge/pull/8) adds and
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
