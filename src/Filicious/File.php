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

	public function __toString()
	{
		try {
			return $this->pathname->rootAdapter()->getStreamURL($this->pathname);
		} catch(\Exception $e) { // TODO catch correct exception
			return '';
		}
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

	public function getExtension()
	{
		$basename = $this->getBasename();
		$pos      = strrpos($basename, '.');
		return $pos === false ? '' : substr($basename, $pos + 1);
	}

	public function getDirname() {
		return dirname($this->pathname);
	}

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
		return $this->pathname->rootAdapter()->setAccessTime($this->pathname, static::getDateTime($atime));
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
		return $this->pathname->rootAdapter()->setModifyTime($this->pathname, static::getDateTime($mtime));
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
		return $this->pathname->rootAdapter()->touch($this->pathname, $time, $atime, $create);
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

	public function getOwner()
	{
		return $this->pathname->rootAdapter()->getOwner($this->pathname);
	}

	public function setOwner($user)
	{
		return $this->pathname->rootAdapter()->setOwner($this->pathname, $user);
	}

	public function getGroup()
	{
		return $this->pathname->rootAdapter()->getGroup($this->pathname);
	}

	public function setGroup($group)
	{
		return $this->pathname->rootAdapter()->setGroup($this->pathname, $group);
	}

	public function getMode()
	{
		return $this->pathname->rootAdapter()->getMode($this->pathname);
	}

	public function setMode($mode)
	{
		return $this->pathname->rootAdapter()->setMode($this->pathname, $mode);
	}

	public function isReadable()
	{
		return $this->pathname->rootAdapter()->isReadable($this->pathname);
	}

	public function isWritable()
	{
		return $this->pathname->rootAdapter()->isWritable($this->pathname);
	}

	public function isExecutable()
	{
		return $this->pathname->rootAdapter()->isExecutable($this->pathname);
	}

	public function exists()
	{
		return $this->pathname->rootAdapter()->exists($this->pathname);
	}

	public function delete($recursive = false, $force = false)
	{
		return $this->pathname->rootAdapter()->delete($this->pathname, $recursive, $force);
	}

	public function copyTo(File $destination, $recursive = false, $overwrite = self::OPERATION_REJECT, $parents = false)
	{
		return $this->pathname->rootAdapter()->copyTo($this->pathname, $destination->adapter, $destination->pathname, $destination->pathname, $parents);
	}

	public function moveTo(File $destination, $overwrite = self::OPERATION_REJECT, $parents = false)
	{
		return $this->pathname->rootAdapter()->moveTo($this->pathname, $destination, $parents);
	}

	public function createDirectory($parents = false)
	{
		return $this->pathname->rootAdapter()->createDirectory($this->pathname, $parents);
	}

	public function createFile($parents = false)
	{
		return $this->pathname->rootAdapter()->createFile($this->pathname, $parents);
	}

	public function getContents()
	{
		return $this->pathname->rootAdapter()->getContents($this->pathname);
	}

	public function setContents($content, $create = true)
	{
		return $this->pathname->rootAdapter()->setContents($this->pathname, $content, $create);
	}

	public function appendContents($content, $create = true)
	{
		return $this->pathname->rootAdapter()->appendContents($this->pathname, $content, $create);
	}

	public function truncate($size = 0)
	{
		return $this->pathname->rootAdapter()->truncate($this->pathname, $size);
	}

	public function open($mode = 'rb')
	{
		return $this->pathname->rootAdapter()->open($this->pathname, $mode);
	}

	public function getMIMEName()
	{
		return $this->pathname->rootAdapter()->getMIMEName($this->pathname);
	}

	public function getMIMEType()
	{
		return $this->pathname->rootAdapter()->getMIMEType($this->pathname);
	}

	public function getMIMEEncoding()
	{
		return $this->pathname->rootAdapter()->getMIMEEncoding($this->pathname);
	}

	public function getMD5($raw = false)
	{
		return $this->pathname->rootAdapter()->getMD5($this->pathname, $raw);
	}

	public function getSHA1($raw = false)
	{
		return $this->pathname->rootAdapter()->getSHA1($this->pathname, $raw);
	}

	public function ls()
	{
		return $this->pathname->rootAdapter()->ls($this->pathname, func_get_args());
	}

	public function count()
	{
		return $this->pathname->rootAdapter()->count($this->pathname, func_get_args());
	}

	public function getIterator()
	{
		return $this->pathname->rootAdapter()->getIterator($this->pathname, func_get_args());
	}

	public function getFreeSpace()
	{
		return $this->pathname->rootAdapter()->getFreeSpace($this->pathname);
	}

	public function getTotalSpace()
	{
		return $this->pathname->rootAdapter()->getTotalSpace($this->pathname);
	}
}
