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

use SplFileInfo;
use Traversable;
use IteratorAggregate;
use ArrayIterator;

/**
 * A file object
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
interface File
	extends \IteratorAggregate, \Countable
{
	/**
	 * File type.
	 */
	const TYPE_FILE = 0x1;

	/**
	 * Directory type.
	 */
	const TYPE_DIRECTORY = 0x2;

	/**
	 * Link type.
	 */
	const TYPE_LINK = 0x4;

	/**
	 * List everything (including "." and "..")
	 */
	const LIST_ALL = 1;

	/**
	 * Return hidden files (starting with ".")
	 */
	const LIST_HIDDEN = 2;

	/**
	 * Return non-hidden (not starting with ".")
	 */
	const LIST_VISIBLE = 4;

	/**
	 * Return only files.
	 */
	const LIST_FILES = 128;

	/**
	 * Return only directories.
	 */
	const LIST_DIRECTORIES = 256;

	/**
	 * Return only links.
	 */
	const LIST_LINKS = 512;

	/**
	 * List non-links.
	 */
	const LIST_OPAQUE = 1024;

	/**
	 * List recursive.
	 */
	const LIST_RECURSIVE = 8192;

	/**
	 * Get the underlaying filesystem for this pathname.
	 *
	 * @return Filesystem
	 */
	public function getFilesystem();

	/**
	 * Test whether this pathname is a file.
	 *
	 * @return bool
	 */
	public function isFile();

	/**
	 * Test whether this pathname is a link.
	 *
	 * @return bool
	 */
	public function isLink();

	/**
	 * Test whether this pathname is a directory.
	 *
	 * @return bool
	 */
	public function isDirectory();

	/**
	 * Get the type of this file.
	 *
	 * @return int Type bitmask
	 */
	public function getType();

	/**
	 * Returns the absolute pathname.
	 *
	 * @return string
	 */
	public function getPathname();

	/**
	 * Get the link target of the link.
	 *
	 * @return string
	 */
	public function getLinkTarget();

	/**
	 * Get the name of the file or directory.
	 *
	 * @return string
	 */
	public function getBasename($suffix = '');

	/**
	 * Get the extension of the file.
	 *
	 * @return mixed
	 */
	public function getExtension();

	/**
	 * Returns the the path of this pathname's parent, or <em>null</em> if this pathname does not name a parent directory.
	 *
	 * @return File|null
	 */
	public function getParent();

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getAccessTime();

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setAccessTime($time);

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getCreationTime();

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getModifyTime();

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setModifyTime($time);

	/**
	 * Sets access and modification time of file.
	 *
	 * @param int $time
	 * @param int $atime
	 *
	 * @return bool
	 */
	public function touch($time = null, $atime = null, $doNotCreate = false);

	/**
	 * Get the size of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getSize();

	/**
	 * Get the owner of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getOwner();

	/**
	 * Set the owner of the file denoted by this pathname.
	 *
	 * @param string|int $user
	 *
	 * @return bool
	 */
	public function setOwner($user);

	/**
	 * Get the group of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getGroup();

	/**
	 * Change the group of the file denoted by this pathname.
	 *
	 * @param mixed $group
	 *
	 * @return bool
	 */
	public function setGroup($group);

	/**
	 * Get the mode of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getMode();

	/**
	 * Set the mode of the file denoted by this pathname.
	 *
	 * @param int  $mode
	 *
	 * @return bool
	 */
	public function setMode($mode);

	/**
	 * Test whether this pathname is readable.
	 *
	 * @return bool
	 */
	public function isReadable();

	/**
	 * Test whether this pathname is writeable.
	 *
	 * @return bool
	 */
	public function isWritable();

	/**
	 * Test whether this pathname is executeable.
	 *
	 * @return bool
	 */
	public function isExecutable();

	/**
	 * Checks whether a file or directory exists.
	 *
	 * @return bool
	 */
	public function exists();

	/**
	 * Delete a file or directory.
	 *
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function delete($recursive = false, $force = false);

	/**
	 * Copies file
	 *
	 * @param File $destination
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function copyTo(File $destination, $parents = false);

	/**
	 * Renames a file or directory
	 *
	 * @param File $destination
	 *
	 * @return bool
	 */
	public function moveTo(File $destination);

	/**
	 * Makes directory
	 *
	 * @return bool
	 */
	public function createDirectory($parents = false);

	/**
	 * Create new empty file.
	 *
	 * @return bool
	 */
	public function createFile($parents = false);

	/**
	 * Get contents of the file. Returns <em>null</em> if file does not exists
	 * and <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @return string|null|bool
	 */
	public function getContents();

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function setContents($content);

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function appendContents($content);

	/**
	 * Truncate a file to a given length. Returns the new length or
	 * <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param int $size
	 *
	 * @return int|bool
	 */
	public function truncate($size = 0);

	/**
	 * Gets an stream for the file. May return <em>null</em> if streaming is not supported.
	 *
	 * @param string $mode
	 *
	 * @return resource|null
	 */
	public function open($mode = 'rb');

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMEName();

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMEType();

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMEEncoding();

	/**
	 * Calculate the md5 hash of this file.
	 * Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param bool $raw Return binary hash, instead of string hash.
	 *
	 * @return string|null
	 */
	public function getMD5($raw = false);

	/**
	 * Calculate the sha1 hash of this file.
	 * Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param bool $raw Return binary hash, instead of string hash.
	 *
	 * @return string|null
	 */
	public function getSHA1($raw = false);

	/**
	 * List files.
	 *
	 * @param int|string|callable Multiple list of LIST_* bitmask, glob pattern and callables to filter the list.
	 *
	 * @return array<File>
	 */
	public function ls();

	/**
	 * Get the real url, e.g. file:/real/path/to/file to the pathname.
	 *
	 * @return string
	 */
	public function getRealURL();

	/**
	 * Get a public url, e.g. http://www.example.com/path/to/public/file to the file.
	 *
	 * @return string
	 */
	public function getPublicURL();
}
