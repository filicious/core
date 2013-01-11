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
use Filicious\Exception\FilesystemOperationException;
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

	public function __construct(Filesystem $fs, Adapter $root, Adapter $parent)
	{
		parent::__construct($fs, $root, $parent);
	}

	/**
	 * @see Filicious\Internals\Adapter::getParent()
	 */
	public function getParent(Pathname $pathname, &$parentAdapter, &$parentPathname)
	{
		// local path is more than the root
		// -> the parent is inside of this adapter
		if ($pathname->local() != '/') {
			$parentAdapter  = $this;
			$parentPathname = new Pathname(
				dirname($pathname->full()),
				dirname($pathname->local())
			);
		}
		else {
			$this->root->getParent($pathname, $parentAdapter, $parentPathname);
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

		try {
			$atime = fileatime($this->basepath . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($atime === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get access time of %s.', $pathname)
			);
		}

		return $atime;
	}

	/**
	 * @see Filicious\Internals\Adapter::setAccessTime()
	 */
	public function setAccessTime(Pathname $pathname, \DateTime $time)
	{
		$this->requireExists($pathname);

		try {
			$result = touch(
				$this->basepath . $pathname->local(),
				$this->getModifyTime($pathname),
				$time->getTimestamp()
			);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not set access time for %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::getCreationTime()
	 */
	public function getCreationTime(Pathname $pathname)
	{
		$this->requireExists($pathname);

		try {
			$ctime = filectime($this->basepath . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($ctime === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get creation time of %s.', $pathname)
			);
		}

		return $ctime;
	}

	/**
	 * @see Filicious\Internals\Adapter::getCreationTime()
	 */
	public function getModifyTime(Pathname $pathname)
	{
		$this->requireExists($pathname);

		try {
			$mtime = filemtime($this->basepath . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($mtime === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get creation time of %s.', $pathname)
			);
		}

		return $mtime;
	}

	/**
	 * @see Filicious\Internals\Adapter::setModifyTime()
	 */
	public function setModifyTime(Pathname $pathname, \DateTime $time)
	{
		$this->requireExists($pathname);

		try {
			$result = touch(
				$this->basepath . $pathname->local(),
				$time->getTimestamp(),
				$this->getAccessTime($pathname)
			);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not set modify time for %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::touch()
	 */
	public function touch(Pathname $pathname, \DateTime $time, \DateTime $atime, $create)
	{
		if (!$create) {
			$this->requireExists($pathname);
		}

		try {
			$result = touch(
				$this->basepath . $pathname->local(),
				$time->getTimestamp(),
				$atime->getTimestamp()
			);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not touch %s.', $pathname)
			);
		}
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
			try {
				$result = filesize($this->basepath . $pathname->local());
			}
			catch (\ErrorException $e) {
				throw new FilesystemOperationException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			if ($result === false) {
				throw new FilesystemOperationException(
					sprintf('Could not get size of %s.', $pathname)
				);
			}

			return $result;
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::getSize()
	 */
	public function getOwner(Pathname $pathname)
	{
		$this->requireExists($pathname);

		try {
			$owner = fileowner($this->basepath . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($owner === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get owner of %s.', $pathname)
			);
		}

		return $owner;
	}

	/**
	 * @see Filicious\Internals\Adapter::setOwner()
	 */
	public function setOwner(Pathname $pathname, $user)
	{
		$this->requireExists($pathname);

		try {
			$result = chown(
				$this->basepath . $pathname->local(),
				$user
			);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not set owner for %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::getGroup()
	 */
	public function getGroup(Pathname $pathname)
	{
		$this->requireExists($pathname);

		try {
			$owner = filegroup($this->basepath . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($owner === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get group of %s.', $pathname)
			);
		}

		return $owner;
	}

	/**
	 * @see Filicious\Internals\Adapter::setGroup()
	 */
	public function setGroup(Pathname $pathname, $group)
	{
		$this->requireExists($pathname);

		try {
			$result = chgrp(
				$this->basepath . $pathname->local(),
				$group
			);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not set owner for %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::getMode()
	 */
	public function getMode(Pathname $pathname)
	{
		$this->requireExists($pathname);

		try {
			$owner = fileperms($this->basepath . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($owner === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get mode of %s.', $pathname)
			);
		}

		return $owner;
	}

	/**
	 * @see Filicious\Internals\Adapter::setMode()
	 */
	public function setMode(Pathname $pathname, $mode)
	{
		$this->requireExists($pathname);

		try {
			$result = chmod(
				$this->basepath . $pathname->local(),
				$mode
			);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not set owner for %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::isReadable()
	 */
	public function isReadable(Pathname $pathname)
	{
		$this->requireExists($pathname);

		try {
			$readable = is_readable($this->basepath . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($readable === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get readable state of %s.', $pathname)
			);
		}

		return $readable;
	}

	/**
	 * @see Filicious\Internals\Adapter::isWritable()
	 */
	public function isWritable(Pathname $pathname)
	{
		$this->requireExists($pathname);

		try {
			$writeable = is_writable($this->basepath . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($writeable === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get writeable state of %s.', $pathname)
			);
		}

		return $writeable;
	}

	/**
	 * @see Filicious\Internals\Adapter::isExecutable()
	 */
	public function isExecutable(Pathname $pathname)
	{
		$this->requireExists($pathname);

		try {
			$executeable = is_executable($this->basepath . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($executeable === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get executeable state of %s.', $pathname)
			);
		}

		return $executeable;
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
			throw new FilesystemOperationException(
				$e->getMessage(),
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

			try {
				$result = rmdir($this->basepath . $pathname->local());
			}
			catch (\ErrorException $e) {
				throw new FilesystemOperationException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			if ($result === false) {
				throw new FilesystemOperationException(
					sprintf('Could not delete directory %s.', $pathname)
				);
			}
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

			try {
				$result = unlink($this->basepath . $pathname->local());
			}
			catch (\ErrorException $e) {
				throw new FilesystemOperationException(
					$e->getMessage(),
					$e->getCode(),
					$e
				);
			}

			if ($result === false) {
				throw new FilesystemOperationException(
					sprintf('Could not delete file %s.', $pathname)
				);
			}
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
					try {
						$result = copy(
							$srcPathname->localAdapter()->basepath . $srcPathname->local(),
							$this->basepath . $dstPathname->local()
						);
					}
					catch (\ErrorException $e) {
						throw new FilesystemOperationException(
							$e->getMessage(),
							$e->getCode(),
							$e
						);
					}

					if ($result === false) {
						throw new FilesystemOperationException(
							sprintf('Could not copy %s to %s.', $srcPathname, $dstPathname)
						);
					}
				}

				// stream copy
				else {
					try {
						$srcStream = $srcPathname->localAdapter()->open($srcPathname, 'rb');
						$dstStream = $this->open($dstPathname, 'wb');

						$result = stream_copy_to_stream(
							$srcStream,
							$dstStream
						);

						fclose($srcStream);
						fclose($dstStream);
					}
					catch (\ErrorException $e) {
						if (is_resource($srcStream)) {
							fclose($srcStream);
						}
						if (is_resource(($dstStream))) {
							fclose($dstStream);
						}

						throw new FilesystemOperationException(
							$e->getMessage(),
							$e->getCode(),
							$e
						);
					}

					if ($result === false) {
						throw new FilesystemOperationException(
							sprintf('Could not copy %s to %s.', $srcPathname, $dstPathname)
						);
					}
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
					try {
						$result = rename(
							$srcPathname->localAdapter()->basepath . $srcPathname->local(),
							$this->basepath . $dstPathname->local()
						);
					}
					catch (\ErrorException $e) {
						throw new FilesystemOperationException(
							$e->getMessage(),
							$e->getCode(),
							$e
						);
					}

					if ($result === false) {
						throw new FilesystemOperationException(
							sprintf('Could not move %s to %s.', $srcPathname, $dstPathname)
						);
					}
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
					try {
						$result = rename(
							$srcPathname->localAdapter()->basepath . $srcPathname->local(),
							$this->basepath . $dstPathname->local()
						);
					}
					catch (\ErrorException $e) {
						throw new FilesystemOperationException(
							$e->getMessage(),
							$e->getCode(),
							$e
						);
					}

					if ($result === false) {
						throw new FilesystemOperationException(
							sprintf('Could not move %s to %s.', $srcPathname, $dstPathname)
						);
					}
				}

				// stream move
				else {
					try {
						$srcStream = $srcPathname->localAdapter()->open($srcPathname, 'rb');
						$dstStream = $this->open($dstPathname, 'wb');

						$result = stream_copy_to_stream(
							$srcStream,
							$dstStream
						);

						$srcPathname->localAdapter()->delete($srcPathname, false, false);

						fclose($srcStream);
						fclose($dstStream);
					}
					catch (\ErrorException $e) {
						if (is_resource($srcStream)) {
							fclose($srcStream);
						}
						if (is_resource(($dstStream))) {
							fclose($dstStream);
						}

						throw new FilesystemOperationException(
							$e->getMessage(),
							$e->getCode(),
							$e
						);
					}

					if ($result === false) {
						throw new FilesystemOperationException(
							sprintf('Could not move %s to %s.', $srcPathname, $dstPathname)
						);
					}
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

		try {
			// create with parents
			if ($parents) {
				// TODO: apply umask.
				$result = mkdir($this->basepath . $pathname->local(), 0777, true);
			}
			else {
				$parentAdapter = $parentPathname = null;
				$this->getParent($pathname, $parentAdapter, $parentPathname);

				$parentAdapter->requireExists($parentPathname);

				$result = mkdir($this->basepath . $pathname->local());
			}
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not create directory %s.', $pathname)
			);
		}
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

		try {
			$result = touch($this->basepath . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not create file %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::getContents()
	 */
	public function getContents(Pathname $pathname)
	{
		$this->checkFile($pathname);

		try {
			$result = file_get_contents($this->basepath . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get contents of %s.', $pathname)
			);
		}

		return $result;
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

		try {
			$result = file_put_contents($this->basepath . $pathname->local(), $content);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not set contents to %s.', $pathname)
			);
		}
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

		$f = false;
		try {
			$result = false;

			if (false !== ($f = fopen($this->basepath . $pathname->local(), 'ab'))) {
				$result = fwrite($f, $content);
				fclose($f);
			}
		}
		catch (\ErrorException $e) {
			if (is_resource($f)) {
				fclose($f);
			}
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not append contents to %s.', $pathname)
			);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::truncate()
	 */
	public function truncate(Pathname $pathname, $size)
	{
		$this->checkFile($pathname);

		$f = false;
		try {
			$result = false;

			if (false !== ($f = fopen($this->basepath . $pathname->local(), 'ab'))) {
				$result = ftruncate($f, $size);
				fclose($f);
			}
		}
		catch (\ErrorException $e) {
			if (is_resource($f)) {
				fclose($f);
			}
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($result === false) {
			throw new FilesystemOperationException(
				sprintf('Could not truncate file %s to %s.', $pathname, $size)
			);
		}
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

		try {
			$finfo = finfo_file(FS::getFileInfo(), $this->basepath . $pathname->local(), FILEINFO_NONE);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($finfo === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get mime name of %s.', $pathname)
			);
		}

		return $finfo;
	}

	/**
	 * @see Filicious\Internals\Adapter::getMIMEType()
	 */
	public function getMIMEType(Pathname $pathname)
	{
		$this->checkFile($pathname);

		try {
			$finfo = finfo_file(FS::getFileInfo(), $this->basepath . $pathname->local(), FILEINFO_MIME_TYPE);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($finfo === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get mime name of %s.', $pathname)
			);
		}

		return $finfo;
	}

	/**
	 * @see Filicious\Internals\Adapter::getMIMEEncoding()
	 */
	public function getMIMEEncoding(Pathname $pathname)
	{
		$this->checkFile($pathname);

		try {
			$finfo = finfo_file(FS::getFileInfo(), $this->basepath . $pathname->local(), FILEINFO_MIME_ENCODING);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($finfo === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get mime name of %s.', $pathname)
			);
		}

		return $finfo;
	}

	/**
	 * @see Filicious\Internals\Adapter::getMD5()
	 */
	public function getMD5(Pathname $pathname, $binary)
	{
		$this->checkFile($pathname);

		try {
			$md5 = md5_file($this->basepath . $pathname->local(), $binary);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($md5 === false) {
			throw new FilesystemOperationException(
				sprintf('Could not calculate md5 sum of %s.', $pathname)
			);
		}

		return $md5;
	}

	/**
	 * @see Filicious\Internals\Adapter::getSHA1()
	 */
	public function getSHA1(Pathname $pathname, $binary)
	{
		$this->checkFile($pathname);

		try {
			$md5 = sha1_file($this->basepath . $pathname->local(), $binary);
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($md5 === false) {
			throw new FilesystemOperationException(
				sprintf('Could not calculate md5 sum of %s.', $pathname)
			);
		}

		return $md5;
	}

	/**
	 * @see Filicious\Internals\Adapter::ls()
	 */
	public function ls(Pathname $pathname)
	{
		$this->checkDirectory($pathname);

		try {
			$files = scandir($this->basepath . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($files === false) {
			throw new FilesystemOperationException(
				sprintf('Could not list contents of %s.', $pathname)
			);
		}

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

		try {
			$diskFreeSpace = disk_free_space($this->basepath . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($diskFreeSpace === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get free space for %s.', $pathname)
			);
		}

		return $diskFreeSpace;
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

		try {
			$diskTotalSpace = disk_total_space($this->basepath . $pathname->local());
		}
		catch (\ErrorException $e) {
			throw new FilesystemOperationException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ($diskTotalSpace === false) {
			throw new FilesystemOperationException(
				sprintf('Could not get total space for %s.', $pathname)
			);
		}

		return $diskTotalSpace;
	}
}
