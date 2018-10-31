Filicious high level object oriented filesystem abstraction for PHP
===================================================================

[![Build Status](https://travis-ci.org/filicious/core.png)](https://travis-ci.org/filicious/core)

This is a high level filesystem abstraction for php,
inspired by the Java filesystem API.

Why another filesystem abstraction?
===================================

We evaluated various *filesystem abstraction* frameworks, like [Gaufrette](https://github.com/KnpLabs/Gaufrette).
But none of the frameworks we found, provides a real *filesystem abstraction*.
Gaufrette for example is more a `key => value` storage, that uses a filesystem or online storage as source.
Some essential functions, like deleting a directory are **not** available in Gaufrette.
Copying files across filesystem adapters is also **not** possible.

The benefit of `Filicious` is that it is a unique layer that...

* can be used every time you work with files (also for temporary files)
* can be used across multiple filesystems (also move or copy files between one another)
* is a nearly complete replacement for the php file API
* does **not** hide the file structure
* provides high and low level functions to the filesystem
* works with php iterators
* provides a "merged" filesystem, that builds a merged structure from several filesystems
* supports streaming
* provides configurable public url generation (useful for web apps)

Start with Filicious
====================

```php
use Filicious\Local\LocalAdapter;
use Filicious\Filesystem;

// go into your kitchen
$adapter = new LocalAdapter('/var/lib/kitchen');
$kitchen = new Filesystem($adapter);

// and grab the starter menu
$starterMenuInKitchen = $kitchen->getFile('/starter.menu');

// access the lounge
$adapter = new LocalAdapter('/var/lib/lounge');
$lounge  = new Filesystem($adapter);

// and move the starter menu from the kitchen to the lounge
$starterMenuInLounge = $lounge->getFile('/starter.menu');
$starterMenuInKitchen->moveTo($starterMenuInLounge);
```

Find out more on [filicious.github.io/how-to-use](https://filicious.github.io/how-to-use/).
