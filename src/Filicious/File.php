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

use Filicious\Internals\FileState;


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
	
	protected $adapter;
	
	/**
	 * @param string $pathname
	 * @param FileState $stat
	 */
	public function __construct(Filesystem $filesystem, $pathname, Adapter $adapter)
	{
		$this->filesystem = $filesystem;
		$this->pathname = $pathname;
		$this->adapter = $adapter;
	}

	public function __toString()
	{
		try {
			return $this->adapter->getStreamURL($this->pathname);
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
		return $this->pathname;
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
		return $this->filesystem->getFile($this->getDirname());
	}
	
	/**
	 * Checks if the file is a file.
	 *
	 * @return bool True if the file exists and is a file; otherwise false
	 */
	public function isFile()
	{
		return $this->adapter->isFile($this->pathname, $this->pathname);
	}

	/**
	 * Checks if the file is a (symbolic) link.
	 * 
	 * @return bool True if the file exists and is a link; otherwise false
	 */
	public function isLink()
	{
		return $this->adapter->isLink($this->pathname, $this->pathname);
	}

	/**
	 * Checks if the file is a directory.
	 * 
	 * @return bool True if the file exists and is a directory; otherwise false
	 */
	public function isDirectory()
	{
		return $this->adapter->isDirectory($this->pathname, $this->pathname);
	}

	public function getLinkTarget()
	{
		return $this->adapter->getLinkTarget($this->pathname, $this->pathname);
	}

	/**
	 * Returns the date and time at which the file was accessed last time.
	 * 
	 * @return \DateTime The last access time
	 * @throws FileStateException If the file does not exists
	 */
	public function getAccessTime()
	{
		return $this->adapter->getAccessTime($this->pathname, $this->pathname);
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
		return $this->adapter->setAccessTime($this->pathname, $this->pathname, static::getDateTime($atime));
	}

	/**
	 * Returns the date and time at which the file was created.
	 * 
	 * @return \DateTime The creation time
	 * @throws FileStateException If the file does not exists
	 */
	public function getCreationTime()
	{ 
		return $this->adapter->getCreationTime($this->pathname, $this->pathname);
	}
	
	/**
	 * Returns the time at which the file was modified last time.
	 *
	 * @return \DateTime The modify time
	 * @throws FileStateException If the file does not exists
	 */
	public function getModifyTime()
	{ 
		return $this->adapter->getModifyTime($this->pathname, $this->pathname);
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
		return $this->adapter->setModifyTime($this->pathname, $this->pathname, static::getDateTime($mtime));
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
		return $this->adapter->touch($this->pathname, $this->pathname, $time, $atime, $create);
	}

	/**
	 * Returns the size of the file.
	 * 
	 * @return int The file size
	 * @throws FileStateException If the file does not exists
	 */
	public function getSize($recursive = false)
	{
		return $this->adapter->getSize($this->pathname, $this->pathname, $recursive);
	}

	public function getOwner()
	{
		return $this->adapter->getOwner($this->pathname, $this->pathname);
	}

	public function setOwner($user)
	{
		return $this->adapter->setOwner($this->pathname, $this->pathname, $user);
	}

	public function getGroup()
	{
		return $this->adapter->getGroup($this->pathname, $this->pathname);
	}

	public function setGroup($group)
	{
		return $this->adapter->setGroup($this->pathname, $this->pathname, $group);
	}

	public function getMode()
	{
		return $this->adapter->getMode($this->pathname, $this->pathname);
	}

	public function setMode($mode)
	{
		return $this->adapter->setMode($this->pathname, $this->pathname, $mode);
	}

	public function isReadable()
	{
		return $this->adapter->isReadable($this->pathname, $this->pathname);
	}

	public function isWritable()
	{
		return $this->adapter->isWritable($this->pathname, $this->pathname);
	}

	public function isExecutable()
	{
		return $this->adapter->isExecutable($this->pathname, $this->pathname);
	}

	public function exists()
	{
		return $this->adapter->exists($this->pathname, $this->pathname);
	}

	public function delete($recursive = false, $force = false)
	{
		return $this->adapter->delete($this->pathname, $this->pathname, $recursive, $force);
	}

	public function copyTo(File $destination, $recursive = false, $overwrite = self::OVERWRITE_REJECT, $parents = false)
	{
		return $this->adapter->copyTo($this->pathname, $this->pathname, $destination->adapter, $destination->pathname, $parents);
	}

	public function moveTo(File $destination, $overwrite = self::OVERWRITE_REJECT, $parents = false)
	{
		return $this->adapter->moveTo($this->pathname, $this->pathname, $destination, $parents);
	}

	public function createDirectory($parents = false)
	{
		return $this->adapter->createDirectory($this->pathname, $this->pathname, $parents);
	}

	public function createFile($parents = false)
	{
		return $this->adapter->createFile($this->pathname, $this->pathname, $parents);
	}

	public function getContents()
	{
		return $this->adapter->getContents($this->pathname, $this->pathname);
	}

	public function setContents($content)
	{
		return $this->adapter->setContents($this->pathname, $this->pathname, $content);
	}

	public function appendContents($content)
	{
		return $this->adapter->appendContents($this->pathname, $this->pathname, $content);
	}

	public function truncate($size = 0)
	{
		return $this->adapter->truncate($this->pathname, $this->pathname, $size);
	}

	public function open($mode = 'rb')
	{
		return $this->adapter->open($this->pathname, $this->pathname, $mode);
	}

	public function getMIMEName()
	{
		return $this->adapter->getMIMEName($this->pathname, $this->pathname);
	}

	public function getMIMEType()
	{
		return $this->adapter->getMIMEType($this->pathname, $this->pathname);
	}

	public function getMIMEEncoding()
	{
		return $this->adapter->getMIMEEncoding($this->pathname, $this->pathname);
	}

	public function getMD5($raw = false)
	{
		return $this->adapter->getMD5($this->pathname, $this->pathname, $raw);
	}

	public function getSHA1($raw = false)
	{
		return $this->adapter->getSHA1($this->pathname, $this->pathname, $raw);
	}

	public function ls()
	{
		return $this->adapter->ls($this->pathname, $this->pathname, func_get_args());
	}

	public function count()
	{
		return $this->adapter->count($this->pathname, $this->pathname, func_get_args());
	}

	public function getIterator()
	{
		return $this->adapter->getIterator($this->pathname, $this->pathname, func_get_args());
	}
	
	public function getFreeSpace()
	{
		return $this->adapter->getFreeSpace($this->pathname, $this->pathname);
	}
	
	public function getTotalSpace()
	{
		return $this->adapter->getTotalSpace($this->pathname, $this->pathname);
	}
}
