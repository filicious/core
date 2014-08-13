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

use Filicious\Exception\AdapterException;
use Filicious\Exception\FilesystemException;
use Filicious\Exception\InvalidArgumentException;
use Filicious\Exception\StreamNotSupportedException;
use Filicious\Internals\AbstractAdapter;
use Filicious\Internals\Pathname;
use Filicious\Internals\Util;
use Filicious\Internals\Validator;
use Filicious\Plugin\DiskSpace\DiskSpaceAwareAdapterInterface;
use Filicious\Plugin\Hash\HashAwareAdapterInterface;
use Filicious\Plugin\Link\LinkAwareAdapterInterface;
use Filicious\Plugin\Mime\MimeAwareAdapterInterface;
use Filicious\Stream\BuildInStream;

/**
 * Local filesystem adapter.
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 */
class LocalAdapter
	extends AbstractAdapter
	implements DiskSpaceAwareAdapterInterface, HashAwareAdapterInterface, LinkAwareAdapterInterface, MimeAwareAdapterInterface
{

	/**
	 * @var string
	 */
	protected $basePath;

	/**
	 * Create a new local adapter using a local pathname as base pathname.
	 *
	 * @param string $basePath The local base pathname.
	 *
	 * @throws InvalidArgumentException
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

		return is_file($this->getBasePath() . $pathname->local());
	}

	/**
	 * {@inheritdoc}
	 */
	public function isDirectory(Pathname $pathname)
	{
		if (!$this->exists($pathname)) {
			return false;
		}

		return is_dir($this->getBasePath() . $pathname->local());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAccessTime(Pathname $pathname)
	{
		Validator::requireExists($pathname);

		$self      = $this;
		$timestamp = Util::executeFunction(
			function () use ($pathname, $self) {
				return fileatime(
					$self->getBasePath() . $pathname->local()
				);
			},
			'Filicious\Exception\AdapterException',
			0,
			'Could not get access time of %s.',
			$pathname
		);

		$date = new \DateTime();
		$date->setTimestamp($timestamp);

		return $date;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setAccessTime(Pathname $pathname, \DateTime $time)
	{
		Validator::requireExists($pathname);

		$self = $this;
		Util::executeFunction(
			function () use ($pathname, $time, $self) {
				return touch(
					$self->basePath . $pathname->local(),
					$self->getModifyTime($pathname)->getTimestamp(),
					$time->getTimestamp()
				);
			},
			'Filicious\Exception\AdapterException',
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
		Validator::requireExists($pathname);

		$self      = $this;
		$timestamp = Util::executeFunction(
			function () use ($pathname, $self) {
				return filectime(
					$self->getBasePath() . $pathname->local()
				);
			},
			'Filicious\Exception\AdapterException',
			0,
			'Could not get creation time of %s.',
			$pathname
		);

		$date = new \DateTime();
		$date->setTimestamp($timestamp);
		return $date;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModifyTime(Pathname $pathname)
	{
		Validator::requireExists($pathname);

		$self      = $this;
		$timestamp = Util::executeFunction(
			function () use ($pathname, $self) {
				return filemtime(
					$self->getBasePath() . $pathname->local()
				);
			},
			'Filicious\Exception\AdapterException',
			0,
			'Could not get modify time of %s.',
			$pathname
		);

		$date = new \DateTime();
		$date->setTimestamp($timestamp);
		return $date;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setModifyTime(Pathname $pathname, \DateTime $time)
	{
		Validator::requireExists($pathname);

		$self = $this;
		Util::executeFunction(
			function () use ($pathname, $time, $self) {
				return touch(
					$self->getBasePath() . $pathname->local(),
					$time->getTimestamp(),
					$this->getAccessTime($pathname)->getTimestamp()
				);
			},
			'Filicious\Exception\AdapterException',
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
			Validator::requireExists($pathname);
		}

		$self = $this;
		Util::executeFunction(
			function () use ($pathname, $time, $atime, $self) {
				return touch(
					$self->getBasePath() . $pathname->local(),
					$time->getTimestamp(),
					$atime->getTimestamp()
				);
			},
			'Filicious\Exception\AdapterException',
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
		Validator::requireExists($pathname);

		// get directory size
		if ($this->isDirectory($pathname)) {
			// calculate complete directory size
			if ($recursive) {
				$size = 0;

				$iterator = $this->getIterator($pathname, array());

				foreach ($iterator as $pathname) {
					$size += $this->filesystem
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
			return Util::executeFunction(
				function () use ($pathname, $self) {
					return filesize(
						$self->getBasePath() . $pathname->local()
					);
				},
				'Filicious\Exception\AdapterException',
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
		Validator::requireExists($pathname);

		$self = $this;
		return Util::executeFunction(
			function () use ($pathname, $self) {
				return fileowner(
					$self->getBasePath() . $pathname->local()
				);
			},
			'Filicious\Exception\AdapterException',
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
		Validator::requireExists($pathname);

		$self = $this;
		Util::executeFunction(
			function () use ($pathname, $user, $self) {
				return chown(
					$self->getBasePath() . $pathname->local(),
					$user
				);
			},
			'Filicious\Exception\AdapterException',
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
		Validator::requireExists($pathname);

		$self = $this;
		return Util::executeFunction(
			function () use ($pathname, $self) {
				return filegroup(
					$self->getBasePath() . $pathname->local()
				);
			},
			'Filicious\Exception\AdapterException',
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
		Validator::requireExists($pathname);

		$self = $this;
		Util::executeFunction(
			function () use ($pathname, $group, $self) {
				return chgrp(
					$self->getBasePath() . $pathname->local(),
					$group
				);
			},
			'Filicious\Exception\AdapterException',
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
		Validator::requireExists($pathname);

		$self = $this;
		return Util::executeFunction(
			function () use ($pathname, $self) {
				return fileperms(
					$self->getBasePath() . $pathname->local()
				);
			},
			'Filicious\Exception\AdapterException',
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
		Validator::requireExists($pathname);

		$self = $this;
		Util::executeFunction(
			function () use ($pathname, $mode, $self) {
				return chmod(
					$self->getBasePath() . $pathname->local(),
					$mode
				);
			},
			'Filicious\Exception\AdapterException',
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
		Validator::requireExists($pathname);

		$self = $this;
		return Util::executeFunction(
			function () use ($pathname, $self) {
				return is_readable(
					$self->getBasePath() . $pathname->local()
				);
			},
			'Filicious\Exception\AdapterException',
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
		Validator::requireExists($pathname);

		$self = $this;
		return Util::executeFunction(
			function () use ($pathname, $self) {
				return is_writable(
					$self->getBasePath() . $pathname->local()
				);
			},
			'Filicious\Exception\AdapterException',
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
		Validator::requireExists($pathname);

		try {
			$executable = is_executable($this->getBasePath() . $pathname->local());
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
			$exists = file_exists($this->getBasePath() . $pathname->local());
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
		Validator::requireExists($pathname);

		if ($this->isDirectory($pathname)) {
			// TODO Handling $force flag needed here!

			// recursive delete directories
			if ($recursive) {
				$iterator = $this->getIterator($pathname, array());

				foreach ($iterator as $pathname) {
					$this->filesystem
						->getFile($pathname)
						->delete($recursive, $force);
				}
			}
			else if ($this->count($pathname, array()) > 0) {
				return false;
			}

			$self = $this;
			Util::executeFunction(
				function () use ($pathname, $self) {
					return rmdir(
						$self->getBasePath() . $pathname->local()
					);
				},
				'Filicious\Exception\AdapterException',
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
			return Util::executeFunction(
				function () use ($pathname, $self) {
					return unlink(
						$self->getBasePath() . $pathname->local()
					);
				},
				'Filicious\Exception\AdapterException',
				0,
				'Could not delete file %s.',
				$pathname
			);
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function nativeCopy(
		Pathname $srcPathname,
		Pathname $dstPathname
	) {
		return Util::executeFunction(
			function () use ($srcPathname, $dstPathname) {
				/** @var LocalAdapter $srcAdapter */
				$srcAdapter = $srcPathname->localAdapter();
				/** @var LocalAdapter $dstAdapter */
				$dstAdapter = $dstPathname->localAdapter();

				return copy(
					$srcAdapter->getBasepath() . $srcPathname->local(),
					$dstAdapter->getBasepath() . $dstPathname->local()
				);
			},
			'Filicious\Exception\AdapterException',
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
		return Util::executeFunction(
			function () use ($srcPathname, $dstPathname) {
				/** @var LocalAdapter $srcAdapter */
				$srcAdapter = $srcPathname->localAdapter();
				/** @var LocalAdapter $dstAdapter */
				$dstAdapter = $dstPathname->localAdapter();

				return rename(
					$srcAdapter->getBasepath() . $srcPathname->local(),
					$dstAdapter->getBasepath() . $dstPathname->local()
				);
			},
			'Filicious\Exception\AdapterException',
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
		return Util::executeFunction(
			function () use ($pathname, $parents, $self) {
				// create with parents
				if ($parents) {
					// TODO: apply umask.
					return mkdir($self->getBasePath() . $pathname->local(), 0777, true);
				}
				else {
					$parentPathname = $pathname->parent();

					Validator::requireExists($parentPathname);

					return mkdir($self->getBasePath() . $pathname->local());
				}
			},
			'Filicious\Exception\AdapterException',
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
			if (!$parentPathname->localAdapter()->isDirectory($parentPathname)) {
				throw new FilesystemException(
					sprintf('Could not create file %s, parent directory %s does not exists!', $pathname, $parentPathname),
					0
				);
			}
		}

		$self = $this;
		Util::executeFunction(
			function () use ($pathname, $self) {
				return touch(
					$self->getBasePath() . $pathname->local()
				);
			},
			'Filicious\Exception\AdapterException',
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
		Validator::checkFile($pathname);

		$self = $this;
		return Util::executeFunction(
			function () use ($pathname, $self) {
				return file_get_contents(
					$self->getBasePath() . $pathname->local()
				);
			},
			'Filicious\Exception\AdapterException',
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
			Validator::requireExists($pathname);
		}

		if ($this->exists($pathname)) {
			Validator::checkFile($pathname);
		}

		$self = $this;
		Util::executeFunction(
			function () use ($pathname, $content, $self) {
				return file_put_contents(
					$self->getBasePath() . $pathname->local(),
					$content
				);
			},
			'Filicious\Exception\AdapterException',
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
			Validator::requireExists($pathname);
		}

		if ($this->exists($pathname)) {
			Validator::checkFile($pathname);
		}

		$self = $this;
		return Util::executeFunction(
			function () use ($pathname, $content, $self) {
				$result = false;
				if (false !== ($f = fopen($self->getBasePath() . $pathname->local(), 'ab'))) {
					$result = fwrite($f, $content);
					fclose($f);
				}
				return $result;
			},
			'Filicious\Exception\AdapterException',
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
		Validator::checkFile($pathname);

		$self = $this;
		return Util::executeFunction(
			function () use ($pathname, $size, $self) {
				$result = false;
				if (false !== ($f = fopen($self->getBasePath() . $pathname->local(), 'ab'))) {
					$result = ftruncate($f, $size);
					fclose($f);
				}
				return $result;
			},
			'Filicious\Exception\AdapterException',
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
		Validator::checkDirectory($pathname);

		$self  = $this;
		$files = Util::executeFunction(
			function () use ($pathname, $self) {
				$temp = scandir(
					$self->getBasePath() . $pathname->local()
				);
				return $temp;
			},
			'Filicious\Exception\AdapterException',
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
		Validator::requireExists($pathname);

		if (!$this->filesystem->isStreamingEnabled()) {
			throw new StreamNotSupportedException($pathname);
		}

		return new BuildInStream('file://' . $this->getBasePath() . $pathname->full(), $pathname);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getStreamURL(Pathname $pathname)
	{
		Validator::requireExists($pathname);

		if (!$this->filesystem->isStreamingEnabled()) {
			throw new StreamNotSupportedException($pathname);
		}

		return $this->filesystem->getStreamPrefix() . $pathname->full();
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
		$self = $this;
		return Util::executeFunction(
			function () use ($pathname, $self) {
				return disk_free_space(
					$self->getBasePath() . $pathname->local()
				);
			},
			'Filicious\Exception\AdapterException',
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
		Validator::checkFile($pathname);

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
	 * {@inheritdoc}
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
	 *                          Mime plugin
	 * ------------------------------------------------------------
	 */

	/**
	 * {@inheritdoc}
	 */
	public function getMimeName(Pathname $pathname)
	{
		Validator::checkFile($pathname);

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
		Validator::checkFile($pathname);

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
		Validator::checkFile($pathname);

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

}
