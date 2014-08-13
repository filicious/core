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

use Filicious\Exception\AdapterException;
use Filicious\Exception\FileNotFoundException;
use Filicious\File;
use Filicious\Filesystem;
use Filicious\Stream;

/**
 * The adapter provide direct access to the native filesystem.
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 */
interface Adapter
{
	/**
	 * Set the filesystem this adapter belongs to.
	 *
	 * @param Filesystem $fs The filesystem this adapter belongs to.
	 *
	 * @return static
	 */
	public function setFilesystem(Filesystem $fs);

	/**
	 * Returns the filesystem this adapter belongs to.
	 *
	 * @return Filesystem The filesystem this adapter belongs to
	 */
	public function getFilesystem();

	/**
	 * Set the root adapter of the filesystem this adapter belongs to.
	 *
	 * @param RootAdapter $root The filesystems root adapter
	 *
	 * @return static
	 */
	public function setRootAdapter(RootAdapter $root);

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
	 *
	 * @return static
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
	 *
	 * @param Pathname $pathname     The pathname to resolve local path from
	 * @param Adapter  $localAdapter The local adapter
	 * @param string   $local        The adapter local path
	 *
	 * @return static
	 */
	public function resolveLocal(Pathname $pathname, &$localAdapter, &$local);

	/**
	 * Tests whether the file denoted by the given pathname exists and is a
	 * file.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return bool True, if the file exists and is a file; otherwise false
	 *
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function isFile(Pathname $pathname);

	/**
	 * Tests whether the file denoted by the given pathname exists and is a
	 * directory.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return bool True, if the file exists and is a directory; otherwise false
	 *
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function isDirectory(Pathname $pathname);

	/**
	 * Tests whether the file denoted by the given pathname exists and is a
	 * link.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return bool True, if the file exists and is a link; otherwise false
	 *
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function isLink(Pathname $pathname);

	/**
	 * Returns the time of the file named by the given pathname was accessed
	 * last time.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return \DateTime
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function getAccessTime(Pathname $pathname);

	/**
	 * Sets the access time of the file named by the given pathname.
	 *
	 * @param Pathname  $pathname The full abstracted pathname
	 * @param \DateTime $accessTime
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function setAccessTime(Pathname $pathname, \DateTime $accessTime);

	/**
	 * Returns the time of the file named by the given pathname at which it was
	 * created.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return \DateTime The creation time of the file
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function getCreationTime(Pathname $pathname);

	/**
	 * Returns the time of the file named by the given pathname was modified
	 * last time.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return \DateTime The modify time of the file
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function getModifyTime(Pathname $pathname);

	/**
	 * Sets the modify time of the file named by the given pathname.
	 *
	 * @param Pathname  $pathname   The full abstracted pathname
	 * @param \DateTime $modifyTime The new modify time to set
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function setModifyTime(Pathname $pathname, \DateTime $modifyTime);

	/**
	 * Sets access and modify time of file, optionally creating the file, if it
	 * does not exists yet.
	 *
	 * @param Pathname  $pathname   The full abstracted pathname
	 * @param \DateTime $modifyTime The new modify time to set
	 * @param \DateTime $accessTime The new access time to set; If null then $modifyTime will be used
	 * @param bool      $create     Whether to create the file, if it does not already exists
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does not exists and $create is set to false
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function touch(Pathname $pathname, \DateTime $modifyTime, \DateTime $accessTime, $create);

	/**
	 * Get the size of the file named by the given pathname.
	 *
	 * @param Pathname $pathname  The full abstracted pathname
	 * @param bool     $recursive Whether or not to calculate the size of
	 *                            directories.
	 *
	 * @return int The size of the file
	 *
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function getSize(Pathname $pathname, $recursive);

	/**
	 * Get the owner of the file named by the given pathname.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return string|int
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function getOwner(Pathname $pathname);

	/**
	 * Set the owner of the file named by the given pathname.
	 *
	 * @param Pathname   $pathname The full abstracted pathname
	 * @param string|int $user
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function setOwner(Pathname $pathname, $user);

	/**
	 * Get the group of the file named by the given pathname.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return string|int
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function getGroup(Pathname $pathname);

	/**
	 * Change the group of the file named by the given pathname.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 * @param mixed    $group
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function setGroup(Pathname $pathname, $group);

	/**
	 * Get the mode of the file named by the given pathname.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return int TODO mode representation type?
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function getMode(Pathname $pathname);

	/**
	 * Set the mode of the file named by the given pathname.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 * @param int      $mode     TODO mode representation type?
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function setMode(Pathname $pathname, $mode);

	/**
	 * Tests whether the file named by the given pathname is readable.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return bool True, if the file exists and is readable; otherwise false
	 *
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function isReadable(Pathname $pathname);

	/**
	 * Tests whether the file named by the given pathname is writable.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return bool True, if the file exists and is writable; otherwise false
	 *
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function isWritable(Pathname $pathname);

	/**
	 * Tests whether the file named by the given pathname is executeable.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return bool True, if the file exists and is executable; otherwise false
	 *
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function isExecutable(Pathname $pathname);

	/**
	 * Checks whether a file or directory exists.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return bool
	 *
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function exists(Pathname $pathname);

	/**
	 * Delete a file or directory.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 * @param bool     $recursive
	 * @param bool     $force
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function delete(Pathname $pathname, $recursive, $force);

	/**
	 * Copies file
	 *
	 * TODO list valid flags
	 *
	 * @param Pathname $srcPathname The full abstracted pathname
	 * @param Pathname $dstPathname The full abstracted destination pathname
	 * @param int      $flags       Flags to control the operations behavior
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function copyTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags
	);

	/**
	 * Copies file
	 *
	 * TODO list valid flags
	 *
	 * @param Pathname $dstPathname The full abstracted pathname
	 * @param Pathname $srcPathname The full abstracted pathname of the source from which will be copied
	 * @param int      $flags       Flags to control the operations behavior
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does already exists and the OVERWRITE_REJECT flag is set
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function copyFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags
	);

	/**
	 * Renames a file or directory
	 *
	 * TODO list valid flags
	 *
	 * @param Pathname $srcPathname The full abstracted pathname
	 * @param Pathname $dstPathname The full abstracted destination pathname
	 * @param int      $flags       Flags to control the operations behavior
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function moveTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags
	);

	/**
	 * Renames a file or directory
	 *
	 * TODO list valid flags
	 *
	 * @param Pathname $dstPathname The full abstracted pathname
	 * @param Pathname $srcPathname The full abstracted pathname of the source from which will be copied
	 * @param int      $flags       Flags to control the operations behavior
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does already exists and the OVERWRITE_REJECT flag is set
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function moveFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags
	);

	/**
	 * Makes directory
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 * @param bool     $parents
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does already exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function createDirectory(Pathname $pathname, $parents);

	/**
	 * Create new empty file.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 * @param bool     $parents
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does already exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function createFile(Pathname $pathname, $parents);

	/**
	 * Get contents of the file. Returns <em>null</em> if file does not exists
	 * and <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return string
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function getContents(Pathname $pathname);

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 * @param string   $content
	 * @param bool     $create
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does not exists and $create is set to false
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function setContents(Pathname $pathname, $content, $create);

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 * @param string   $content
	 * @param bool     $create
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does not exists and $create is set to false
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function appendContents(Pathname $pathname, $content, $create);

	/**
	 * Truncate a file to a given length. Returns the new length or
	 * <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 * @param int      $size
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function truncate(Pathname $pathname, $size);

	/**
	 * Gets an stream for the file. May return <em>null</em> if streaming is not supported.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return Stream
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function getStream(Pathname $pathname);

	/**
	 * Get the real url, e.g. file:/real/path/to/file to the pathname.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return string
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function getStreamURL(Pathname $pathname);

	/**
	 * Returns all filenames of all (direct) children.
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 *
	 * @return File[]
	 *
	 * @throws FileNotFoundException If the file does not exists or is not a directory
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function ls(Pathname $pathname);

	/**
	 * TODO
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 * @param array    $filter   An array of filters to apply
	 *
	 * @return int The amount of child nodes of the pathname
	 *
	 * @throws FileNotFoundException If the file does not exists or is not a directory
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function count(Pathname $pathname, array $filter);

	/**
	 * TODO
	 *
	 * @param Pathname $pathname The full abstracted pathname
	 * @param array    $filter   An array of filters to apply
	 *
	 * @return \Iterator An iterator which iterates over the matched child nodes
	 *
	 * @throws FileNotFoundException If the file does not exists or is not a  directory
	 * @throws AdapterException If the access to the underlying filesystem fails due to technical reasons like connection problems or timeouts
	 */
	public function getIterator(Pathname $pathname, array $filter);
}
