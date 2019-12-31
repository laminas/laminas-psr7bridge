# laminas-psr7bridge

Code for converting [PSR-7](http://www.php-fig.org/psr/psr-7/) messages to
[laminas-http](https://github.com/laminas/laminas-http) messages, and vice
versa.

**Note: This project is a work in progress.**

Initial functionality is only covering conversion of non-body request data from
PSR-7 to laminas-http in order to facilitate routing in
[mezzio](https://github.com/mezzio/mezzio); we plan to
expand this once initial work on mezzio is complete.
