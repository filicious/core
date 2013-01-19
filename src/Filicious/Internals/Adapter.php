<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 * @link    http://filicious.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Filicious\Internals;

use Filicious\Filesystem;

if(DIRECTORY_SEPARATOR !== '/') {
	/**
	 * A fast dirname function, which only accepts non-zero-length strings
	 * with at least one "/" in it.
	 * Only this type of path strings is used within adapter.
	 *
	 * @param string $path
	 */
	function dirname($path) {
		return substr($path, 0, strrpos($path, '/'));
	}
}

/**
 * 
 * 
 * An full abstracted pathname is the name of a file within the Filicous
 * filesystem the file belongs to.
 * 
 * An adapter local path is the name of a file of a specific adapter. It is
 * calculated by applying the mapping rules of intermediate adapters.
 * 
 * @package filicious-core
 * @author  Oliver Hoff <oliver@hofff.com>
 */
interface Adapter
{
	/**
	 * Return the configuration of this adapter.
	 *
	 * @return \Filicious\FilesystemConfig
	 */
	public function getConfig();

	/**
	 * Set the filesystem this adapter belongs to.
	 *
	 * @param Filicious\Filesystem $fs The filesystem this adapter belongs to
	 */
	public function setFilesystem(Filesystem $fs);
	
	/**
	 * Returns the filesystem this adapter belongs to.
	 * 
	 * @return Filicious\Filesystem The filesystem this adapter belongs to
	 */
	public function getFilesystem();

	/**
	 * Set the root adapter of the filesystem this adapter belongs to.
	 *
	 * @param Adapter $root The filesystems root adapter
	 */
	public function setRootAdapter(Adapter $root);
	
	/**
	 * Returns the root adapter of the filesystem this adapter belongs to.
	 * 
	 * @return Adapter The filesystems root adapter
	 */
	public function getRootAdapter();

	/**
	 * Set the parent adapter for this adapter.
	 *
	 * @param Adapter|null $parent The parent adapter
	 */
	public function setParentAdapter(Adapter $parent);
	
	/**
	 * Returns the parent adapter of this adapter, if any.
	 * 
	 * @return Adapter|null The parent adapter
	 */
	public function getParentAdapter();

	/**
	 * Resolve the local path
	 * @param Pathname $pathname The pathname to resolve local path from
	 * @param Adapter  $localAdapter The local adapter
	 * @param string   $local The adapter local path
	 */
	public function resolveLocal(Pathname $pathname, &$localAdapter, &$local);

	/**
	 * Tests whether the file denoted by the given pathname exists and is a
	 * file.
	 * 
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return bool True, if the file exists and is a file; otherwise false
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function isFile(Pathname $pathname);
	
	/**
	 * Tests whether the file denoted by the given pathname exists and is a
	 * directory.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return bool True, if the file exists and is a directory; otherwise false
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function isDirectory(Pathname $pathname);
	
	/**
	 * Tests whether the file denoted by the given pathname exists and is a
	 * link.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return bool True, if the file exists and is a link; otherwise false
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function isLink(Pathname $pathname);
	
	/**
	 * Returns the time of the file named by the given pathname was accessed
	 * last time.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return \DateTime
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getAccessTime(Pathname $pathname);
	
	/**
	 * Sets the access time of the file named by the given pathname.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param \DateTime $atime 
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function setAccessTime(Pathname $pathname, \DateTime $atime);
	
	/**
	 * Returns the time of the file named by the given pathname at which it was
	 * created.
	 * 
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return \DateTime The creation time of the file
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getCreationTime(Pathname $pathname);
	
	/**
	 * Returns the time of the file named by the given pathname was modified
	 * last time.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return \DateTime The modify time of the file
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getModifyTime(Pathname $pathname);
	
	/**
	 * Sets the modify time of the file named by the given pathname.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param \DateTime $mtime The new modify time to set
	 * @return void
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function setModifyTime(Pathname $pathname, \DateTime $mtime);
	
	/**
	 * Sets access and modify time of file, optionally creating the file, if it
	 * does not exists yet.
	 * 
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param \DateTime $time The new modify time to set
	 * @param \DateTime $atime The new access time to set; If null then $time
	 * 		will be used
	 * @param bool $create Whether to create the file, if it does not already
	 * 		exists
	 * @return void
	 * @throws FileStateException If the file does not exists and $create is set
	 * 		to false
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function touch(Pathname $pathname, \DateTime $time, \DateTime $atime, $create);
	
	/**
	 * Get the size of the file named by the given pathname.
	 * 
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param bool $recursive Whether or not to calculate the size of
	 * 		directories.
	 * @return numeric The size of the file
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getSize(Pathname $pathname, $recursive);
	
	/**
	 * Get the owner of the file named by the given pathname.
	 * 
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return string|int
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getOwner(Pathname $pathname);
	
	/**
	 * Set the owner of the file named by the given pathname.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param string|int $user
	 * @return void
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function setOwner(Pathname $pathname, $user);
	
	/**
	 * Get the group of the file named by the given pathname.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return string|int
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getGroup(Pathname $pathname);
	
	/**
	 * Change the group of the file named by the given pathname.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param mixed $group
	 * @return void
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function setGroup(Pathname $pathname, $group);
	
	/**
	 * Get the mode of the file named by the given pathname.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return int TODO mode representation type?
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getMode(Pathname $pathname);
	
	/**
	 * Set the mode of the file named by the given pathname.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param int $mode TODO mode representation type?
	 * @return void
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function setMode(Pathname $pathname, $mode);
	
	/**
	 * Tests whether the file named by the given pathname is readable.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return bool True, if the file exists and is readable; otherwise false
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function isReadable(Pathname $pathname);
	
	/**
	 * Tests whether the file named by the given pathname is writable.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return bool True, if the file exists and is writable; otherwise false
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function isWritable(Pathname $pathname);
	
	/**
	 * Tests whether the file named by the given pathname is executeable.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return bool True, if the file exists and is executable; otherwise false
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function isExecutable(Pathname $pathname);
	
	/**
	 * Checks whether a file or directory exists.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return bool
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function exists(Pathname $pathname);
	
	/**
	 * Delete a file or directory.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param bool $recursive
	 * @param bool $force
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function delete(Pathname $pathname, $recursive, $force);
	
	/**
	 * Copies file
	 * 
	 * TODO list valid flags
	 *
	 * @param string $srcPathname The full abstracted pathname
	 * @param string $srcLocal The adapter local path
	 * @param Adapter $dstAdapter The root adapter of destination filesystem
	 * @param string $dstPathname The full abstracted destination pathname
	 * @param int $flags Flags to control the operations behavior
	 * @return void
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function copyTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags);
	
	/**
	 * Copies file
	 * 
	 * TODO list valid flags
	 *
	 * @param string $dstPathname The full abstracted pathname
	 * @param string $dstLocal The adapter local path
	 * @param Adapter $srcAdapter
	 * @param string $srcPathname The full abstracted pathname of the source
	 * 		from which will be copied
	 * @param string $srcLocal The adapter local path of source from which
	 * 		will be copied
	 * @param int $flags Flags to control the operations behavior
	 * @return void
	 * @throws FileStateException If the file does already exists and the
	 * 		OVERWRITE_REJECT flag is set
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function copyFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags);
	
	/**
	 * Renames a file or directory
	 *
	 * TODO list valid flags
	 * 
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param Adapter $dstAdapter The root adapter of destination filesystem
	 * @param string $dstPathname The full abstracted destination pathname
	 * @param int $flags Flags to control the operations behavior
	 * @return void
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function moveTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags);
	
	/**
	 * Renames a file or directory
	 *
	 * TODO list valid flags
	 * 
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param Adapter $srcAdapter
	 * @param string $srcPathname The full abstracted pathname of the source
	 * 		from which will be copied
	 * @param string $srcLocal The adapter local path of source from which
	 * 		will be copied
	 * @param int $flags Flags to control the operations behavior
	 * @return void
	 * @throws FileStateException If the file does already exists and the
	 * 		OVERWRITE_REJECT flag is set
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function moveFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags);
	
	/**
	 * Makes directory
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param bool $parents
	 * @return void
	 * @throws FileStateException If the file does already exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function createDirectory(Pathname $pathname, $parents);
	
	/**
	 * Create new empty file.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param bool $parents
	 * @return void
	 * @throws FileStateException If the file does already exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function createFile(Pathname $pathname, $parents);
	
	/**
	 * Get contents of the file. Returns <em>null</em> if file does not exists
	 * and <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return string
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getContents(Pathname $pathname);
	
	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param string $content
	 * @param bool $create
	 * @return void
	 * @throws FileStateException If the file does not exists and $create is set
	 * 		to false
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function setContents(Pathname $pathname, $content, $create);
	
	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param string $content
	 * @param bool $create
	 * @return void
	 * @throws FileStateException If the file does not exists and $create is set
	 * 		to false
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function appendContents(Pathname $pathname, $content, $create);
	
	/**
	 * Truncate a file to a given length. Returns the new length or
	 * <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param int $size
	 * @return void
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function truncate(Pathname $pathname, $size);
	
	/**
	 * Gets an stream for the file. May return <em>null</em> if streaming is not supported.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @return Stream
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getStream(Pathname $pathname);
	
	/**
	 * Get the real url, e.g. file:/real/path/to/file to the pathname.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return string
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getStreamURL(Pathname $pathname);
	
	/**
	 * Get mime content type.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return string
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getMIMEName(Pathname $pathname);
	
	/**
	 * Get mime content type.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return string
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getMIMEType(Pathname $pathname);
	
	/**
	 * Get mime content type.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return string
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getMIMEEncoding(Pathname $pathname);
	
	/**
	 * Calculate the md5 hash of this file.
	 * Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param bool $binary Return binary hash, instead of string hash
	 * @return string
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getMD5(Pathname $pathname, $binary);
	
	/**
	 * Calculate the sha1 hash of this file.
	 * Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param bool $binary Return binary hash, instead of string hash
	 * @return string
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getSHA1(Pathname $pathname, $binary);
	
	/**
	 * Returns all filenames of all (direct) children.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return array<Filicious\File>
	 * @throws FileStateException If the file does not exists or is not a
	 * 		directory
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function ls(Pathname $pathname);
	
	/**
	 * TODO
	 * 
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param array An array of filters to apply
	 * @return int The amount of child nodes of the pathname
	 * @throws FileStateException If the file does not exists or is not a
	 * 		directory
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function count(Pathname $pathname, array $filter);
	
	/**
	 * TODO
	 * 
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param array An array of filters to apply
	 * @return \Iterator An iterator which iterates over the matched child nodes
	 * @throws FileStateException If the file does not exists or is not a
	 * 		directory
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getIterator(Pathname $pathname, array $filter);
	
	/**
	 * Returns the available space of the disk or partition or system
	 * the directory denoted by pathname resides on.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return float The amount of free space available in bytes
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getFreeSpace(Pathname $pathname);
	
	/**
	 * Returns the total size of the disk or partition or system the directory
	 * denoted by pathname resides on.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return float The total size in bytes
	 * @throws FileStateException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getTotalSpace(Pathname $pathname);

	/**
	 * Check if the pathname exists and throw an exception if not.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return void
	 * @throws Filicious\Exception\FileNotFoundException
	 */
	public function requireExists(Pathname $pathname);

	/**
	 * Check if the pathname is a file and throw an exception if not.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return void
	 * @throws Filicious\Exception\NotAFileException
	 */
	public function checkFile(Pathname $pathname);

	/**
	 * Check if the pathname is a directory and throw an exception if not.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return void
	 * @throws Filicious\Exception\NotADirectoryException
	 */
	public function checkDirectory(Pathname $pathname);

	/**
	 * Notify about config changes.
	 */
	public function notifyConfigChange();
}
