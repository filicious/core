Filicious high level object oriented filesystem abstraction for PHP
===================================================================

[![Build Status](https://travis-ci.org/Filicious/FiliciousCore.png)](https://travis-ci.org/Filicious/FiliciousCore)

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

Filesystem API
==============

`Filesystem` is the basic interface to access any filesystem.
You need a `Filesystem` instance to connect to the filesystem and get files, but not to work with it.

`File` is the basic interface to access files and directories inside of a filesystem.
A `File` instance represents a pathname inside of the underlying filesystem.
With a `File` you can do everything you like: create files and directories, delete files and directories,
read and write files and list directory content including glob'ing it.

`FS` is a *static* object, to control and access global filesystem access.
Currently `FS` only handles the system temporary filesystem.

`TemporaryFilesystem` is an extending interface of `Filesystem`.
The `TemporaryFilesystem` provides a `createTempFile` and  a `createTempDirectory` method.
All files/directories created with those methods will be deleted if the filesystem instance gets destroyed.

`Util` is a *static* object with some filesystem related methods.

`PublicUrlProvider` is an interface for a class that generates public URL's for a file.

`AbstractFile` is a basic abstract implementation of `File`.

Working with the filesystem
========================

Getting the root `/` node of a filesystem
-------------------------------------

```php
/** @var Filesystem $fs */
/** @var File $root */
$root = $fs->getRoot();
```

Getting a file from the filesystem
------------------------------

```php
/** @var Filesystem $fs */
/** @var File $file */
$file = $fs->getFile('/example.txt');
```

Testing for file existance and file types
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

Getting basic information about a file
-----------------------------------

```php
/** @var File $file */
// get the pathname INSIDE of the filesystem (this may not be the real pathname)
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

Getting and testing permissions
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

Deleting files and directories
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

Copying files
----------

Keep in mind: `$source` and `$target` do not need to be files in the same filesystem!

```php
/** @var File $source */
/** @var File $target */
$source->copyTo($target);
```

Renaming/Moving files
-----------------

Keep in mind: `$source` and `$target` do not need to be files in the same filesystem!

```php
/** @var File $source */
/** @var File $target */
$source->moveTo($target);
```

Creating a directory
------------------

```php
/** @var File $file */
if (!$file->exists()) {
	$file->mkdir();
}
```

Creating a directory path (including all missing parent directories)
------------------------------------------------------------------

```php
/** @var File $file */
if (!$file->exists()) {
	$file->mkdirs();
}
```

Creating a new empty file
-----------------------

```php
/** @var File $file */
if (!$file->exists()) {
	$file->createNewFile();
}
```

Reading and writing files
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

Truncating files
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

Calculating file hashes
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

Listing files in a directory
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

Glob'ing files in a directory
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

Iterating over directories (simple)
----------------------------

Keep in mind: the *magic* children `.` and `..` will never be visible to you!

```php
/** @var File $file */
if ($file->isDirectory()) {
	/** @var File $child */
	foreach ($file as $child) {
		// do somethink with $child
	}
}
```

Iterating over directories (advanced)
----------------------------

Keep in mind: the *magic* children `.` and `..` will never be visible to you!

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

Geting the real URL to a file
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

Geting the public URL to a file
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

The `LocalFilesystem` constructor accepts a *base path* to the root directory and an optional `PublicUrlProvider` as second argument.
All files from the `LocalFilesystem` are relative to the *base path*, even absolute files.

Merged filesystem
-----------------

A merged filesystem is similar to the [union mount](http://en.wikipedia.org/wiki/Union_mount).
Using a merged filesystem several other filesystems can be *mounted* into a virtual structure.

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

The `MergedFilesystem` constructor accepts an optional filesystem object as root (/) filesystem.

FTP filesystem
--------------

The `FTPFilesystem` allows access to an ftp server.

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

The `FTPFilesystem` constructor accepts an instance of `FTPFilesystemConfig` and an optional `PublicUrlProvider` as second argument.
The `FTPFilesystemConfig` object is used to setup the ftp configuration. The instance can be reused for several `FTPFilesystem` instances.

SSH Filesystem
--------------

work in progress...
