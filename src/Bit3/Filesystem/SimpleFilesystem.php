<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem;

/**
 * A simple file system object.
 * This interface provides the counter part of SimpleFile
 * all calls to the given file object instance.
 *
 * @package php-filesystem
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
	public function getTypeOf($objFile);

	/**
	 * Test whether this pathname is a file.
	 *
	 * @return bool
	 */
	public function isThisFile($objFile);

	/**
	 * Test whether this pathname is a link.
	 *
	 * @return bool
	 */
	public function isThisLink($objFile);

	/**
	 * Test whether this pathname is a directory.
	 *
	 * @return bool
	 */
	public function isThisDirectory($objFile);

	/**
	 * Get the link target of the link.
	 *
	 * @return string
	 */
	public function getLinkTargetOf($objFile);

	/**
	 * Returns the the path of this pathname's parent, or <em>null</em> if this pathname does not name a parent directory.
	 *
	 * @return File|null
	 */
	public function getParentOf($objFile);

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getAccessTimeOf($objFile);

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setAccessTimeOf($objFile, $time);

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getCreationTimeOf($objFile);

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getModifyTimeOf($objFile);

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setModifyTimeOf($objFile, $time);

	/**
	 * Sets access and modification time of file.
	 *
	 * @param File $objFile the file to modify
	 * @param int  $time
	 * @param int  $atime
	 *
	 * @return bool
	 */
	public function touch($objFile, $time = null, $atime = null, $doNotCreate = false);

	/**
	 * Get the size of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getSizeOf($objFile);

	/**
	 * Get the owner of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getOwnerOf($objFile);

	/**
	 * Set the owner of the file denoted by this pathname.
	 *
	 * @param string|int $user
	 *
	 * @return bool
	 */
	public function setOwnerOf($objFile, $user);

	/**
	 * Get the group of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getGroupOf($objFile);

	/**
	 * Change the group of the file denoted by this pathname.
	 *
	 * @param mixed $group
	 *
	 * @return bool
	 */
	public function setGroupOf($objFile, $group);

	/**
	 * Get the mode of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getModeOf($objFile);

	/**
	 * Set the mode of the file denoted by this pathname.
	 *
	 * @param int  $mode
	 *
	 * @return bool
	 */
	public function setModeOf($objFile, $mode);

	/**
	 * Test whether this pathname is readable.
	 *
	 * @return bool
	 */
	public function isThisReadable($objFile);

	/**
	 * Test whether this pathname is writeable.
	 *
	 * @return bool
	 */
	public function isThisWritable($objFile);

	/**
	 * Test whether this pathname is executeable.
	 *
	 * @return bool
	 */
	public function isThisExecutable($objFile);

	/**
	 * Checks whether a file or directory exists.
	 *
	 * @return bool
	 */
	public function exists($objFile);

	/**
	 * Delete a file or directory.
	 *
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function delete($objFile, $recursive = false, $force = false);

	/**
	 * Copies file
	 *
	 * @param File $destination
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function copyTo($objFile, File $destination, $parents = false);

	/**
	 * Renames a file or directory
	 *
	 * @param File $destination
	 *
	 * @return bool
	 */
	public function moveTo($objFile, File $destination);

	/**
	 * Makes directory
	 *
	 * @return bool
	 */
	public function createDirectory($objFile, $parents = false);

	/**
	 * Create new empty file.
	 *
	 * @return bool
	 */
	public function createFile($objFile, $parents = false);

	/**
	 * Get contents of the file. Returns <em>null</em> if file does not exists
	 * and <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @return string|null|bool
	 */
	public function getContentsOf($objFile);

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function setContentsOf($objFile, $content);

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function appendContentsTo($objFile, $content);

	/**
	 * Truncate a file to a given length. Returns the new length or
	 * <em>false</em> on error (e.a. if file is a directory).
	 * @param int $size
	 *
	 * @return int|bool
	 */
	public function truncate($objFile, $size = 0);

	/**
	 * Gets an stream for the file. May return <em>null</em> if streaming is not supported.
	 *
	 * @param string $mode
	 *
	 * @return resource|null
	 */
	public function open($objFile, $mode = 'rb');

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMENameOf($objFile);

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMETypeOf($objFile);

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMEEncodingOf($objFile);

	/**
	 * Calculate the md5 hash of this file.
	 * Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param bool $raw Return binary hash, instead of string hash.
	 *
	 * @return string|null
	 */
	public function getMD5Of($objFile, $raw = false);

	/**
	 * Calculate the sha1 hash of this file.
	 * Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param bool $raw Return binary hash, instead of string hash.
	 *
	 * @return string|null
	 */
	public function getSHA1Of($objFile, $raw = false);

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
	public function getRealURLOf($objFile);

	/**
	 * Get a public url, e.g. http://www.example.com/path/to/public/file to the file.
	 *
	 * @return string
	 */
	public function getPublicURLOf($objFile);

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
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 *
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 */
	public function getIteratorOf();
}
