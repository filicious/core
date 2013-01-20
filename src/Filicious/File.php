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

use Filicious\Internals\Adapter;
use Filicious\Internals\Pathname;

/**
 * A file object
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 */
class File
	implements \IteratorAggregate, \Countable
{

	public static function getDateTime($time) {
		if($time instanceof \DateTime) {
			return $time;
		}
		if(is_int($time) || is_float($time)) {
			return new \DateTime('@' . intval($time));
		}
		return new \DateTime($time);
	}

	/**
	 * @var int Flag for file operations, which involve a file target
	 * 		destination, indicating that missing parent directories of the
	 * 		operations file target destination should be created before the
	 * 		execution of the operation is done
	 */
	const OPERATION_PARENTS		= 0x01;
	/**
	 * @var int Flag for file operations to apply the execution recrusivly, if
	 * 		the file being operated on is a directory.
	 */
	const OPERATION_RECURSIVE	= 0x02;
	/**
	 * @var int Flag for file operations, which involve a file target
	 * 		destination, to reject the execution of the operation, if the
	 * 		operations file target destination already exists. This flag
	 * 		overrules the OPERATION_REPLACE flag.
	 */
	const OPERATION_REJECT		= 0x10;
	/**
	 * @var int Flag for file operations, which involve a file target
	 * 		destination, to merge the operations file source with the operations
	 * 		file target destination.
	 */
	const OPERATION_MERGE		= 0x20;
	/**
	 * @var int Flag for file operations, which involve a file target
	 * 		destination, to replace the target destination.
	 */
	const OPERATION_REPLACE		= 0x40;

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

	protected $filesystem;

	protected $pathname;

	/**
	 * @param string $pathname
	 * @param FileState $stat
	 */
	public function __construct(Filesystem $filesystem, Pathname $pathname)
	{
		$this->filesystem = $filesystem;
		$this->pathname = $pathname;
	}

	/**
	 * Returns the full abstracted pathname of this file within the containing
	 * filesystem.
	 * A pathname always starts with a forward slash and never ends with one.
	 * The exception is the root of the filesystem, which returns the empty
	 * string.
	 *
	 * @return string The full abstracted pathname
	 */
	public function getPathname()
	{
		return $this->pathname->full();
	}

	/**
	 * Returns the basename of this file, which is the string after the last
	 * forward slash of the pathname of this file.
	 * If the basename ends with the given suffix, it will be truncated off
	 * of the end of the basename.
	 *
	 * @param string $suffix The suffix to truncate
	 * @return string The basename
	 */
	public function getBasename($suffix = null)
	{
		return basename($this->pathname, $suffix);
	}

	/**
	 * Return the extension of the filename.
	 * May return an empty string, if filename has no extension.
	 *
	 * @return string
	 */
	public function getExtension()
	{
		$basename = $this->getBasename();
		$pos      = strrpos($basename, '.');
		return $pos === false ? '' : substr($basename, $pos + 1);
	}

	/**
	 * Return the dirname or parent name.
	 *
	 * @return string
	 */
	public function getDirname() {
		return $this->pathname->parent()->full();
	}

	/**
	 * Return the parent file object.
	 * May return null if called on the root "/" node.
	 *
	 * @return File|null
	 */
	public function getParent()
	{
		if ($this->pathname->full() != '/') {
			return $this->filesystem->getFile($this->getDirname());
		}
		return null;
	}

	/**
	 * Checks if the file is a file.
	 *
	 * @return bool True if the file exists and is a file; otherwise false
	 */
	public function isFile()
	{
		return $this->pathname->rootAdapter()->isFile($this->pathname);
	}

	/**
	 * TODO PROPOSED TO BE REMOVED
	 *
	 * Checks if the file is a (symbolic) link.
	 *
	 * @return bool True if the file exists and is a link; otherwise false
	 */
	public function isLink()
	{
		return $this->pathname->rootAdapter()->isLink($this->pathname);
	}

	/**
	 * Checks if the file is a directory.
	 *
	 * @return bool True if the file exists and is a directory; otherwise false
	 */
	public function isDirectory()
	{
		return $this->pathname->rootAdapter()->isDirectory($this->pathname);
	}

	/**
	 * TODO PROPOSED TO BE REMOVED
	 * @return mixed
	 */
	public function getLinkTarget()
	{
		return $this->pathname->rootAdapter()->getLinkTarget($this->pathname);
	}

	/**
	 * Returns the date and time at which the file was accessed last time.
	 *
	 * @return \DateTime The last access time
	 * @throws FileStateException If the file does not exists
	 */
	public function getAccessTime()
	{
		return $this->pathname->rootAdapter()->getAccessTime($this->pathname);
	}

	/**
	 * Set the date and time at which the file was accessed last time.
	 * The given $atime parameter is converted to a \DateTime object via
	 * File::getDateTime.
	 *
	 * @param mixed $atime The new access time
	 * @return void
	 * @throws FileStateException If the file does not exists
	 */
	public function setAccessTime($atime = 'now')
	{
		$this->pathname->rootAdapter()->setAccessTime($this->pathname, static::getDateTime($atime));
		return $this;
	}

	/**
	 * Returns the date and time at which the file was created.
	 *
	 * @return \DateTime The creation time
	 * @throws FileStateException If the file does not exists
	 */
	public function getCreationTime()
	{
		return $this->pathname->rootAdapter()->getCreationTime($this->pathname);
	}

	/**
	 * Returns the time at which the file was modified last time.
	 *
	 * @return \DateTime The modify time
	 * @throws FileStateException If the file does not exists
	 */
	public function getModifyTime()
	{
		return $this->pathname->rootAdapter()->getModifyTime($this->pathname);
	}

	/**
	 * Set the date and time at which the file was modified last time.
	 * The given $mtime parameter is converted to a \DateTime object via
	 * File::getDateTime.
	 *
	 * @param mixed $atime The new modify time
	 * @return void
	 * @throws FileStateException If the file does not exists
	 */
	public function setModifyTime($mtime = 'now')
	{
		$this->pathname->rootAdapter()->setModifyTime($this->pathname, static::getDateTime($mtime));
		return $this;
	}

	/**
	 * Set the date and time at which the file was modified and / or accessed
	 * last time.
	 * The given $time and $atime parameters are converted to \DateTime objects
	 * via File::getDateTime, with the one exception, that if $atime parameter
	 * is set to null, then the date and time given in the $time parameter will
	 * be used for $atime.
	 *
	 * @param mixed $time The new modify time
	 * @param mixed $atime The new access time; If null then $time will be used
	 * @param bool $create Whether to create the file, if it does not already
	 * 		exists
	 * @return void
	 * @throws FileStateException If the file does not exists and $create is set
	 * 		to false
	 */
	public function touch($time = 'now', $atime = null, $create = true)
	{
		$time = static::getDateTime($time);
		$atime = $atime === null ? $time : static::getDateTime($atime);
		$this->pathname->rootAdapter()->touch($this->pathname, $time, $atime, $create);
	}

	/**
	 * Returns the size of the file.
	 *
	 * @return int The file size
	 * @throws FileStateException If the file does not exists
	 */
	public function getSize($recursive = false)
	{
		return $this->pathname->rootAdapter()->getSize($this->pathname, $recursive);
	}

	/**
	 * Return the owner of the file.
	 *
	 * @return int|string
	 */
	public function getOwner()
	{
		return $this->pathname->rootAdapter()->getOwner($this->pathname);
	}

	/**
	 * Set the owner of the file.
	 *
	 * @param $user
	 *
	 * @return File
	 */
	public function setOwner($user)
	{
		$this->pathname->rootAdapter()->setOwner($this->pathname, $user);
		return $this;
	}

	/**
	 * Return the group of the file.
	 *
	 * @return int|string
	 */
	public function getGroup()
	{
		return $this->pathname->rootAdapter()->getGroup($this->pathname);
	}

	/**
	 * Set the group of the file.
	 *
	 * @param $group
	 *
	 * @return File
	 */
	public function setGroup($group)
	{
		$this->pathname->rootAdapter()->setGroup($this->pathname, $group);
		return $this;
	}

	/**
	 * Return the permission mode of the file.
	 *
	 * @return int
	 */
	public function getMode()
	{
		return $this->pathname->rootAdapter()->getMode($this->pathname);
	}

	/**
	 * Set the permission mode of the file.
	 *
	 * @param $mode
	 *
	 * @return File
	 */
	public function setMode($mode)
	{
		$this->pathname->rootAdapter()->setMode($this->pathname, $mode);
		return $this;
	}

	/**
	 * Check if file is readable.
	 *
	 * @return bool
	 */
	public function isReadable()
	{
		return $this->pathname->rootAdapter()->isReadable($this->pathname);
	}

	/**
	 * Check if file is writable.
	 *
	 * @return bool
	 */
	public function isWritable()
	{
		return $this->pathname->rootAdapter()->isWritable($this->pathname);
	}

	/**
	 * Check if file is executable.
	 *
	 * @return bool
	 */
	public function isExecutable()
	{
		return $this->pathname->rootAdapter()->isExecutable($this->pathname);
	}

	/**
	 * Check if file exists.
	 *
	 * @return bool
	 */
	public function exists()
	{
		return $this->pathname->rootAdapter()->exists($this->pathname);
	}

	/**
	 * Delete a file or directory.
	 *
	 * @param bool $recursive
	 * @param bool $force
	 *
	 * @return File
	 */
	public function delete($recursive = false, $force = false)
	{
		$this->pathname->rootAdapter()->delete($this->pathname, $recursive, $force);
		return $this;
	}

	/**
	 * Copy this file to another destination.
	 *
	 * @param File $destination The target destination.
	 * @param bool $recursive
	 * @param int  $overwrite
	 * @param bool $parents
	 *
	 * @return File
	 */
	public function copyTo(File $destination, $recursive = false, $overwrite = self::OPERATION_REJECT, $parents = false)
	{
		$this->pathname->rootAdapter()->copyTo(
			$this->pathname,
			$destination->pathname,
			($recursive ? File::OPERATION_RECURSIVE : 0)
			| ($parents ? File::OPERATION_PARENTS : 0)
			| ($overwrite ? File::OPERATION_MERGE : 0)
		);
		return $this;
	}

	/**
	 * Move this file to another destination.
	 *
	 * @param File $destination The target destination.
	 * @param int  $overwrite
	 * @param bool $parents
	 *
	 * @return File
	 */
	public function moveTo(File $destination, $overwrite = self::OPERATION_REJECT, $parents = false)
	{
		$this->pathname->rootAdapter()->moveTo(
			$this->pathname,
			$destination->pathname,
			($parents ? File::OPERATION_PARENTS : 0)
			| ($overwrite ? File::OPERATION_MERGE : 0)
		);
		return $this;
	}

	/**
	 * Create a new directory.
	 *
	 * @param bool $parents
	 *
	 * @return File
	 */
	public function createDirectory($parents = false)
	{
		$this->pathname->rootAdapter()->createDirectory($this->pathname, $parents);
		return $this;
	}

	/**
	 * Create an empty file.
	 *
	 * @param bool $parents
	 *
	 * @return File
	 */
	public function createFile($parents = false)
	{
		$this->pathname->rootAdapter()->createFile($this->pathname, $parents);
		return $this;
	}

	/**
	 * Get contents of the file.
	 *
	 * @return string
	 */
	public function getContents()
	{
		return $this->pathname->rootAdapter()->getContents($this->pathname);
	}

	/**
	 * Set contents of the file.
	 *
	 * @param      $content
	 * @param bool $create
	 *
	 * @return File
	 */
	public function setContents($content, $create = true)
	{
		$this->pathname->rootAdapter()->setContents($this->pathname, $content, $create);
		return $this;
	}

	/**
	 * Append contents to the file.
	 *
	 * @param      $content
	 * @param bool $create
	 *
	 * @return File
	 */
	public function appendContents($content, $create = true)
	{
		$this->pathname->rootAdapter()->appendContents($this->pathname, $content, $create);
		return $this;
	}

	/**
	 * Truncate file to a given size.
	 *
	 * @param int $size
	 */
	public function truncate($size = 0)
	{
		return $this->pathname->rootAdapter()->truncate($this->pathname, $size);
	}

	/**
	 * Get a stream object to the file.
	 *
	 * @return Stream
	 */
	public function getStream()
	{
		return $this->pathname->rootAdapter()->getStream($this->pathname);
	}

	/**
	 * Get a streaming url to the file.
	 *
	 * @return string
	 */
	public function getStreamURL()
	{
		return $this->pathname->rootAdapter()->getStreamURL($this->pathname);
	}

	/**
	 * Get the mime name (e.g. "OpenDocument Text") of the file.
	 *
	 * @return string
	 */
	public function getMIMEName()
	{
		return $this->pathname->rootAdapter()->getMIMEName($this->pathname);
	}

	/**
	 * Get the mime type (e.g. "application/vnd.oasis.opendocument.text") of the file.
	 *
	 * @return string
	 */
	public function getMIMEType()
	{
		return $this->pathname->rootAdapter()->getMIMEType($this->pathname);
	}

	/**
	 * Get the mime encoding (e.g. "binary" or "us-ascii" or "utf-8") of the file.
	 *
	 * @return string
	 */
	public function getMIMEEncoding()
	{
		return $this->pathname->rootAdapter()->getMIMEEncoding($this->pathname);
	}

	/**
	 * Get the md5 hash of the file.
	 *
	 * @param bool $raw
	 *
	 * @return string
	 */
	public function getMD5($raw = false)
	{
		return $this->pathname->rootAdapter()->getMD5($this->pathname, $raw);
	}

	/**
	 * Get the sha1 hash of the file.
	 *
	 * @param bool $raw
	 *
	 * @return string
	 */
	public function getSHA1($raw = false)
	{
		return $this->pathname->rootAdapter()->getSHA1($this->pathname, $raw);
	}

	/**
	 * List all children of this directory.
	 *
	 * @param Variable list of filters.
	 * - Flags File::LIST_*
	 * - Glob pattern
	 * - Callables
	 * @return array
	 * @throws \Filicious\Exception\NotADirectoryException
	 */
	public function ls($filter = null, $_ = null)
	{
		return $this->pathname->rootAdapter()->getIterator($this->pathname, func_get_args())->toArray();
	}

	/**
	 * Count all children of this directory.
	 *
	 * @param Variable list of filters.
	 * - Flags File::LIST_*
	 * - Glob pattern
	 * - Callables
	 * @return int
	 * @throws \Filicious\Exception\NotADirectoryException
	 */
	public function count($filter = null, $_ = null)
	{
		return $this->pathname->rootAdapter()->count($this->pathname, func_get_args());
	}

	/**
	 * Get an iterator for this directory.
	 *
	 * @param Variable list of filters.
	 * - Flags File::LIST_*
	 * - Glob pattern
	 * - Callables
	 * @return \Iterator|\Traversable
	 */
	public function getIterator($filter = null, $_ = null)
	{
		return $this->pathname->rootAdapter()->getIterator($this->pathname, func_get_args());
	}

	/**
	 * Get the free space.
	 *
	 * @return float
	 */
	public function getFreeSpace()
	{
		return $this->pathname->rootAdapter()->getFreeSpace($this->pathname);
	}

	/**
	 * Get the total space.
	 *
	 * @return float
	 */
	public function getTotalSpace()
	{
		return $this->pathname->rootAdapter()->getTotalSpace($this->pathname);
	}

	/**
	 * INTERNAL USE ONLY
	 *
	 * @return Internals\Pathname|string
	 */
	public function internalPathname()
	{
		return $this->pathname;
	}

	/**
	 * Return a stream url or pathname, if streaming is not supported.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->pathname->rootAdapter()->getStreamURL($this->pathname);
	}
}
