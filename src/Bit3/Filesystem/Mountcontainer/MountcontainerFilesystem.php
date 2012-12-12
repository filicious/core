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

namespace Bit3\Filesystem\Mountcontainer;

use Bit3\Filesystem\File;
use Bit3\Filesystem\Filesystem;
use Bit3\Filesystem\AbstractSimpleFilesystem;
use Bit3\Filesystem\FilesystemException;
use Bit3\Filesystem\Util;

/**
 * Virtual filesystem structure.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MountcontainerFilesystem
	extends AbstractSimpleFilesystem
{
	/**
	 * @var string The name of the config class used by instances of this
	 * 		filesystem implementation. Override in concrete classes to specify
	 * 		another config class.
	 */
	const CONFIG_CLASS = 'Bit3\Filesystem\Mountcontainer\MountcontainerFilesystemConfig';

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

		if (array_key_exists($path, $this->map))
		{
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
		return array_filter(array_keys($this->mounts),
		function ($pattern) {
			return substr($pattern, 0, -1);
		});
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
		if ($pattern == $path)
		{
			return new MountcontainerFile($path, $this->mounts[$path]->getRoot(), $this);
			} else {
				if ($filesystem == $this)
				{
					$allMounts = array_filter($this->mounts(), function ($path) use ($path) {
						return substr($path, 0, strlen($path)) == $path;
					});
					if (count($allMounts) > 0)
					{
						return new MountcontainerFile($path, null, $this);
					}
					return NULL;
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

	public function getTypeOf($objFile)
	{
		if (!$objFile->getSubFile())
		{
			return File::TYPE_DIRECTORY;
		} else {
			return $objFile->getSubFile()->getType();
		}
	}

	/**
	 * Get the link target of the link.
	 *
	 * @return string
	 */
	public function getLinkTargetOf($objFile)
	{
		return $objFile->isLink() ? $objFile->getSubFile()->getLinkTarget() : null;
	}

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getAccessTimeOf($objFile)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->getAccessTime();
		}
		return false;
	}

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setAccessTimeOf($objFile, $time)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->setAccessTime($time);
		}
		return false;
	}

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getCreationTimeOf($objFile)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->getCreationTime();
		}
		return false;
	}

	/**
	 * Return the time that the file denoted by this pathname was las modified.
	 *
	 * @return int
	 */
	public function getModifyTimeOf($objFile)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->getModifyTime();
		}
		return false;
	}

	/**
	 * Sets the last-modified time of the file or directory named by this pathname.
	 *
	 * @param int $time
	 */
	public function setModifyTimeOf($objFile, $time)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->setModifyTime($time);
		}
		return false;
	}

	/**
	 * Sets access and modification time of file.
	 *
	 * @param File $objFile the file to modify
	 * @param int  $time
	 * @param int  $atime
	 *
	 * @return bool
	 */
	public function touch($objFile, $time = null, $atime = null, $doNotCreate = false)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->touch($time, $atime, $doNotCreate);
		}
		return false;
	}


	/**
	 * Get the size of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getSizeOf($objFile)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->getSize();
		}
		return 0;
	}

	/**
	 * Get the owner of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getOwnerOf($objFile)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->getOwner();
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
	public function setOwnerOf($objFile, $user)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->setOwner($user);
		}
		return false;
	}

	/**
	 * Get the group of the file denoted by this pathname.
	 *
	 * @return string|int
	 */
	public function getGroupOf($objFile)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->getGroup();
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
	public function setGroupOf($objFile, $group)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->setGroup($group);
		}
		return false;
	}

	/**
	 * Get the mode of the file denoted by this pathname.
	 *
	 * @return int
	 */
	public function getModeOf($objFile)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->getMode();
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
	public function setModeOf($objFile, $mode)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->setMode();
		}
		return false;
	}


	/**
	 * Test whether this pathname is readable.
	 *
	 * @return bool
	 */
	public function isThisReadable($objFile)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->isReadable();
		}
		return true;
	}

	/**
	 * Test whether this pathname is writeable.
	 *
	 * @return bool
	 */
	public function isThisWritable($objFile)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->isWritable();
		}
		return false;
	}

	/**
	 * Test whether this pathname is executeable.
	 *
	 * @return bool
	 */
	public function isThisExecutable($objFile)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->isExecutable();
		}
		return true;
	}

	/**
	 * Checks whether a file or directory exists.
	 *
	 * @return bool
	 */
	public function exists($objFile)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->exists();
		}
		return true;
	}

	/**
	 * Delete a file or directory.
	 *
	 * @param File $objFile the file
	 *
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function delete($objFile, $recursive = false, $force = false)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->delete($recursive, $force);
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
	public function copyTo($objFile, File $destination, $parents = false)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->copy($destination, $parents);
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
	public function moveTo($objFile, File $destination)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->copy($destination);
		}
		return false;
	}

	/**
	 * Makes directory
	 *
	 * @return bool
	 */
	public function createDirectory($objFile, $parents = false)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->createDirectory();
		}
		return true;
	}

	/**
	 * Create new empty file.
	 *
	 * @return bool
	 */
	public function createFile($objFile, $parents = false)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->createFile();
		}
		return false;
	}

	/**
	 * Get contents of the file. Returns <em>null</em> if file does not exists
	 * and <em>false</em> on error (e.a. if file is a directory).
	 *
	 * @return string|null|bool
	 */
	public function getContentsOf($objFile)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->getContents();
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
	public function setContentsOf($objFile, $content)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->setContents($content);
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
	public function appendContentsTo($objFile, $content)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->appendContents($content);
		}
		return false;
	}

	/**
	 * Truncate a file to a given length. Returns the new length or
	 * <em>false</em> on error (e.a. if file is a directory).
	 * @param int $size
	 *
	 * @return int|bool
	 */
	public function truncate($objFile, $size = 0)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->truncate($size);
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
	public function open($objFile, $mode = 'rb')
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->open($mode);
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
	public function getMIMENameOf($objFile)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->getMIMEName();
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
	public function getMIMETypeOf($objFile)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->getMIMEType();
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
	public function getMIMEEncodingOf($objFile)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->getMIMEEncoding();
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
	public function getMD5Of($objFile, $raw = false)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->getMD5($raw);
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
	public function getSHA1Of($objFile, $raw = false)
	{
		if ($objFile->getSubFile())
		{
			return $objFile->getSubFile()->getSHA1($raw);
		}
		return false;
	}

	/**
	 * List all files within a sub file.
	 *
	 * @param VirtualFile|MergedFile $objFile
	 *
	 * @param mixed filter options.
	 *
	 * @return array<File>
	 */
	public function lsFile(/*$objFile, ... */)
	{
		$args = func_get_args();
		$objFile = array_shift($args);
		list($recursive, $bitmask, $globs, $callables, $globSearchPatterns) = Util::buildFilters($objFile, $args);

		$allMounts = $this->mounts();
		$filterBase = $objFile->getPathname();
		// special case, we are the root element.
		if ($filterBase != '/')
		{
			$filterBase .= '/';
		}
		$offset = strlen($filterBase);

		// filter out non matching mount points in parent vfs
		$allMounts = array_filter($allMounts, function ($path) use ($filterBase) {
			return substr($path, 0, strlen($filterBase)) == $filterBase;
		});

		// get unique virtual children list
		$allRoots = array_unique(array_map(function ($path) use ($filterBase, $offset) {

			$length = strpos($path, '/', $offset+1) - $offset;

			if ($length > 0)
			{
				return substr($path, $offset, $length);
			}

			return substr($path, $offset);

		}, $allMounts));

		$arrFiles = array();
		if ($objFile->getSubfile())
		{
			$arrFiles = call_user_func_array(array($objFile->getSubfile(), 'ls'), $args);
		}

		foreach($allRoots as $subpath)
		{
			$subfile = $objFile->getPathname() . '/' .  $subpath;

			//  is it a mount point? if so, return its root.
			if (in_array($subfile, $allMounts))
			{
				$arrFiles[] = $this->getFile($subfile);
			} else {
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
	public function getRealURLOf($objFile)
	{
		return 'mountcontainer:' . $this->realPath($objFile);
	}
}