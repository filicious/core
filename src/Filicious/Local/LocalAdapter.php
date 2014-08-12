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

use Filicious\Exception\InvalidArgumentException;
use Filicious\Internals\AbstractAdapter;
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
use Filicious\Plugin\DiskSpace\DiskSpaceAwareAdapterInterface;
use Filicious\Plugin\Hash\HashAwareAdapterInterface;
use Filicious\Plugin\Link\LinkAwareAdapterInterface;
use Filicious\Plugin\Mime\MimeAwareAdapterInterface;

/**
 * Local filesystem adapter.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class LocalAdapter
	extends AbstractAdapter
	implements MimeAwareAdapterInterface, HashAwareAdapterInterface, LinkAwareAdapterInterface,
				  DiskSpaceAwareAdapterInterface
{

	/**
	 * @var string
	 */
	protected $basePath;

	/**
	 * Create a new local adapter using a local pathname as base pathname.
	 *
	 * @param string $basePath The local base pathname.
	 */
	public function __construct($basePath = null)
	{
		$basePath = Util::normalizePath($basePath);

		if (empty($basePath)) {
			throw new InvalidArgumentException('Pathname cannot be empty');
		}
		if (!is_dir($basePath)) {
			throw new InvalidArgumentException(sprintf('Pathname "%s" is not a directory', $basePath));
		}

		$this->basePath = $basePath . '/';
	}

	/**
	 * Return the local base pathname.
	 *
	 * @return string
	 */
	public function getBasePath()
	{
		return $this->basePath;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isFile(Pathname $pathname)
	{
		if (!$this->exists($pathname)) {
			return false;
		}

		return is_file($this->getBasepath() . $pathname->local());
	}

	/**
	 * {@inheritdoc}
	 */
	public function isDirectory(Pathname $pathname)
	{
		if (!$this->exists($pathname)) {
			return false;
		}

		return is_dir($this->getBasepath() . $pathname->local());
	}

	/**
	 * {@inheritdoc}
	 */
	public function isLink(Pathname $pathname)
	{
		if (!$this->exists($pathname)) {
			return false;
		}

		return is_link($this->getBasepath() . $pathname->local());
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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

	/**
	 * {@inheritdoc}
	 */
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

	/**
	 * {@inheritdoc}
	 */
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function getStream(Pathname $pathname)
	{
		$this->checkFile($pathname);

		return new BuildInStream('file://' . $this->getBasePath() . $pathname->local(), $pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getStreamURL(Pathname $pathname)
	{
		// TODO get stream protocol from filesystem!
	}

	/*
	 * ------------------------------------------------------------
	 *                          Mime plugin
	 * ------------------------------------------------------------
	 */

	/**
	 * {@inheritdoc}
	 */
	public function getMimeName(Pathname $pathname)
	{
		$this->checkFile($pathname);

		$self = $this;
		return Util::executeFunction(
			function () use ($pathname, $self) {
				return finfo_file(
					Util::getFileInfo(),
					$self->getBasePath() . $pathname->local(),
					FILEINFO_NONE
				);
			},
			'Filicious\Exception\AdapterException',
			0,
			'Could not get mime name of %s.',
			$pathname
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMimeType(Pathname $pathname)
	{
		$this->checkFile($pathname);

		$self = $this;
		return Util::executeFunction(
			function () use ($pathname, $self) {
				return finfo_file(
					Util::getFileInfo(),
					$self->getBasePath() . $pathname->local(),
					FILEINFO_MIME_TYPE
				);
			},
			'Filicious\Exception\AdapterException',
			0,
			'Could not get mime type of %s.',
			$pathname
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMimeEncoding(Pathname $pathname)
	{
		$this->checkFile($pathname);

		$self = $this;
		return Util::executeFunction(
			function () use ($pathname, $self) {
				return finfo_file(
					Util::getFileInfo(),
					$self->getBasePath() . $pathname->local(),
					FILEINFO_MIME_ENCODING
				);
			},
			'Filicious\Exception\AdapterException',
			0,
			'Could not get mime encoding of %s.',
			$pathname
		);
	}

	/*
	 * ------------------------------------------------------------
	 *                          Hash plugin
	 * ------------------------------------------------------------
	 */

	/**
	 * {@inheritdoc}
	 */
	public function getHash(Pathname $pathname, $algorithm, $binary)
	{
		$this->checkFile($pathname);

		$self = $this;
		return Util::executeFunction(
			function () use ($pathname, $algorithm, $binary, $self) {
				return hash_file(
					$self->getBasePath() . $pathname->local(),
					$algorithm,
					$binary
				);
			},
			'Filicious\Exception\AdapterException',
			0,
			'Could not calculate %s hash of %s.',
			$algorithm,
			$pathname
		);
	}

	/*
	 * ------------------------------------------------------------
	 *                          Link plugin
	 * ------------------------------------------------------------
	 */

	/**
	 * {@inheritdoc}
	 */
	public function isLink(Pathname $pathname)
	{
		if (!$this->exists($pathname)) {
			return false;
		}

		return is_link($this->getBasePath() . $pathname->local());
	}

	/**
	 * Receive the link target from symbolic links.
	 *
	 * @return string|null
	 */
	public function getLinkTarget(Pathname $pathname)
	{
		if (!$this->isLink($pathname)) {
			return null;
		}

		return readlink($this->getBasePath() . $pathname->local());
	}

	/*
	 * ------------------------------------------------------------
	 *                       Disk space plugin
	 * ------------------------------------------------------------
	 */

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function getTotalSpace(Pathname $pathname)
	{
		if (!$this->isDirectory($pathname)) {
			$parentPathname = $pathname->parent();

			return $parentPathname->localAdapter()->getTotalSpace($parentPathname);
		}

		$self = $this;
		return Util::executeFunction(
			function () use ($pathname, $self) {
				return disk_total_space(
					$self->getBasePath() . $pathname->local()
				);
			},
			'Filicious\Exception\AdapterException',
			0,
			'Could not get total space for %s.',
			$pathname
		);
	}

}
