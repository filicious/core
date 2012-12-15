Filicious high level object oriented filesystem abstraction for PHP
===================================================================

[![Build Status](https://travis-ci.org/Filicious/FiliciousCore.png)](https://travis-ci.org/Filicious/FiliciousCore)

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

`AbstractFile` is a basic abstract implementation of `File`.

Work with the filesystem
========================

Get the root `/` node of a filesystem
-------------------------------------

```php
/** @var Filesystem $fs */
/** @var File $root */
$root = $fs->getRoot();
```

Get a file from the filesystem
------------------------------

```php
/** @var Filesystem $fs */
/** @var File $file */
$file = $fs->getFile('/example.txt');
```

Test if file exists and test if it is a file, directory or link
---------------------------------------------------------------

```php
/** @var File $file */
if ($file->exists()) {
	if ($file->isLink()) {
		// $file is a link
	}
	if ($file->isFile()) {
		// $file is a file
	}
	if ($file->isDirectory()) {
		// $file is a directory
	}
}
```

Get basic informations about a file
-----------------------------------

```php
/** @var File $file */
// get the passname INSIDE of the filesystem (this may not be the real pathname)
$pathname = $file->getPathname();

// get the basename
$basename = $file->getBasename();

// the the extension
$extension = $file->getExtension();

// get the parent directory
/** @var File $parent */
$parent = $file->getParent();

// get last access time
$accessTime = $file->getAccessTime();

// get creation time
$creationTime = $file->getCreationTime();

// get last modified time
$lastModified = $file->getLastModified();

// get file size
$size = $file->getSize();

// get owner (may be the name or uid)
$owner = $file->getOwner();

// get group (may be the name or gid)
$group = $file->getGroup();
```

Get and test permissions
------------------------

```php
/** @var File $file */
// get permissions
$mode = $file->getMode();

// test if file is readable
if ($file->isReadable()) {
	// do something...
}

// test if file is writeable
if ($file->isWriteable()) {
	// do something...
}

// test if file is executable
if ($file->isExecutable()) {
	// do something...
}
```

Delete files and directories
----------------------------

```php
/** @var File $file */
if ($file->isDirectory()) {
	$file->delete(true); // recursive delete!!!
}
else {
	$file->delete();
}
```

Copy files
----------

Keep in mind: `$source` and `$target` does not need to be files in the same filesystem!

```php
/** @var File $source */
/** @var File $target */
$source->copyTo($target);
```

Rename/Move files
-----------------

Keep in mind: `$source` and `$target` does not need to be files in the same filesystem!

```php
/** @var File $source */
/** @var File $target */
$source->moveTo($target);
```

Create a directory
------------------

```php
/** @var File $file */
if (!$file->exists()) {
	$file->mkdir();
}
```

Create a directory path (including all missing parent directories)
------------------------------------------------------------------

```php
/** @var File $file */
if (!$file->exists()) {
	$file->mkdirs();
}
```

Create a new empty file
-----------------------

```php
/** @var File $file */
if (!$file->exists()) {
	$file->createNewFile();
}
```

Read and write files
--------------------

```php
/** @var File $file */
// read the file
$content = $file->getContents();

// write to the file
$file->setContents("Hello world!\n");

// append to the file
$file->appendContents("The world is like a pizza!\n");
```

Truncate files
--------------

```php
/** @var File $file */
$file->truncate(1024); // truncate to 1024 bytes
```

Streaming files
---------------

```php
/** @var File $file */
// read the file
$stream = $file->openStream('rb');
$content = stream_get_contents($stream);
fclose($stream);

// write to the file
$stream = $file->openStream('wb');
fwrite($stream, "Hello world!\n");
fclose($stream);

// append to the file
$stream = $file->openStream('ab');
fwrite($stream, "The world is like a pizza!\n");
fclose($stream);
```

Calculate file hashes
---------------------

```php
/** @var File $file */
// get md5 hash
$md5 = $file->hashMD5();

// get raw md5 hash
$md5raw = $file->hashMD5(true);

// get sha1 hash
$sha1 = $file->hashSHA1();

// get raw sha1 hash
$sha1raw = $file->hashSHA1(true);
```

List files in a directory
-------------------------

```php
/** @var File $file */
if ($file->isDirectory()) {
	// get files and directories
	$children = $file->listAll();

	// get files only
	$files = $file->ls();

	// get directories only
	$directories = $file->listDirectories();
}
```

Glob files in a directory
-------------------------

```php
/** @var File $file */
if ($file->isDirectory()) {
	// get files and directories
	$children = $file->glob('*example*');

	// get files only
	$files = $file->globFiles('*example*');

	// get directories only
	$directories = $file->globDirectories('*example*');
}
```

Iterate directories (simple)
----------------------------

Keep in mind: the *magic* childrens `.` and `..` will never be visible to you!

```php
/** @var File $file */
if ($file->isDirectory()) {
	/** @var File $child */
	foreach ($file as $child) {
		// do somethink with $child
	}
}
```

Iterate directories (expert)
----------------------------

Keep in mind: the *magic* childrens `.` and `..` will never be visible to you!

```php
use Bit3\Filesystem\Iterator\FilesystemIterator;

/** @var File $file */
if ($file->isDirectory()) {
	$iterator = new FilesystemIterator($file, FilesystemIterator::CURRENT_AS_PATHNAME);

	/** @var string $child */
	foreach ($file as $child) {
		// $child will be the pathname
	}
}
```

Get real url to a file
----------------------

```php
/** @var File $file */
$url = $file->getRealUrl();
// -> file:/real/path/to/file
// or
// -> ftp://username:password@host:port/path/to/file
// or
// ...
```

Get public url to a file
------------------------

```php
/** @var File $file */
$url = $file->getPublicUrl();
// may return false|null if no public url is available
if ($url) {
	header('Location: ' . $url);
}
```

Supported filesystems
=====================

Local filesystem
----------------

Allow access to the local filesystem.

```php
use Bit3\Filesystem\Local\LocalFilesystem;
use Bit3\Filesystem\Iterator\RecursiveFilesystemIterator;
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
use Bit3\Filesystem\Merged\MergedFilesystem;
use Bit3\Filesystem\Local\LocalFilesystem;
use Bit3\Filesystem\Iterator\RecursiveFilesystemIterator;
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

The `FTPFilesystem` allow access to an ftp server.

```php
use Bit3\Filesystem\FTP\FTPFilesystemConfig;
use Bit3\Filesystem\FTP\FTPFilesystem;
use Bit3\Filesystem\Iterator\RecursiveFilesystemIterator;
use RecursiveTreeIterator;

// create a ftp configuration
$config = new FTPFilesystemConfig('example.com');
$config->setPassiveMode(true);
$config->setUsername('user');
$config->setPassword('password');
$config->setPath('/path/on/the/ftp');

// access the filesystem
$fs = new FTPFilesystem($config);

// create a filesystem iterator
$filesystemIterator = new RecursiveFilesystemIterator($root, FilesystemIterator::CURRENT_AS_BASENAME);

// create a tree iterator
$treeIterator = new RecursiveTreeIterator($filesystemIterator);

// output the filesystem tree
foreach ($treeIterator as $path) {
	echo $path . "\n";
}
```

The `FTPFilesystem` constructor accept an instance of `FTPFilesystemConfig` and an optional `PublicUrlProvider` as second argument.
The `FTPFilesystemConfig` object is used, to setup the ftp configuration. The instance can be reused for several `FTPFilesystem` instantiations.

SSH Filesystem
--------------

in work...
