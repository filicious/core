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

namespace Filicious;

/**
 * A simple file system object.
 * This interface provides the counter part of SimpleFile
 * all calls to the given file object instance.
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface SimpleFilesystem
	extends Filesystem
{
	/**
	 * Get the type of this file.
	 *
	 * @return int Type bitmask
	 */
	public function getTypeOf($file);

	/**
	 * Test whether this pathname is a file.
	 *
	 * @return bool
	 */
	public function isThisFile($file);

	/**
	 * Test whether this pathname is a link.
	 *
	 * @return bool
	 */
	public function isThisLink($file);

	/**
	 * Test whether this pathname is a directory.
	 *
	 * @return bool
	 */
	public function isThisDirectory($file);

	/**
	 * Get the link target of the link.
	 *
	 * @return string
	 */
	public function getLinkTargetOf($file);

	/**
	 * Returns the the path of this pathname's parent, or <em>null</em> if this pathname does not name a parent directory.
	 *
	 * @return File|null
	 */
	public function getParentOf($file);

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getAccessTimeOf($file);

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setAccessTimeOf($file, $time);

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getCreationTimeOf($file);

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getModifyTimeOf($file);

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setModifyTimeOf($file, $time);

	/**
	 * Sets access and modification time of file.
	 *
	 * @param File $file the file to modify
	 * @param int  $time
	 * @param int  $atime
	 *
	 * @return bool
	 */
	public function touch($file, $time = null, $atime = null, $doNotCreate = false);

	/**
	 * Get the size of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getSizeOf($file);

	/**
	 * Get the owner of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getOwnerOf($file);

	/**
	 * Set the owner of the file denoted by this pathname.
	 *
	 * @param string|int $user
	 *
	 * @return bool
	 */
	public function setOwnerOf($file, $user);

	/**
	 * Get the group of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getGroupOf($file);

	/**
	 * Change the group of the file denoted by this pathname.
	 *
	 * @param mixed $group
	 *
	 * @return bool
	 */
	public function setGroupOf($file, $group);

	/**
	 * Get the mode of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getModeOf($file);

	/**
	 * Set the mode of the file denoted by this pathname.
	 *
	 * @param int  $mode
	 *
	 * @return bool
	 */
	public function setModeOf($file, $mode);

	/**
	 * Test whether this pathname is readable.
	 *
	 * @return bool
	 */
	public function isThisReadable($file);

	/**
	 * Test whether this pathname is writeable.
	 *
	 * @return bool
	 */
	public function isThisWritable($file);

	/**
	 * Test whether this pathname is executeable.
	 *
	 * @return bool
	 */
	public function isThisExecutable($file);

	/**
	 * Checks whether a file or directory exists.
	 *
	 * @return bool
	 */
	public function exists($file);

	/**
	 * Delete a file or directory.
	 *
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function delete($file, $recursive = false, $force = false);

	/**
	 * Copies file
	 *
	 * @param File $destination
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function copyTo($file, File $destination, $parents = false);

	/**
	 * Renames a file or directory
	 *
	 * @param File $destination
	 *
	 * @return bool
	 */
	public function moveTo($file, File $destination);

	/**
	 * Makes directory
	 *
	 * @return bool
	 */
	public function createDirectory($file, $parents = false);

	/**
	 * Create new empty file.
	 *
	 * @return bool
	 */
	public function createFile($file, $parents = false);

	/**
	 * Get contents of the file. Returns <em>null</em> if file does not exists
	 * and <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @return string|null|bool
	 */
	public function getContentsOf($file);

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function setContentsOf($file, $content);

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function appendContentsTo($file, $content);

	/**
	 * Truncate a file to a given length. Returns the new length or
	 * <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param int $size
	 *
	 * @return int|bool
	 */
	public function truncate($file, $size = 0);

	/**
	 * Gets an stream for the file. May return <em>null</em> if streaming is not supported.
	 *
	 * @param string $mode
	 *
	 * @return resource|null
	 */
	public function open($file, $mode = 'rb');

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMENameOf($file);

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMETypeOf($file);

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMEEncodingOf($file);

	/**
	 * Calculate the md5 hash of this file.
	 * Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param bool $raw Return binary hash, instead of string hash.
	 *
	 * @return string|null
	 */
	public function getMD5Of($file, $raw = false);

	/**
	 * Calculate the sha1 hash of this file.
	 * Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param bool $raw Return binary hash, instead of string hash.
	 *
	 * @return string|null
	 */
	public function getSHA1Of($file, $raw = false);

	/**
	 * List files.
	 *
	 * @param int|string|callable Multiple list of LIST_* bitmask, glob pattern and callables to filter the list.
	 *
	 * @return array<File>
	 */
	public function lsFile();

	/**
	 * Get the real url, e.g. file:/real/path/to/file to the pathname.
	 *
	 * @return string
	 */
	public function getRealURLOf($file);

	/**
	 * Get a public url, e.g. http://www.example.com/path/to/public/file to the file.
	 *
	 * @return string
	 */
	public function getPublicURLOf($file);

	/**
	 * Get a public url, e.g. http://www.example.com/path/to/public/file to the file.
	 *
	 * @param int|string|callable Multiple list of LIST_* bitmask, glob pattern and callables to filter the list.
	 *
	 * @return int
	 */
	public function countFile();

	/**
	 * iterator for file.
	 *
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 *
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 */
	public function getIteratorOf();
}
