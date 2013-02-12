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

namespace Filicious\Local;

use Filicious\File;
use Filicious\FilesystemConfig;
use Filicious\Internals\Adapter;
use Filicious\Internals\AbstractAdapter;
use Filicious\Internals\BoundFilesystemConfig;
use Filicious\Internals\Pathname;
use Filicious\Internals\Util;
use Filicious\Exception\FilesystemException;
use Filicious\Exception\AdapterException;
use Filicious\Exception\ConfigurationException;
use Filicious\Exception\DirectoryOverwriteDirectoryException;
use Filicious\Exception\DirectoryOverwriteFileException;
use Filicious\Exception\FileOverwriteDirectoryException;
use Filicious\Exception\FileOverwriteFileException;
Use Filicious\Stream\BuildInStream;
use Filicious\Stream\StreamMode;

/**
 * Local filesystem adapter.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class LocalAdapter
	extends AbstractAdapter
{
	protected $basepath = null;

	/**
	 * @param string|FilesystemConfig $basepath
	 */
	public function __construct($basepath = null)
	{
		$this->config = new BoundFilesystemConfig($this);
		$this->config
			->open()
			->set(FilesystemConfig::BASEPATH, null);

		if ($basepath instanceof FilesystemConfig) {
			$this->config->merge($basepath);
		}
		else if (is_string($basepath)) {
			$this->config->set(FilesystemConfig::BASEPATH, $basepath);
		}

		$this->config
			->set(FilesystemConfig::IMPLEMENTATION, __CLASS__)
			->commit();
	}

	public function getBasepath()
	{
		if ($this->basepath === null) {
			throw new ConfigurationException('basepath is not configured for local adapter.'); // TODO
		}
		return $this->basepath;
	}

	/**
	 * @see Filicious\Internals\Adapter::isFile()
	 */
	public function isFile(Pathname $pathname)
	{
		if (!$this->exists($pathname)) {
			return false;
		}

		return is_file($this->getBasepath() . $pathname->local());
	}

	/**
	 * @see Filicious\Internals\Adapter::isDirectory()
	 */
	public function isDirectory(Pathname $pathname)
	{
		if (!$this->exists($pathname)) {
			return false;
		}

		return is_dir($this->getBasepath() . $pathname->local());
	}

	/**
	 * @see Filicious\Internals\Adapter::isLink()
	 */
	public function isLink(Pathname $pathname)
	{
		if (!$this->exists($pathname)) {
			return false;
		}

		return is_link($this->getBasepath() . $pathname->local());
	}

	/**
	 * @see Filicious\Internals\Adapter::getAccessTime()
	 */
	public function getAccessTime(Pathname $pathname)
	{
		$this->requireExists($pathname);

		$self = $this;
		return new \DateTime(
			'@' . $this->execute(
				function() use ($pathname, $self) {
					return fileatime(
						$self->getBasepath() . $pathname->local()
					);
				},
				0,
				'Could not get access time of %s.',
				$pathname
			)
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::setAccessTime()
	 */
	public function setAccessTime(Pathname $pathname, \DateTime $time)
	{
		$this->requireExists($pathname);

		$self = $this;
		$this->execute(
			function() use ($pathname, $time, $self) {
				return touch(
					$self->basepath . $pathname->local(),
					$self->getModifyTime($pathname),
					$time->getTimestamp()
				);
			},
			0,
			'Could not set access time of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::getCreationTime()
	 */
	public function getCreationTime(Pathname $pathname)
	{
		$this->requireExists($pathname);

		$self = $this;
		return new \DateTime(
			'@' . $this->execute(
				function() use ($pathname, $self) {
					return filectime(
						$self->getBasepath() . $pathname->local()
					);
				},
				0,
				'Could not get creation time of %s.',
				$pathname
			)
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::getCreationTime()
	 */
	public function getModifyTime(Pathname $pathname)
	{
		$this->requireExists($pathname);

		$self = $this;
		return new \DateTime(
			'@' . $this->execute(
				function() use ($pathname, $self) {
					return filemtime(
						$self->getBasepath() . $pathname->local()
					);
				},
				0,
				'Could not get modify time of %s.',
				$pathname
			)
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::setModifyTime()
	 */
	public function setModifyTime(Pathname $pathname, \DateTime $time)
	{
		$this->requireExists($pathname);

		$self = $this;
		$this->execute(
			function() use ($pathname, $time, $self) {
				return touch(
					$self->getBasepath() . $pathname->local(),
					$time->getTimestamp(),
					$this->getAccessTime($pathname)
				);
			},
			0,
			'Could not set modify time of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::touch()
	 */
	public function touch(Pathname $pathname, \DateTime $time, \DateTime $atime, $create)
	{
		if (!$create) {
			$this->requireExists($pathname);
		}

		$self = $this;
		$this->execute(
			function() use ($pathname, $time, $atime, $self) {
				return touch(
					$self->getBasepath() . $pathname->local(),
					$time->getTimestamp(),
					$atime->getTimestamp()
				);
			},
			0,
			'Could not touch %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::getSize()
	 */
	public function getSize(Pathname $pathname, $recursive)
	{
		$this->requireExists($pathname);

		// get directory size
		if ($this->isDirectory($pathname)) {
			// calculate complete directory size
			if ($recursive) {
				$size = 0;

				$iterator = $this->getIterator($pathname, array());

				foreach ($iterator as $pathname) {
					$size += $this->fs
						->getFile($pathname)
						->getSize(true);
				}

				return $size;
			}

			// a directory itself has no size (per definition)
			else {
				return 0;
			}
		}

		// get file size
		else {
			$self = $this;
			return $this->execute(
				function() use ($pathname, $self) {
					return filesize(
						$self->getBasepath() . $pathname->local()
					);
				},
				0,
				'Could not get size of %s.',
				$pathname
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::getSize()
	 */
	public function getOwner(Pathname $pathname)
	{
		$this->requireExists($pathname);

		$self = $this;
		return $this->execute(
			function() use ($pathname, $self) {
				return fileowner(
					$self->getBasepath() . $pathname->local()
				);
			},
			0,
			'Could not get owner of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::setOwner()
	 */
	public function setOwner(Pathname $pathname, $user)
	{
		$this->requireExists($pathname);

		$self = $this;
		$this->execute(
			function() use ($pathname, $user, $self) {
				return chown(
					$self->getBasepath() . $pathname->local(),
					$user
				);
			},
			0,
			'Could not set owner of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::getGroup()
	 */
	public function getGroup(Pathname $pathname)
	{
		$this->requireExists($pathname);

		$self = $this;
		return $this->execute(
			function() use ($pathname, $self) {
				return filegroup(
					$self->getBasepath() . $pathname->local()
				);
			},
			0,
			'Could not get group of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::setGroup()
	 */
	public function setGroup(Pathname $pathname, $group)
	{
		$this->requireExists($pathname);

		$self = $this;
		$this->execute(
			function() use ($pathname, $group, $self) {
				return chgrp(
					$self->getBasepath() . $pathname->local(),
					$group
				);
			},
			0,
			'Could not set group of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::getMode()
	 */
	public function getMode(Pathname $pathname)
	{
		$this->requireExists($pathname);

		$self = $this;
		return $this->execute(
			function() use ($pathname, $self) {
				return fileperms(
					$self->getBasepath() . $pathname->local()
				);
			},
			0,
			'Could not get mode of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::setMode()
	 */
	public function setMode(Pathname $pathname, $mode)
	{
		$this->requireExists($pathname);

		$self = $this;
		$this->execute(
			function() use ($pathname, $mode, $self) {
				return chmod(
					$self->getBasepath() . $pathname->local(),
					$mode
				);
			},
			0,
			'Could not set mode of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::isReadable()
	 */
	public function isReadable(Pathname $pathname)
	{
		$this->requireExists($pathname);

		$self = $this;
		return $this->execute(
			function() use ($pathname, $self) {
				return is_readable(
					$self->getBasepath() . $pathname->local()
				);
			},
			0,
			'Could not get readable state of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::isWritable()
	 */
	public function isWritable(Pathname $pathname)
	{
		$this->requireExists($pathname);

		$self = $this;
		return $this->execute(
			function() use ($pathname, $self) {
				return is_writable(
					$self->getBasepath() . $pathname->local()
				);
			},
			0,
			'Could not get writeable state of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::isExecutable()
	 */
	public function isExecutable(Pathname $pathname)
	{
		$this->requireExists($pathname);

		try {
			$executable = is_executable($this->getBasepath() . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new AdapterException(
				sprintf('Could not get executable state of %s', $pathname),
				$e->getCode(),
				$e
			);
		}

		return $executable;
	}

	/**
	 * @see Filicious\Internals\Adapter::exists()
	 */
	public function exists(Pathname $pathname)
	{
		try {
			$exists = file_exists($this->getBasepath() . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new AdapterException(
				sprintf('Could not get exists state of %s', $pathname),
				$e->getCode(),
				$e
			);
		}

		return $exists;
	}

	/**
	 * @see Filicious\Internals\Adapter::delete()
	 */
	public function delete(Pathname $pathname, $recursive, $force)
	{
		$this->requireExists($pathname);

		if ($this->isDirectory($pathname)) {
			// TODO Handling $force flag needed here!

			// recursive delete directories
			if ($recursive) {
				$iterator = $this->getIterator($pathname, array());

				foreach ($iterator as $pathname) {
					$this->fs
						->getFile($pathname)
						->delete($recursive, $force);
				}
			}
			else if ($this->count($pathname, array()) > 0) {
				return false;
			}

			$self = $this;
			$this->execute(
				function() use ($pathname, $self) {
					return rmdir(
						$self->getBasepath() . $pathname->local()
					);
				},
				0,
				'Could not delete directory %s.',
				$pathname
			);
		}
		else {
			// Handling $force flag
			if (!$this->isWritable($pathname)) {
				if ($force) {
					$this->setMode($pathname, 0666);
				}
				else {
					return false;
				}
			}

			$self = $this;
			return $this->execute(
				function() use ($pathname, $self) {
					return unlink(
						$self->getBasepath() . $pathname->local()
					);
				},
				0,
				'Could not delete file %s.',
				$pathname
			);
		}
	}

	public function nativeCopy(
		Pathname $srcPathname,
		Pathname $dstPathname
	) {
		return $this->execute(
			function() use ($srcPathname, $dstPathname) {
				return copy(
					$srcPathname->localAdapter()->getBasepath() . $srcPathname->local(),
					$dstPathname->localAdapter()->getBasepath() . $dstPathname->local()
				);
			},
			0,
			'Could not copy %s to %s.',
			$srcPathname,
			$dstPathname
		);
	}

	public function nativeMove(
		Pathname $srcPathname,
		Pathname $dstPathname
	) {
		return $this->execute(
			function() use ($srcPathname, $dstPathname) {
				return rename(
					$srcPathname->localAdapter()->getBasepath() . $srcPathname->local(),
					$dstPathname->localAdapter()->getBasepath() . $dstPathname->local()
				);
			},
			0,
			'Could not move %s to %s.',
			$srcPathname,
			$dstPathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::createDirectory()
	 */
	public function createDirectory(Pathname $pathname, $parents)
	{
		// if exists, check if pathname is allready a directory
		if ($this->exists($pathname)) {
			return $this->isDirectory($pathname);
		}

		$self = $this;
		return $this->execute(
			function() use ($pathname, $parents, $self) {
				// create with parents
				if ($parents) {
					// TODO: apply umask.
					return mkdir($self->getBasepath() . $pathname->local(), 0777, true);
				}
				else {
					$parentPathname = $pathname->parent();

					$parentPathname->localAdapter()->requireExists($parentPathname);

					return mkdir($self->getBasepath() . $pathname->local());
				}
			},
			0,
			'Could not create directory %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::createFile()
	 */
	public function createFile(Pathname $pathname, $parents)
	{
		$parentPathname = $pathname->parent();

		if ($parents) {
			try {
				$parentPathname->localAdapter()->createDirectory($parentPathname, true);
			}
			catch (FilesystemException $e) {
				throw new FilesystemException(
					sprintf('Could not create parent path %s to create file %s!', $parentPathname, $pathname),
					0,
					$e
				);
			}
		}
		else {
			try {
				$parentPathname->localAdapter()->checkDirectory($parentPathname);
			}
			catch (FilesystemException $e) {
				throw new FilesystemException(
					sprintf('Could not create file %s, parent directory %s does not exists!', $pathname, $parentPathname),
					0,
					$e
				);
			}
		}

		$self = $this;
		$this->execute(
			function() use ($pathname, $self) {
				return touch(
					$self->getBasepath() . $pathname->local()
				);
			},
			0,
			'Could not create file %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::getContents()
	 */
	public function getContents(Pathname $pathname)
	{
		$this->checkFile($pathname);

		$self = $this;
		return $this->execute(
			function() use ($pathname, $self) {
				return file_get_contents(
					$self->getBasepath() . $pathname->local()
				);
			},
			0,
			'Could not get contents of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::setContents()
	 */
	public function setContents(Pathname $pathname, $content, $create)
	{
		if (!$create) {
			$this->requireExists($pathname);
		}

		if ($this->exists($pathname)) {
			$this->checkFile($pathname);
		}

		$self = $this;
		$this->execute(
			function() use ($pathname, $content, $self) {
				return file_put_contents(
					$self->getBasepath() . $pathname->local(),
					$content
				);
			},
			0,
			'Could not set contents of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::appendContents()
	 */
	public function appendContents(Pathname $pathname, $content, $create)
	{
		if (!$create) {
			$this->requireExists($pathname);
		}

		if ($this->exists($pathname)) {
			$this->checkFile($pathname);
		}

		$self = $this;
		return $this->execute(
			function() use ($pathname, $content, $self) {
				$result = false;
				if (false !== ($f = fopen($self->getBasepath() . $pathname->local(), 'ab'))) {
					$result = fwrite($f, $content);
					fclose($f);
				}
				return $result;
			},
			0,
			'Could not append contents to %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::truncate()
	 */
	public function truncate(Pathname $pathname, $size)
	{
		$this->checkFile($pathname);

		$self = $this;
		return $this->execute(
			function() use ($pathname, $size, $self) {
				$result = false;
				if (false !== ($f = fopen($self->getBasepath() . $pathname->local(), 'ab'))) {
					$result = ftruncate($f, $size);
					fclose($f);
				}
				return $result;
			},
			0,
			'Could not truncate file %s to %s.',
			$pathname,
			$size
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::getStream()
	 */
	public function getStream(Pathname $pathname)
	{
		$this->checkFile($pathname);

		return new BuildInStream('file://' . $this->getBasepath() . $pathname->local(), $pathname);
	}

	/**
	 * @see Filicious\Internals\Adapter::getStreamURL()
	 */
	public function getStreamURL(Pathname $pathname)
	{
		// TODO get stream protocol from filesystem!
	}

	/**
	 * @see Filicious\Internals\Adapter::getMIMEName()
	 */
	public function getMIMEName(Pathname $pathname)
	{
		$this->checkFile($pathname);

		$self = $this;
		return $this->execute(
			function() use ($pathname, $self) {
				return finfo_file(
					FS::getFileInfo(),
					$self->getBasepath() . $pathname->local(),
					FILEINFO_NONE
				);
			},
			0,
			'Could not get mime name of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::getMIMEType()
	 */
	public function getMIMEType(Pathname $pathname)
	{
		$this->checkFile($pathname);

		$self = $this;
		return $this->execute(
			function() use ($pathname, $self) {
				return finfo_file(
					FS::getFileInfo(),
					$self->getBasepath() . $pathname->local(),
					FILEINFO_MIME_TYPE
				);
			},
			0,
			'Could not get mime type of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::getMIMEEncoding()
	 */
	public function getMIMEEncoding(Pathname $pathname)
	{
		$this->checkFile($pathname);

		$self = $this;
		return $this->execute(
			function() use ($pathname, $self) {
				return finfo_file(
					FS::getFileInfo(),
					$self->getBasepath() . $pathname->local(),
					FILEINFO_MIME_ENCODING
				);
			},
			0,
			'Could not get mime encoding of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::getMD5()
	 */
	public function getMD5(Pathname $pathname, $binary)
	{
		$this->checkFile($pathname);

		$self = $this;
		return $this->execute(
			function() use ($pathname, $binary, $self) {
				return md5_file(
					$self->getBasepath() . $pathname->local(),
					$binary
				);
			},
			0,
			'Could not calculate md5 sum of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::getSHA1()
	 */
	public function getSHA1(Pathname $pathname, $binary)
	{
		$this->checkFile($pathname);

		$self = $this;
		return $this->execute(
			function() use ($pathname, $binary, $self) {
				return sha1_file(
					$self->getBasepath() . $pathname->local(),
					$binary
				);
			},
			0,
			'Could not calculate sha1 sum of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::ls()
	 */
	public function ls(Pathname $pathname)
	{
		$this->checkDirectory($pathname);

		$self = $this;
		$files = $this->execute(
			function() use ($pathname, $self) {
				$temp = scandir(
					$self->getBasepath() . $pathname->local()
				);
				return $temp;
			},
			0,
			'Could not list contents of %s.',
			$pathname
		);

		natcasesort($files);

		return array_values(
			array_filter(
				$files,
				function ($file) {
					return $file !== '.' && $file !== '..';
				}
			)
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::getFreeSpace()
	 */
	public function getFreeSpace(Pathname $pathname)
	{
		if (!$this->isDirectory($pathname)) {
			$parentPathname = $pathname->parent();

			return $parentPathname->localAdapter()->getFreeSpace($parentPathname);
		}

		$self = $this;
		return $this->execute(
			function() use ($pathname, $self) {
				return disk_free_space(
					$self->getBasepath() . $pathname->local()
				);
			},
			0,
			'Could not get free space for %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::getTotalSpace()
	 */
	public function getTotalSpace(Pathname $pathname)
	{
		if (!$this->isDirectory($pathname)) {
			$parentPathname = $pathname->parent();

			return $parentPathname->localAdapter()->getTotalSpace($parentPathname);
		}

		$self = $this;
		return $this->execute(
			function() use ($pathname, $self) {
				return disk_total_space(
					$self->getBasepath() . $pathname->local()
				);
			},
			0,
			'Could not get total space for %s.',
			$pathname
		);
	}

	protected function execute($callback, $errorCode, $errorMessage) {
		$error = null;

		try {
			$result = $callback();
		}
		catch (\ErrorException $e) {
			$error = $e;
		}

		if ($error !== null || $result === false) {
			throw new AdapterException(
				vsprintf(
					$errorMessage,
					array_slice(
						func_get_args(),
						3
					)
				),
				$errorCode,
				$e
			);
		}

		return $result;
	}

	/**
	 * Notify about config changes.
	 */
	public function notifyConfigChange()
	{
		$basepath = $this->config->get(FilesystemConfig::BASEPATH);

		if ($basepath) {
			$basepath = Util::normalizePath($basepath);

			if (!is_dir($basepath) && $this->config->get(FilesystemConfig::CREATE_BASEPATH)) {
					$this->execute(
						function() use ($basepath) {
							// second is_dir is required, because mkdir may return true even if only one,
							// but not all directories of the path are created!
							return mkdir($basepath, 0777, true) && is_dir($basepath);
						},
						0, // TODO,
						'Could not create basepath %s',
						$basepath
					);
			}

			$this->basepath = $basepath;
			return;
		}

		// TODO Logging missing basepath?
		$this->basepath = null;
	}
}
