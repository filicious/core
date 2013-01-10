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
	 * Returns the filesystem this adapter belongs to.
	 * 
	 * @return Filicious\Filesystem The filesystem this adapter belongs to
	 */
	public function getFilesystem();
	
	/**
	 * Returns the root adapter of the filesystem this adapter belongs to.
	 * 
	 * @return Adapter The filesystems root adapter
	 */
	public function getRootAdapter();
	
	/**
	 * Returns the parent adapter of this adapter, if any.
	 * 
	 * @return Adapter|null The parent adapter
	 */
	public function getParentAdapter();
	
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
	public function isFile($pathname, $local);
	
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
	public function isDirectory($pathname, $local);
	
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
	public function isLink($pathname, $local);
	
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
	public function getAccessTime($pathname, $local);
	
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
	public function setAccessTime($pathname, $local, \DateTime $atime);
	
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
	public function getCreationTime($pathname, $local);
	
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
	public function getModifyTime($pathname, $local);
	
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
	public function setModifyTime($pathname, $local, \DateTime $mtime);
	
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
	public function touch($pathname, $local, \DateTime $time, \DateTime $atime, $create);
	
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
	public function getSize($pathname, $local, $recursive);
	
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
	public function getOwner($pathname, $local);
	
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
	public function setOwner($pathname, $local, $user);
	
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
	public function getGroup($pathname, $local);
	
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
	public function setGroup($pathname, $local, $group);
	
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
	public function getMode($pathname, $local);
	
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
	public function setMode($pathname, $local, $mode);
	
	/**
	 * Tests whether the file named by the given pathname is readable.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return bool True, if the file exists and is readable; otherwise false
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function isReadable($pathname, $local);
	
	/**
	 * Tests whether the file named by the given pathname is writable.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return bool True, if the file exists and is writable; otherwise false
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function isWritable($pathname, $local);
	
	/**
	 * Tests whether the file named by the given pathname is executeable.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return bool True, if the file exists and is executable; otherwise false
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function isExecutable($pathname, $local);
	
	/**
	 * Checks whether a file or directory exists.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return bool
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function exists($pathname, $local);
	
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
	public function delete($pathname, $local, $recursive, $force);
	
	/**
	 * Copies file
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
	public function copyTo(
		$pathname, $local,
		Adapter $dstAdapter, $dstPathname,
		$flags);
	
	/**
	 * Copies file
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
	public function copyFrom(
		$pathname, $local,
		Adapter $srcAdapter, $srcPathname, $srcLocal,
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
		$pathname, $local,
		Adapter $dstAdapter, $dstPathname,
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
		$pathname, $local, 
		Adapter $srcAdapter, $srcPathname, $srcLocal,
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
	public function createDirectory($pathname, $local, $parents);
	
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
	public function createFile($pathname, $local, $parents);
	
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
	public function getContents($pathname, $local);
	
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
	public function setContents($pathname, $local, $content, $create);
	
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
	public function appendContents($pathname, $local, $content, $create);
	
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
	public function truncate($pathname, $local, $size);
	
	/**
	 * Gets an stream for the file. May return <em>null</em> if streaming is not supported.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @param string $mode
	 * @return resource TODO return stream object?
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function open($pathname, $local, $mode);
	
	/**
	 * Get the real url, e.g. file:/real/path/to/file to the pathname.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return string
	 * @throws AdapterException If the access to the underlying filesystem fails
	 * 		due to technical reasons like connection problems or timeouts
	 */
	public function getStreamURL($pathname, $local);
	
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
	public function getMIMEName($pathname, $local);
	
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
	public function getMIMEType($pathname, $local);
	
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
	public function getMIMEEncoding($pathname, $local);
	
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
	public function getMD5($pathname, $local, $binary);
	
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
	public function getSHA1($pathname, $local, $binary);
	
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
	public function ls($pathname, $local);
	
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
	public function count($pathname, $local, array $filter);
	
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
	public function getIterator($pathname, $local, array $filter);
	
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
	public function getFreeSpace($pathname, $local);
	
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
	public function getTotalSpace($pathname, $local);

	/**
	 * Check if the pathname exists and throw an exception if not.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return void
	 * @throws Filicious\Exception\FileNotFoundException
	 */
	public function requireExists($pathname, $local);

	/**
	 * Check if the pathname is a file and throw an exception if not.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return void
	 * @throws Filicious\Exception\NotAFileException
	 */
	public function checkFile($pathname, $local);

	/**
	 * Check if the pathname is a directory and throw an exception if not.
	 *
	 * @param string $pathname The full abstracted pathname
	 * @param string $local The adapter local path
	 * @return void
	 * @throws Filicious\Exception\NotADirectoryException
	 */
	public function checkDirectory($pathname, $local);
}
