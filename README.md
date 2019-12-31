# laminas-psr7bridge

[![Build Status](https://travis-ci.org/laminas/laminas-psr7bridge.svg?branch=master)](https://travis-ci.org/laminas/laminas-psr7bridge)

Code for converting [PSR-7](http://www.php-fig.org/psr/psr-7/) messages to
[laminas-http](https://github.com/laminas/laminas-http) messages, and vice
versa.

**Note: This project is a work in progress.**

Initial functionality is only covering conversion of non-body request data from
PSR-7 to laminas-http in order to facilitate routing in
[mezzio](https://github.com/mezzio/mezzio); we plan to
expand this once initial work on mezzio is complete.

## Installation

Install this library using composer:

```console
$ composer require laminas/laminas-psr7bridge
```

## Documentation

Documentation is [in the doc tree](doc/), and can be compiled using [bookdown](http://bookdown.io):

```console
$ bookdown doc/bookdown.json
$ php -S 0.0.0.0:8080 -t doc/html/ # then browse to http://localhost:8080/
```

> ### Bookdown
>
> You can install bookdown globally using `composer global require bookdown/bookdown`. If you do
> this, make sure that `$HOME/.composer/vendor/bin` is on your `$PATH`.
