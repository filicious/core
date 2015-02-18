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

use Filicious\Event\AppendEvent;
use Filicious\Event\CopyEvent;
use Filicious\Event\CreateDirectoryEvent;
use Filicious\Event\CreateFileEvent;
use Filicious\Event\DeleteEvent;
use Filicious\Event\FiliciousEvents;
use Filicious\Event\MoveEvent;
use Filicious\Event\SetGroupEvent;
use Filicious\Event\SetModeEvent;
use Filicious\Event\SetOwnerEvent;
use Filicious\Event\TouchEvent;
use Filicious\Event\TruncateEvent;
use Filicious\Event\WriteEvent;
use Filicious\Exception\FileNotFoundException;
use Filicious\Exception\NotADirectoryException;
use Filicious\Exception\NotAFileException;
use Filicious\Internals\Pathname;
use Filicious\Internals\Util;
use Filicious\Iterator\FilesystemIterator;
use Filicious\Plugin\FilePluginInterface;

/**
 * A generic file object.
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 */
class File
	implements \IteratorAggregate, \Countable
{

	/**
	 * @var int Flag for file operations, which involve a file target
	 *      destination, indicating that missing parent directories of the
	 *      operations file target destination should be created before the
	 *      execution of the operation is done
	 *
	 * @api
	 */
	const OPERATION_PARENTS = 0x01;

	/**
	 * @var int Flag for file operations to apply the execution recrusivly, if
	 *      the file being operated on is a directory.
	 *
	 * @api
	 */
	const OPERATION_RECURSIVE = 0x02;

	/**
	 * @var int Flag for file operations, which involve a file target
	 *      destination, to reject the execution of the operation, if the
	 *      operations file target destination already exists. This flag
	 *      overrules the OPERATION_REPLACE flag.
	 *
	 * @api
	 */
	const OPERATION_REJECT = 0x10;

	/**
	 * @var int Flag for file operations, which involve a file target
	 *      destination, to merge the operations file source with the operations
	 *      file target destination.
	 *
	 * @api
	 */
	const OPERATION_MERGE = 0x20;

	/**
	 * @var int Flag for file operations, which involve a file target
	 *      destination, to replace the target destination.
	 *
	 * @api
	 */
	const OPERATION_REPLACE = 0x40;

	/**
	 * List everything (LIST_HIDDEN | LIST_VISIBLE | LIST_FILES | LIST_DIRECTORIES | LIST_LINKS | LIST_OPAQUE)
	 *
	 * @api
	 */
	const LIST_ALL = 64512;

	/**
	 * Return hidden files (starting with ".")
	 *
	 * @api
	 */
	const LIST_HIDDEN = 1024;

	/**
	 * Return non-hidden (not starting with ".")
	 *
	 * @api
	 */
	const LIST_VISIBLE = 2048;

	/**
	 * Return only files.
	 *
	 * @api
	 */
	const LIST_FILES = 4096;

	/**
	 * Return only directories.
	 *
	 * @api
	 */
	const LIST_DIRECTORIES = 8192;

	/**
	 * List recursive.
	 *
	 * @api
	 */
	const LIST_RECURSIVE = 65536;

	/**
	 * @var Filesystem
	 */
	protected $filesystem;

	/**
	 * @var Pathname
	 */
	protected $pathname;

	/**
	 * @param Pathname $pathname
	 */
	public function __construct(Pathname $pathname)
	{
		$this->pathname   = $pathname;
		$this->filesystem = $pathname->rootAdapter()->getFilesystem();
	}

	/**
	 * Get the filesystem this file belongs to.
	 *
	 * @return Filesystem
	 *
	 * @api
	 */
	public function getFilesystem()
	{
		return $this->filesystem;
	}

	/**
	 * Returns the full abstracted pathname of this file within the containing
	 * filesystem.
	 * A pathname always starts with a forward slash and never ends with one.
	 * The exception is the root of the filesystem, which returns the empty
	 * string.
	 *
	 * @return string The full abstracted pathname
	 *
	 * @api
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
	 *
	 * @return string The basename
	 *
	 * @api
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
	 *
	 * @api
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
	 *
	 * @api
	 */
	public function getDirname()
	{
		return $this->pathname->parent()->full();
	}

	/**
	 * Return the parent file object.
	 * May return null if called on the root "/" node.
	 *
	 * @return File|null
	 *
	 * @api
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
	 *
	 * @api
	 */
	public function isFile()
	{
		return $this->pathname->rootAdapter()->isFile($this->pathname);
	}

	/**
	 * Checks if the file is a directory.
	 *
	 * @return bool True if the file exists and is a directory; otherwise false
	 *
	 * @api
	 */
	public function isDirectory()
	{
		return $this->pathname->rootAdapter()->isDirectory($this->pathname);
	}

	/**
	 * Returns the date and time at which the file was accessed last time.
	 *
	 * @return \DateTime The last access time
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function getAccessTime()
	{
		return $this->pathname->rootAdapter()->getAccessTime($this->pathname);
	}

	/**
	 * Set the date and time at which the file was accessed last time.
	 * The given $accessTime parameter is converted to a \DateTime object via
	 * Util::createDateTime.
	 *
	 * @param int|string|\DateTime $accessTime The new access time
	 *
	 * @return File
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function setAccessTime($accessTime = 'now')
	{
		$this->pathname->rootAdapter()->setAccessTime($this->pathname, Util::createDateTime($accessTime));
		return $this;
	}

	/**
	 * Returns the date and time at which the file was created.
	 *
	 * @return \DateTime The creation time
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function getCreationTime()
	{
		return $this->pathname->rootAdapter()->getCreationTime($this->pathname);
	}

	/**
	 * Returns the time at which the file was modified last time.
	 *
	 * @return \DateTime The modify time
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function getModifyTime()
	{
		return $this->pathname->rootAdapter()->getModifyTime($this->pathname);
	}

	/**
	 * Set the date and time at which the file was modified last time.
	 * The given $modifyTime parameter is converted to a \DateTime object via
	 * Util::createDateTime.
	 *
	 * @param int|string|\DateTime $modifyTime The new modify time
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function setModifyTime($modifyTime = 'now')
	{
		$this->pathname->rootAdapter()->setModifyTime($this->pathname, Util::createDateTime($modifyTime));
		return $this;
	}

	/**
	 * Set the date and time at which the file was modified and / or accessed
	 * last time.
	 * The given $time and $atime parameters are converted to \DateTime objects
	 * via Util::createDateTime, with the one exception, that if $atime parameter
	 * is set to null, then the date and time given in the $time parameter will
	 * be used for $atime.
	 *
	 * @param int|string|\DateTime $modifyTime The new modify time
	 * @param int|string|\DateTime $accessTime The new access time; If null then $time will be used
	 * @param bool  $create     Whether to create the file, if it does not already
	 *                          exists
	 *
	 * @return void
	 *
	 * @throws FileNotFoundException If the file does not exists and $create is set
	 *      to false
	 *
	 * @api
	 */
	public function touch($modifyTime = 'now', $accessTime = null, $create = true)
	{
		$eventDispatcher = $this->filesystem->getEventDispatcher();

		if ($eventDispatcher) {
			$exists = $this->pathname->rootAdapter()->exists($this->pathname);
		}
		else {
			$exists = null;
		}

		$modifyTime = Util::createDateTime($modifyTime);
		$accessTime = $accessTime === null ? $modifyTime : Util::createDateTime($accessTime);
		$this->pathname->rootAdapter()->touch($this->pathname, $modifyTime, $accessTime, $create);

		if ($eventDispatcher) {
			$event = new TouchEvent($this->filesystem, $this, $modifyTime, $accessTime, $create && !$exists);
			$eventDispatcher->dispatch(FiliciousEvents::TOUCH, $event);
		}
	}

	/**
	 * Returns the size of the file.
	 *
	 * @return int The file size
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function getSize($recursive = false)
	{
		return $this->pathname->rootAdapter()->getSize($this->pathname, $recursive);
	}

	/**
	 * Return the owner of the file.
	 *
	 * @return int|string
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function getOwner()
	{
		return $this->pathname->rootAdapter()->getOwner($this->pathname);
	}

	/**
	 * Set the owner of the file.
	 *
	 * @param string|int $user
	 *
	 * @return File
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function setOwner($user)
	{
		$this->pathname->rootAdapter()->setOwner($this->pathname, $user);

		$eventDispatcher = $this->filesystem->getEventDispatcher();
		if ($eventDispatcher) {
			$event = new SetOwnerEvent($this->filesystem, $this, $user);
			$eventDispatcher->dispatch(FiliciousEvents::SET_OWNER, $event);
		}

		return $this;
	}

	/**
	 * Return the group of the file.
	 *
	 * @return int|string
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function getGroup()
	{
		return $this->pathname->rootAdapter()->getGroup($this->pathname);
	}

	/**
	 * Set the group of the file.
	 *
	 * @param string|int $group
	 *
	 * @return File
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function setGroup($group)
	{
		$this->pathname->rootAdapter()->setGroup($this->pathname, $group);

		$eventDispatcher = $this->filesystem->getEventDispatcher();
		if ($eventDispatcher) {
			$event = new SetGroupEvent($this->filesystem, $this, $group);
			$eventDispatcher->dispatch(FiliciousEvents::SET_GROUP, $event);
		}

		return $this;
	}

	/**
	 * Return the permission mode of the file.
	 *
	 * @return int
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function getMode()
	{
		return $this->pathname->rootAdapter()->getMode($this->pathname);
	}

	/**
	 * Set the permission mode of the file.
	 *
	 * @param int $mode
	 *
	 * @return File
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function setMode($mode)
	{
		$this->pathname->rootAdapter()->setMode($this->pathname, $mode);

		$eventDispatcher = $this->filesystem->getEventDispatcher();
		if ($eventDispatcher) {
			$event = new SetModeEvent($this->filesystem, $this, $mode);
			$eventDispatcher->dispatch(FiliciousEvents::SET_MODE, $event);
		}

		return $this;
	}

	/**
	 * Check if file is readable.
	 *
	 * @return bool
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function isReadable()
	{
		return $this->pathname->rootAdapter()->isReadable($this->pathname);
	}

	/**
	 * Check if file is writable.
	 *
	 * @return bool
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function isWritable()
	{
		return $this->pathname->rootAdapter()->isWritable($this->pathname);
	}

	/**
	 * Check if file is executable.
	 *
	 * @return bool
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function isExecutable()
	{
		return $this->pathname->rootAdapter()->isExecutable($this->pathname);
	}

	/**
	 * Check if file exists.
	 *
	 * @return bool
	 *
	 * @api
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
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function delete($recursive = false, $force = false)
	{
		$eventDispatcher = $this->filesystem->getEventDispatcher();

		if ($eventDispatcher) {
			$event = new DeleteEvent($this->filesystem, $this, $recursive);
			$eventDispatcher->dispatch(FiliciousEvents::BEFORE_DELETE, $event);
		}

		$this->pathname->rootAdapter()->delete($this->pathname, $recursive, $force);

		if ($eventDispatcher) {
			$event = new DeleteEvent($this->filesystem, $this, $recursive);
			$eventDispatcher->dispatch(FiliciousEvents::DELETE, $event);
		}

		return $this;
	}

	/**
	 * Copy this file to another destination.
	 *
	 * @param File $destination The target destination.
	 * @param bool $recursive
	 * @param int  $overwrite
	 * @param bool $createParents
	 *
	 * @return File
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function copyTo(File $destination, $recursive = false, $overwrite = self::OPERATION_REJECT, $createParents = false)
	{
		$this->pathname->rootAdapter()->copyTo(
			$this->pathname,
			$destination->pathname,
			($recursive ? File::OPERATION_RECURSIVE : 0)
			| ($createParents ? File::OPERATION_PARENTS : 0)
			| ($overwrite ? File::OPERATION_MERGE : 0)
		);

		$eventDispatcher = $this->filesystem->getEventDispatcher();
		if ($eventDispatcher) {
			$event = new CopyEvent($this->filesystem, $this, $destination, $recursive, $overwrite, $createParents);
			$eventDispatcher->dispatch(FiliciousEvents::COPY, $event);
		}

		return $this;
	}

	/**
	 * Move this file to another destination.
	 *
	 * @param File $destination The target destination.
	 * @param int  $overwrite
	 * @param bool $createParents
	 *
	 * @return File
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function moveTo(File $destination, $overwrite = self::OPERATION_REJECT, $createParents = false)
	{
		$this->pathname->rootAdapter()->moveTo(
			$this->pathname,
			$destination->pathname,
			($createParents ? $createParents : File::OPERATION_PARENTS)
			| ($overwrite ? $overwrite : File::OPERATION_MERGE)
		);

		$eventDispatcher = $this->filesystem->getEventDispatcher();
		if ($eventDispatcher) {
			$event = new MoveEvent($this->filesystem, $this, $destination, $overwrite, $createParents);
			$eventDispatcher->dispatch(FiliciousEvents::MOVE, $event);
		}

		return $this;
	}

	/**
	 * Create a new directory.
	 *
	 * @param bool $createParents
	 *
	 * @return File
	 *
	 * @api
	 */
	public function createDirectory($createParents = false)
	{
		$this->pathname->rootAdapter()->createDirectory($this->pathname, $createParents);

		$eventDispatcher = $this->filesystem->getEventDispatcher();
		if ($eventDispatcher) {
			$event = new CreateDirectoryEvent($this->filesystem, $this, $createParents);
			$eventDispatcher->dispatch(FiliciousEvents::CREATE_DIRECTORY, $event);
		}

		return $this;
	}

	/**
	 * Create an empty file.
	 *
	 * @param bool $createParents
	 *
	 * @return File
	 *
	 * @api
	 */
	public function createFile($createParents = false)
	{
		$this->pathname->rootAdapter()->createFile($this->pathname, $createParents);

		$eventDispatcher = $this->filesystem->getEventDispatcher();
		if ($eventDispatcher) {
			$event = new CreateFileEvent($this->filesystem, $this, $createParents);
			$eventDispatcher->dispatch(FiliciousEvents::CREATE_FILE, $event);
		}

		return $this;
	}

	/**
	 * Get contents of the file.
	 *
	 * @return string
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function getContents()
	{
		return $this->pathname->rootAdapter()->getContents($this->pathname);
	}

	/**
	 * Set contents of the file.
	 *
	 * @param string $content
	 * @param bool $create
	 *
	 * @return File
	 *
	 * @throws NotAFileException If the pathname is not a file.
	 *
	 * @api
	 */
	public function setContents($content, $create = true)
	{
		$eventDispatcher = $this->filesystem->getEventDispatcher();

		if ($eventDispatcher) {
			$exists = $this->pathname->rootAdapter()->exists($this->pathname);
		}
		else {
			$exists = null;
		}

		$this->pathname->rootAdapter()->setContents($this->pathname, $content, $create);

		if ($eventDispatcher) {
			$event = new WriteEvent($this->filesystem, $this, $content, $create && !$exists);
			$eventDispatcher->dispatch(FiliciousEvents::WRITE, $event);
		}

		return $this;
	}

	/**
	 * Append contents to the file.
	 *
	 * @param string $content
	 * @param bool $create
	 *
	 * @return File
	 *
	 * @throws FileNotFoundException If the file does not exists
	 *
	 * @api
	 */
	public function appendContents($content, $create = true)
	{
		$eventDispatcher = $this->filesystem->getEventDispatcher();

		if ($eventDispatcher) {
			$exists = $this->pathname->rootAdapter()->exists($this->pathname);
		}
		else {
			$exists = null;
		}

		$this->pathname->rootAdapter()->appendContents($this->pathname, $content, $create);

		if ($eventDispatcher) {
			$event = new AppendEvent($this->filesystem, $this, $content, $create && !$exists);
			$eventDispatcher->dispatch(FiliciousEvents::APPEND, $event);
		}

		return $this;
	}

	/**
	 * Truncate file to a given size.
	 *
	 * @param int $size
	 *
	 * @return static
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws NotAFileException If the pathname is not a file.
	 *
	 * @api
	 */
	public function truncate($size = 0)
	{
		$this->pathname->rootAdapter()->truncate($this->pathname, $size);

		$eventDispatcher = $this->filesystem->getEventDispatcher();
		if ($eventDispatcher) {
			$event = new TruncateEvent($this->filesystem, $this, $size);
			$eventDispatcher->dispatch(FiliciousEvents::TRUNCATE, $event);
		}

		return $this;
	}

	/**
	 * Get a stream object to the file.
	 *
	 * @return Stream
	 *
	 * @throws FileNotFoundException If the file does not exists
	 * @throws NotAFileException If the pathname is not a file.
	 *
	 * @api
	 */
	public function getStream()
	{
		return $this->pathname->rootAdapter()->getStream($this->pathname);
	}

	/**
	 * Get a streaming url to the file.
	 *
	 * @return string
	 *
	 * @api
	 */
	public function getStreamURL()
	{
		return $this->pathname->rootAdapter()->getStreamURL($this->pathname);
	}

	/**
	 * List all children of this directory.
	 *
	 * @param int|string|\Closure|callable $filter Variable list of filters.
	 *                                             - Flags File::LIST_*
	 *                                             - Glob pattern
	 *                                             - Callables
	 *
	 * @return File[]
	 *
	 * @throws NotADirectoryException
	 *
	 * @api
	 */
	public function ls($filter = null, $_ = null)
	{
		$iterator = $this->pathname->rootAdapter()->getIterator($this->pathname, func_get_args());

		return iterator_to_array($iterator, false);
	}

	/**
	 * Count all children of this directory.
	 *
	 * @param int|string|\Closure|callable $filter Variable list of filters.
	 *                                             - Flags File::LIST_*
	 *                                             - Glob pattern
	 *                                             - Callables
	 *
	 * @return int
	 *
	 * @throws NotADirectoryException
	 *
	 * @api
	 */
	public function count($filter = null, $_ = null)
	{
		return $this->pathname->rootAdapter()->count($this->pathname, func_get_args());
	}

	/**
	 * Get an iterator for this directory.
	 *
	 * @param int|string|\Closure|callable $filter Variable list of filters.
	 *                                             - Flags File::LIST_*
	 *                                             - Glob pattern
	 *                                             - Callables
	 *
	 * @return \Iterator
	 *
	 * @api
	 */
	public function getIterator($filter = null, $_ = null)
	{
		return $this->pathname->rootAdapter()->getIterator($this->pathname, func_get_args());
	}

	/**
	 * Return a plugin for the filesystem.
	 *
	 * @param string $name
	 *
	 * @return bool
	 *
	 * @api
	 */
	public function hasPlugin($name)
	{
		$pluginManager = $this->filesystem->getPluginManager();

		return $pluginManager &&
		$pluginManager->hasPlugin($name) &&
		$pluginManager->getPlugin($name)->providesFilePlugin($this);
	}

	/**
	 * Return a plugin for the filesystem.
	 *
	 * @param string $name
	 *
	 * @return FilePluginInterface|null
	 *
	 * @api
	 */
	public function getPlugin($name)
	{
		$pluginManager = $this->filesystem->getPluginManager();

		if ($pluginManager && $pluginManager->hasPlugin($name)) {
			$plugin = $pluginManager->getPlugin($name);

			if ($plugin->providesFilePlugin($this)) {
				return $plugin->getFilePlugin($this);
			}
		}

		return null;
	}

	/**
	 * INTERNAL USE ONLY
	 *
	 * @return Internals\Pathname
	 *
	 * @internal Used to access the internal API.
	 *           This is formally used within adapters or plugins.
	 */
	public function internalPathname()
	{
		return $this->pathname;
	}

	/**
	 * Return a stream url or pathname, if streaming is not supported.
	 *
	 * @see File::getStreamUrl()
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getStreamURL();
	}
}
