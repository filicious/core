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

namespace Filicious\Mountcontainer;

use Filicious\File;
use Filicious\Filesystem;
use Filicious\AbstractSimpleFilesystem;
use Filicious\FilesystemException;
use Filicious\Util;

/**
 * Virtual filesystem structure.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MountcontainerFilesystem
	extends AbstractSimpleFilesystem
{
	/**
	 * @var string The name of the config class used by instances of this
	 *         filesystem implementation. Override in concrete classes to specify
	 *         another config class.
	 */
	const CONFIG_CLASS = 'Filicious\Mountcontainer\MountcontainerFilesystemConfig';

	protected $mounts;

	protected $map;

	public function __construct(MountcontainerFilesystemConfig $config, PublicURLProvider $provider = null)
	{
		parent::__construct($config, $provider);

		$this->mounts = array();
		$this->map    = array();
	}

	protected function normalizeMountPath($path, $absolute = false)
	{
		$path = Util::normalizePath($path);

		if ($path[0] != '/') {
			$path = '/' . $path;
		}

		if (!$absolute) {
			if (substr($path, -1) != '/') {
				$path .= '/';
			}

			$path .= '*';
		}

		return $path;
	}

	/**
	 * Mount an filesystem to a specific path.
	 *
	 * @param Filesystem $filesystem
	 * @param string     $path
	 */
	public function mount(Filesystem $filesystem, $path)
	{
		$path = $this->normalizeMountPath($path, true);

		if (array_key_exists($path, $this->map)) {
			throw FilesystemException('There is already a filesystem mounted at: ' . $path);
		}

		$this->mounts[$path] = $filesystem;
		$this->map[$path]    = $filesystem;
		$path                = $this->normalizeMountPath($path);
		$this->map[$path]    = $filesystem;
		krsort($this->map);
	}

	public function umount($path)
	{
		$path = $this->normalizeMountPath($path, true);
		unset($this->mounts[$path]);
		unset($this->map[$path]);
		$path = $this->normalizeMountPath($path);
		unset($this->map[$path]);
	}

	public function mounts()
	{
		return array_filter(
			array_keys($this->mounts),
			function ($pattern) {
				return substr($pattern, 0, -1);
			}
		);
	}

	protected function searchFilesystem($path)
	{
		if ($path[0] != '/') {
			$path = '/' . $path;
		}

		foreach ($this->map as $pattern => $filesystem) {

			if (fnmatch($pattern, $path)) {
				// remove trailing *
				$pattern = preg_replace('#/\*$#', '', $pattern);

				return array($pattern, $filesystem);
			}
		}

		return array('', $this);
	}

	/**************************************************************************
	 * Interface Filesystem
	 *************************************************************************/

	/**
	 * Get the root (/) file node.
	 *
	 * @return File
	 */
	public function getRoot()
	{
		return new MountcontainerFile('/', null, $this);
	}

	/**
	 * Get a file object for the specific file.
	 *
	 * @param string $path
	 *
	 * @return File
	 */
	public function getFile($path)
	{
		if ($path == '/') {
			return $this->getRoot();
		}

		/** @var string $pattern */
		/** @var Filesystem $filesystem */
		list($pattern, $filesystem) = $this->searchFilesystem($path);
		if ($pattern == $path) {
			return new MountcontainerFile($path, $this->mounts[$path]->getRoot(), $this);
		}
		else {
			if ($filesystem == $this) {
				$allMounts = array_filter(
					$this->mounts(),
					function ($path) use ($path) {
						return substr($path, 0, strlen($path)) == $path;
					}
				);
				if (count($allMounts) > 0) {
					return new MountcontainerFile($path, null, $this);
				}
				return null;
			}
		}
		return new MountcontainerFile($path, $filesystem->getFile('/' . substr($path, strlen($pattern))), $this);
	}

	/**
	 * Returns available space on filesystem or disk partition.
	 *
	 * @param File $path
	 *
	 * @return int
	 */
	public function getFreeSpace(File $path = null)
	{
		// TODO: Implement getFreeSpace() method.
	}

	/**
	 * Returns the total size of a filesystem or disk partition.
	 *
	 * @param File $path
	 *
	 * @return int
	 */
	public function getTotalSpace(File $path = null)
	{
		// TODO: Implement getTotalSpace() method.
	}

	/**************************************************************************
	 * Interface SimpleFilesystem
	 *************************************************************************/

	public function getTypeOf($file)
	{
		if (!$file->getSubFile()) {
			return File::TYPE_DIRECTORY;
		}
		else {
			return $file
				->getSubFile()
				->getType();
		}
	}

	/**
	 * Get the link target of the link.
	 *
	 * @return string
	 */
	public function getLinkTargetOf($file)
	{
		return $file->isLink() ? $file
			->getSubFile()
			->getLinkTarget() : null;
	}

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getAccessTimeOf($file)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->getAccessTime();
		}
		return false;
	}

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setAccessTimeOf($file, $time)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->setAccessTime($time);
		}
		return false;
	}

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getCreationTimeOf($file)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->getCreationTime();
		}
		return false;
	}

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getModifyTimeOf($file)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->getModifyTime();
		}
		return false;
	}

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setModifyTimeOf($file, $time)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->setModifyTime($time);
		}
		return false;
	}

	/**
	 * Sets access and modification time of file.
	 *
	 * @param File $file the file to modify
	 * @param int  $time
	 * @param int  $atime
	 *
	 * @return bool
	 */
	public function touch($file, $time = null, $atime = null, $doNotCreate = false)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->touch($time, $atime, $doNotCreate);
		}
		return false;
	}


	/**
	 * Get the size of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getSizeOf($file)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->getSize();
		}
		return 0;
	}

	/**
	 * Get the owner of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getOwnerOf($file)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->getOwner();
		}
		return -1;
	}

	/**
	 * Set the owner of the file denoted by this pathname.
	 *
	 * @param string|int $user
	 *
	 * @return bool
	 */
	public function setOwnerOf($file, $user)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->setOwner($user);
		}
		return false;
	}

	/**
	 * Get the group of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getGroupOf($file)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->getGroup();
		}
		return -1;
	}

	/**
	 * Change the group of the file denoted by this pathname.
	 *
	 * @param mixed $group
	 *
	 * @return bool
	 */
	public function setGroupOf($file, $group)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->setGroup($group);
		}
		return false;
	}

	/**
	 * Get the mode of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getModeOf($file)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->getMode();
		}
		return 0555;
	}

	/**
	 * Set the mode of the file denoted by this pathname.
	 *
	 * @param int  $mode
	 *
	 * @return bool
	 */
	public function setModeOf($file, $mode)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->setMode();
		}
		return false;
	}


	/**
	 * Test whether this pathname is readable.
	 *
	 * @return bool
	 */
	public function isThisReadable($file)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->isReadable();
		}
		return true;
	}

	/**
	 * Test whether this pathname is writeable.
	 *
	 * @return bool
	 */
	public function isThisWritable($file)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->isWritable();
		}
		return false;
	}

	/**
	 * Test whether this pathname is executeable.
	 *
	 * @return bool
	 */
	public function isThisExecutable($file)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->isExecutable();
		}
		return true;
	}

	/**
	 * Checks whether a file or directory exists.
	 *
	 * @return bool
	 */
	public function exists($file)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->exists();
		}
		return true;
	}

	/**
	 * Delete a file or directory.
	 *
	 * @param File $file the file
	 *
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function delete($file, $recursive = false, $force = false)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->delete($recursive, $force);
		}
		return false;
	}

	/**
	 * Copies file
	 *
	 * @param File $destination
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function copyTo($file, File $destination, $parents = false)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->copy($destination, $parents);
		}
		return false;
	}

	/**
	 * Renames a file or directory
	 *
	 * @param File $destination
	 *
	 * @return bool
	 */
	public function moveTo($file, File $destination)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->copy($destination);
		}
		return false;
	}

	/**
	 * Makes directory
	 *
	 * @return bool
	 */
	public function createDirectory($file, $parents = false)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->createDirectory();
		}
		return true;
	}

	/**
	 * Create new empty file.
	 *
	 * @return bool
	 */
	public function createFile($file, $parents = false)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->createFile();
		}
		return false;
	}

	/**
	 * Get contents of the file. Returns <em>null</em> if file does not exists
	 * and <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @return string|null|bool
	 */
	public function getContentsOf($file)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->getContents();
		}
		return false;
	}

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function setContentsOf($file, $content)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->setContents($content);
		}
		return false;
	}

	/**
	 * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param string $content
	 *
	 * @return bool
	 */
	public function appendContentsTo($file, $content)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->appendContents($content);
		}
		return false;
	}

	/**
	 * Truncate a file to a given length. Returns the new length or
	 * <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param int $size
	 *
	 * @return int|bool
	 */
	public function truncate($file, $size = 0)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->truncate($size);
		}
		return false;
	}

	/**
	 * Gets an stream for the file. May return <em>null</em> if streaming is not supported.
	 *
	 * @param string $mode
	 *
	 * @return resource|null
	 */
	public function open($file, $mode = 'rb')
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->open($mode);
		}
		return false;
	}


	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMENameOf($file)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->getMIMEName();
		}
		return false;
	}

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMETypeOf($file)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->getMIMEType();
		}
		return false;
	}

	/**
	 * Get mime content type.
	 *
	 * @param int $type
	 *
	 * @return string
	 */
	public function getMIMEEncodingOf($file)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->getMIMEEncoding();
		}
		return false;
	}

	/**
	 * Calculate the md5 hash of this file.
	 * Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param bool $raw Return binary hash, instead of string hash.
	 *
	 * @return string|null
	 */
	public function getMD5Of($file, $raw = false)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->getMD5($raw);
		}
		return false;
	}

	/**
	 * Calculate the sha1 hash of this file.
	 * Returns <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @param bool $raw Return binary hash, instead of string hash.
	 *
	 * @return string|null
	 */
	public function getSHA1Of($file, $raw = false)
	{
		if ($file->getSubFile()) {
			return $file
				->getSubFile()
				->getSHA1($raw);
		}
		return false;
	}

	/**
	 * List all files within a sub file.
	 *
	 * @param VirtualFile|MergedFile $file
	 *
	 * @param                        mixed filter options.
	 *
	 * @return array<File>
	 */
	public function lsFile( /*$file, ... */)
	{
		$args = func_get_args();
		$file = array_shift($args);
		list($recursive, $bitmask, $globs, $callables, $globSearchPatterns) = Util::buildFilters($file, $args);

		$allMounts  = $this->mounts();
		$filterBase = $file->getPathname();
		// special case, we are the root element.
		if ($filterBase != '/') {
			$filterBase .= '/';
		}
		$offset = strlen($filterBase);

		// filter out non matching mount points in parent vfs
		$allMounts = array_filter(
			$allMounts,
			function ($path) use ($filterBase) {
				return substr($path, 0, strlen($filterBase)) == $filterBase;
			}
		);

		// get unique virtual children list
		$allRoots = array_unique(
			array_map(
				function ($path) use ($filterBase, $offset) {

					$length = strpos($path, '/', $offset + 1) - $offset;

					if ($length > 0) {
						return substr($path, $offset, $length);
					}

					return substr($path, $offset);

				},
				$allMounts
			)
		);

		$arrFiles = array();
		if ($file->getSubfile()) {
			$arrFiles = call_user_func_array(array($file->getSubfile(), 'ls'), $args);
		}

		foreach ($allRoots as $subpath) {
			$subfile = $file->getPathname() . '/' . $subpath;

			//  is it a mount point? if so, return its root.
			if (in_array($subfile, $allMounts)) {
				$arrFiles[] = $this->getFile($subfile);
			}
			else {
				$arrFiles[] = new MountcontainerFile($subfile, null, $this);
			}
		}
		return $arrFiles;
	}

	/**
	 * Get the real url, e.g. file:/real/path/to/file to the pathname.
	 *
	 * @return string
	 */
	public function getRealURLOf($file)
	{
		return 'mountcontainer:' . $this->realPath($file);
	}
}