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
use Filicious\Internals\Pathname;
use Filicious\Exception\FilesystemException;
use Filicious\Exception\AdapterException;
use Filicious\Exception\DirectoryOverwriteDirectoryException;
use Filicious\Exception\DirectoryOverwriteFileException;
use Filicious\Exception\FileOverwriteDirectoryException;
use Filicious\Exception\FileOverwriteFileException;
Use Filicious\Stream\BuildInStream;

/**
 * Local filesystem adapter.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class LocalAdapter
	extends AbstractAdapter
{

	protected $basepath;

	/**
	 * @param string|FilesystemConfig $basepath
	 */
	public function __construct($basepath)
	{
		if ($basepath instanceof FilesystemConfig) {
			$this->config = $basepath;
			$basepath = $basepath->get(FilesystemConfig::BASEPATH);
		}
		else {
			$this->config = new FilesystemConfig();
			$this->config
				->set(FilesystemConfig::IMPLEMENTATION, __CLASS__)
				->set(FilesystemConfig::BASEPATH, $basepath);
		}

		$this->basepath = $basepath;

		if (!is_dir($this->basepath)) {
			// TODO
			throw new \InvalidArgumentException();
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::isFile()
	 */
	public function isFile(Pathname $pathname)
	{
		$this->requireExists($pathname);

		return is_file($this->basepath . $pathname->local());
	}

	/**
	 * @see Filicious\Internals\Adapter::isDirectory()
	 */
	public function isDirectory(Pathname $pathname)
	{
		if (!$this->exists($pathname)) {
			return false;
		}

		return is_dir($this->basepath . $pathname->local());
	}

	/**
	 * @see Filicious\Internals\Adapter::isLink()
	 */
	public function isLink(Pathname $pathname)
	{
		if (!$this->exists($pathname)) {
			return false;
		}

		return is_link($this->basepath . $pathname->local());
	}

	/**
	 * @see Filicious\Internals\Adapter::getAccessTime()
	 */
	public function getAccessTime(Pathname $pathname)
	{
		$this->requireExists($pathname);

		$self = $this;
		return new \DateTime(
			$this->execute(
				function() use ($pathname, $self) {
					return fileatime(
						$this->basepath . $pathname->local()
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
			$this->execute(
				function() use ($pathname, $self) {
					return filectime(
						$this->basepath . $pathname->local()
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
			$this->execute(
				function() use ($pathname, $self) {
					return filemtime(
						$this->basepath . $pathname->local()
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
					$this->basepath . $pathname->local(),
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
					$this->basepath . $pathname->local(),
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
						$this->basepath . $pathname->local()
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
					$this->basepath . $pathname->local()
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
					$this->basepath . $pathname->local(),
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
					$this->basepath . $pathname->local()
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
					$this->basepath . $pathname->local(),
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
					$this->basepath . $pathname->local()
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
					$this->basepath . $pathname->local(),
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
					$this->basepath . $pathname->local()
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
					$this->basepath . $pathname->local()
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

		$self = $this;
		return $this->execute(
			function() use ($pathname, $self) {
				return is_executable(
					$this->basepath . $pathname->local()
				);
			},
			0,
			'Could not get executable state of %s.',
			$pathname
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::exists()
	 */
	public function exists(Pathname $pathname)
	{
		try {
			$exists = file_exists($this->basepath . $pathname->local());
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
						$this->basepath . $pathname->local()
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
						$this->basepath . $pathname->local()
					);
				},
				0,
				'Could not delete file %s.',
				$pathname
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::copyTo()
	 */
	public function copyTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags
	) {
		$dstPathname->localAdapter()->copyFrom(
			$dstPathname,
			$srcPathname,
			$flags
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::copyFrom()
	 */
	public function copyFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags
	) {
		/** @var Adapter $dstParentAdapter */
		$dstParentAdapter = $dstParentPathname = null;
		$this->getParent($dstPathname, $dstParentAdapter, $dstParentPathname);

		if ($flags & File::OPERATION_PARENTS) {
			$dstParentAdapter->createDirectory(
				$dstParentPathname,
				true
			);
		}
		else {
			$dstParentAdapter->checkDirectory($dstParentPathname);
		}

		$dstExists      = $this->exists($dstPathname);
		$srcIsDirectory = $srcPathname->localAdapter()->isDirectory($srcPathname);
		$dstIsDirectory = $this->isDirectory($dstPathname);

		// target not exists
		if (!$dstExists) {
			if ($srcIsDirectory) {
				$dstIsDirectory = true;
			}
			else {
				$dstIsDirectory = false;
			}
			// continue copy operation
		}

		// copy file -> directory
		else if (!$srcIsDirectory && $dstIsDirectory) {
			// replace directory with file
			if (!($flags & File::OPERATION_REJECT) && $flags & File::OPERATION_REPLACE) {
				$this->delete($dstPathname, true, false);
				$dstIsDirectory = false;
				// continue copy operation
			}

			// merge file into directory
			else if ($flags & File::OPERATION_MERGE) {
				$dstInsidePathname = $dstPathname->child($srcPathname);

				$srcPathname->localAdapter()->copyTo(
					$srcPathname,
					$dstInsidePathname->localAdapter(),
					$dstInsidePathname,
					$flags
				);
				return;
			}

			else {
				throw new FileOverwriteDirectoryException(
					$srcPathname,
					$dstPathname
				);
			}
		}
		// copy directory -> file
		else if ($srcIsDirectory && !$dstIsDirectory) {
			if (!($flags & File::OPERATION_REJECT) && $flags & File::OPERATION_REPLACE) {
				$this->delete($dstPathname, false, false);
				$this->createDirectory($dstPathname, false);
				$dstIsDirectory = true;
				// continue copy operation
			}

			else {
				throw new DirectoryOverwriteFileException(
					$srcPathname,
					$dstPathname
				);
			}
		}

		// copy directory -> directory
		if ($srcIsDirectory && $dstIsDirectory) {
			// replace target directory
			if (!($flags & File::OPERATION_REJECT) && $flags & File::OPERATION_REPLACE) {
				if ($dstExists) {
					$this->delete($dstPathname, true, false);
				}
				$this->createDirectory($dstPathname, false);

				$flags |= File::OPERATION_RECURSIVE;
				// continue recursive copy
			}

			// recursive merge directories
			if ($flags & File::OPERATION_RECURSIVE) {
				$iterator = $srcPathname->localAdapter()->getIterator($srcPathname, array());

				/** @var Pathname $srcChildPathname */
				foreach ($iterator as $srcChildPathname) {
					$srcPathname->localAdapter()->getRootAdapter()->copyTo(
						$srcChildPathname,
						$this,
						$dstPathname->child($srcChildPathname),
						$flags
					);
				}
			}

			else {
				throw new DirectoryOverwriteDirectoryException(
					$srcPathname,
					$dstPathname
				);
			}
		}

		// copy file -> file
		else if (!$srcIsDirectory && !$dstIsDirectory) {
			if (!($flags & File::OPERATION_REJECT) && $flags & File::OPERATION_REPLACE) {
				// native copy
				if ($srcPathname->localAdapter() instanceof LocalAdapter) {
					$self = $this;
					return $this->execute(
						function() use ($srcPathname, $dstPathname, $self) {
							return copy(
								$srcPathname->localAdapter()->basepath . $srcPathname->local(),
								$this->basepath . $dstPathname->local()
							);
						},
						0,
						'Could not copy %s to %s.',
						$srcPathname,
						$dstPathname
					);
				}

				// stream copy
				else {
					$self = $this;
					return $this->execute(
						function() use ($srcPathname, $dstPathname, $self) {
							$srcStream = $srcPathname->localAdapter()->open($srcPathname, 'rb');
							$dstStream = $this->open($dstPathname, 'wb');

							$result = stream_copy_to_stream(
								$srcStream,
								$dstStream
							);

							fclose($srcStream);
							fclose($dstStream);

							return $result;
						},
						0,
						'Could not copy %s to %s.',
						$srcPathname,
						$dstPathname
					);
				}
			}

			else {
				throw new FileOverwriteFileException(
					$srcPathname,
					$dstPathname
				);
			}
		}

		// illegal state
		else {
			throw new FilesystemException('Illegal state!');
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::moveTo()
	 */
	public function moveTo(
		Pathname $srcPathname,
		Pathname $dstPathname,
		$flags
	) {
		$dstPathname->localAdapter()->moveFrom(
			$dstPathname,
			$srcPathname,
			$flags
		);
	}

	/**
	 * @see Filicious\Internals\Adapter::moveFrom()
	 */
	public function moveFrom(
		Pathname $dstPathname,
		Pathname $srcPathname,
		$flags
	) {
		/** @var Adapter $dstParentAdapter */
		$dstParentAdapter = $dstParentPathname = null;
		$this->getParent($dstPathname, $dstParentAdapter, $dstParentPathname);

		if ($flags & File::OPERATION_PARENTS) {
			$dstParentAdapter->createDirectory(
				$dstParentPathname,
				true
			);
		}
		else {
			$dstParentAdapter->checkDirectory($dstParentPathname);
		}

		$dstExists      = $this->exists($dstPathname);
		$srcIsDirectory = $srcPathname->localAdapter()->isDirectory($srcPathname);
		$dstIsDirectory = $this->isDirectory($dstPathname);

		// target not exists
		if (!$dstExists) {
			if ($srcIsDirectory) {
				$dstIsDirectory = true;
			}
			else {
				$dstIsDirectory = false;
			}
			// continue move operation
		}

		// move file -> directory
		else if (!$srcIsDirectory && $dstIsDirectory) {
			// replace directory with file
			if (!($flags & File::OPERATION_REJECT) && $flags & File::OPERATION_REPLACE) {
				$this->delete($dstPathname, true, false);
				$dstIsDirectory = false;
				// continue move operation
			}

			// merge file into directory
			else if ($flags & File::OPERATION_MERGE) {
				$dstInsidePathname = $dstPathname->child($srcPathname);

				$srcPathname->localAdapter()->moveTo(
					$srcPathname,
					$dstInsidePathname,
					$flags
				);
				return;
			}

			else {
				throw new FileOverwriteDirectoryException(
					$srcPathname,
					$dstPathname
				);
			}
		}
		// move directory -> file
		else if ($srcIsDirectory && !$dstIsDirectory) {
			if (!($flags & File::OPERATION_REJECT) && $flags & File::OPERATION_REPLACE) {
				$this->delete($dstPathname, false, false);
				$this->createDirectory($dstPathname, false);
				$dstIsDirectory = true;
				// continue move operation
			}

			else {
				throw new DirectoryOverwriteFileException(
					$srcPathname,
					$dstPathname
				);
			}
		}

		// move directory -> directory
		if ($srcIsDirectory && $dstIsDirectory) {
			// replace target directory
			if (!($flags & File::OPERATION_REJECT) && $flags & File::OPERATION_REPLACE) {
				if ($dstExists) {
					$this->delete($dstPathname, true, false);
				}

				$flags |= File::OPERATION_RECURSIVE;
				// continue recursive move
			}

			// recursive merge directories
			if ($flags & File::OPERATION_RECURSIVE) {
				if ($srcPathname->localAdapter() instanceof LocalAdapter) {
					$self = $this;
					return $this->execute(
						function() use ($srcPathname, $dstPathname, $self) {
							return rename(
								$srcPathname->localAdapter()->basepath . $srcPathname->local(),
								$this->basepath . $dstPathname->local()
							);
						},
						0,
						'Could not move %s to %s.',
						$srcPathname,
						$dstPathname
					);
				}
				else {
					$iterator = $srcPathname->localAdapter()->getIterator($srcPathname, array());

					/** @var Pathname $srcChildPathname */
					foreach ($iterator as $srcChildPathname) {
						$srcPathname->localAdapter()->getRootAdapter()->moveTo(
							$srcChildPathname,
							$this,
							$dstPathname->child($srcChildPathname),
							$flags
						);
					}
				}
			}

			else {
				throw new DirectoryOverwriteDirectoryException(
					$srcPathname,
					$dstPathname
				);
			}
		}

		// move file -> file
		else if (!$srcIsDirectory && !$dstIsDirectory) {
			if (!($flags & File::OPERATION_REJECT) && $flags & File::OPERATION_REPLACE) {
				// native move
				if ($srcPathname->localAdapter() instanceof LocalAdapter) {
					$self = $this;
					return $this->execute(
						function() use ($srcPathname, $dstPathname, $self) {
							return rename(
								$srcPathname->localAdapter()->basepath . $srcPathname->local(),
								$this->basepath . $dstPathname->local()
							);
						},
						0,
						'Could not move %s to %s.',
						$srcPathname,
						$dstPathname
					);
				}

				// stream move
				else {
					$self = $this;
					return $this->execute(
						function() use ($srcPathname, $dstPathname, $self) {
							$srcStream = $srcPathname->localAdapter()->open($srcPathname, 'rb');
							$dstStream = $this->open($dstPathname, 'wb');

							$result = stream_copy_to_stream(
								$srcStream,
								$dstStream
							);

							$srcPathname->localAdapter()->delete($srcPathname, false, false);

							fclose($srcStream);
							fclose($dstStream);

							return $result;
						},
						0,
						'Could not move %s to %s.',
						$srcPathname,
						$dstPathname
					);
				}
			}
		}

		// illegal state
		else {
			throw new FilesystemException('Illegal state!');
		}
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
					return mkdir($this->basepath . $pathname->local(), 0777, true);
				}
				else {
					$parentAdapter = $parentPathname = null;
					$this->getParent($pathname, $parentAdapter, $parentPathname);

					$parentAdapter->requireExists($parentPathname);

					return mkdir($this->basepath . $pathname->local());
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
		$parentAdapter = $parentPathname = null;
		$this->getParent($pathname, $parentAdapter, $parentPathname);

		if ($parents) {
			try {
				$parentAdapter->createDirectory($parentPathname, true);
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
				$parentAdapter->checkDirectory($parentPathname);
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
					$this->basepath . $pathname->local()
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
					$this->basepath . $pathname->local()
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
					$this->basepath . $pathname->local(),
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
				if (false !== ($f = fopen($this->basepath . $pathname->local(), 'ab'))) {
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
				if (false !== ($f = fopen($this->basepath . $pathname->local(), 'ab'))) {
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
	 * @see Filicious\Internals\Adapter::open()
	 */
	public function open(Pathname $pathname, $mode)
	{
		$this->checkFile($pathname);

		return new BuildInStream('file://' . $this->basepath . $pathname->local(), $mode);
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
					$this->basepath . $pathname->local(),
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
					$this->basepath . $pathname->local(),
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
					$this->basepath . $pathname->local(),
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
					$this->basepath . $pathname->local(),
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
					$this->basepath . $pathname->local(),
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
				return scandir(
					$this->basepath . $pathname->local()
				);
			},
			0,
			'Could not list contents of %s.',
			$pathname
		);

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
			$parentAdapter = $parentPathname = null;
			$this->getParent($pathname, $parentAdapter, $parentPathname);

			return $parentAdapter->getFreeSpace($parentPathname);
		}

		$self = $this;
		return $this->execute(
			function() use ($pathname, $self) {
				return disk_free_space(
					$this->basepath . $pathname->local()
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
			$parentAdapter = $parentPathname = null;
			$this->getParent($pathname, $parentAdapter, $parentPathname);

			return $parentAdapter->getTotalSpace($parentPathname);
		}

		$self = $this;
		return $this->execute(
			function() use ($pathname, $self) {
				return disk_total_space(
					$this->basepath . $pathname->local()
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
				sprintf(
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

		return $error;
	}
}
