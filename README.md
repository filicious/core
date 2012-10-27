Object oriented high level filesystem abstraction
=================================================

This is a high level filesystem abstraction for php,
inspired by the Java filesystem API.

Why another filesystem abstraction?
===================================

We evaluated some *filesystem abstraction* frameworks, like [Gaufrette](https://github.com/KnpLabs/Gaufrette).
But none of the frameworks we found, is a real *filesystem abstraction*.
Gaufrette for example is more a `key => value` storage, that use a filesystem or online storage as source.
Some essential functions, like delete directory are **not** available in Gaufrette.
Copying files across filesystem adapters is also **not** possible.

The benefit of `php-filesystem` is that it is a unique layer that can be...

* used every time you work with files (also for temporary files)
* used across multiple filesystem (also move or copy files between each other)
* nearly complete replace the php file api
* do **not** hide the file structure
* provide high and low level functions to the filesystem
* works with php iterators
* provide a "merged" filesystem, that build a merged structure from several filesystems
* support streaming
* provide configurable public url generation (useful for web apps)

Filesystem api
==============

`Filesystem` is the basic interface to access any filesystem.
You need a `Filesystem` instance to connect to the filesystem and get files, but not to work with it.

`File` is the basic interface to access files and directories inside of a filesystem.
A `File` instance represents a pathname inside of the underlaying filesystem.
With a `File` you can do all what you want, create files and directories, delete files and directories,
read and write files and list directory content including glob'ing it.

`FS` is an *static* object, to control and access global filesystem access.
Currently `FS` only handle the system temporary filesystem.

`TemporaryFilesystem` is an extending interface of `Filesystem`.
The `TemporaryFilesystem` provide a `createTempFile` and `createTempDirectory` method.
All files/directories created with these methods will be deleted if the filesystem gets destroyed.

`Util` is a *static* object with some filesystem related methods.

`PublicUrlProvider` is an interface for a class that generated public urls for a file.

`BasicFileImpl` is a basic abstract implementation of `File`.

Supported filesystems
=====================

Local filesystem
----------------

Allow access to the local filesystem.

```php
use bit3\filesystem\local\LocalFilesystem;
use bit3\filesystem\iterator\RecursiveFilesystemIterator;
use RecursiveTreeIterator;

// access the filesystem
$fs = new LocalFilesystem('/path/to/directory');

// create a filesystem iterator
$filesystemIterator = new RecursiveFilesystemIterator($root, FilesystemIterator::CURRENT_AS_BASENAME);

// create a tree iterator
$treeIterator = new RecursiveTreeIterator($filesystemIterator);

// output the filesystem tree
foreach ($treeIterator as $path) {
	echo $path . "\n";
}
```

The `LocalFilesystem` constructor accept a *base path* to the root directory and an optional `PublicUrlProvider` as second argument.
All files from the `LocalFilesystem` are relative to the *base path*, even absolute files.

Merged filesystem
-----------------

A merged filesystem is similar to the [union mount](http://en.wikipedia.org/wiki/Union_mount).
With the merged filesystem several other filesystems can be *mounted* into a virtual structure.

```php
use bit3\filesystem\merged\MergedFilesystem;
use bit3\filesystem\local\LocalFilesystem;
use bit3\filesystem\iterator\RecursiveFilesystemIterator;
use RecursiveTreeIterator;

// create a merged filesystem
$fs = new MergedFilesystem();

// mount some other filesystems into the structure
$fs->mount('/home', new LocalFilesystem('/path/to/directory'));
$fs->mount('/remote/server', new LocalFilesystem('/other/path'));
$fs->mount('/tmp', new LocalTemporaryFilesystem('/tmp'));

// create a filesystem iterator
$filesystemIterator = new RecursiveFilesystemIterator($root, FilesystemIterator::CURRENT_AS_BASENAME);

// create a tree iterator
$treeIterator = new RecursiveTreeIterator($filesystemIterator);

// output the filesystem tree
foreach ($treeIterator as $path) {
	echo $path . "\n";
}
```

The `MergedFilesystem` constructor accept an optional filesystem object as root (/) filesystem.

FTP filesystem
--------------

The `FtpFilesystem` allow access to an ftp server.

```php
use bit3\filesystem\ftp\FtpConfig;
use bit3\filesystem\ftp\FtpFilesystem;
use bit3\filesystem\iterator\RecursiveFilesystemIterator;
use RecursiveTreeIterator;

// create a ftp configuration
$config = new FtpConfig('example.com');
$config->setPassiveMode(true);
$config->setUsername('user');
$config->setPassword('password');
$config->setPath('/path/on/the/ftp');

// access the filesystem
$fs = new FtpFilesystem($config);

// create a filesystem iterator
$filesystemIterator = new RecursiveFilesystemIterator($root, FilesystemIterator::CURRENT_AS_BASENAME);

// create a tree iterator
$treeIterator = new RecursiveTreeIterator($filesystemIterator);

// output the filesystem tree
foreach ($treeIterator as $path) {
	echo $path . "\n";
}
```

The `FtpFilesystem` constructor accept an instance of `FtpConfig` and an optional `PublicUrlProvider` as second argument.
The `FtpConfig` object is used, to setup the ftp configuration. The instance can be reused for several `FtpFilesystem` instantiations.

SSH Filesystem
--------------

in work...
