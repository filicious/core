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

namespace Filicious\Internals;

use Filicious\File;
use Filicious\Filesystem;
use Filicious\FilesystemConfig;
use Filicious\Exception\FileNotFoundException;
use Filicious\Exception\NotAFileException;
use Filicious\Exception\NotADirectoryException;

/**
 * A mount aggregator can mount adapters to various paths.
 * Multiple adapters can be mounted to the same path, but only the last mounted
 * adapter can be seen.
 *
 * @package filicious-core
 * @author  Oliver Hoff <oliver@hofff.com>
 */
abstract class AbstractAdapter
	implements Adapter
{
	/**
	 * @var FilesystemConfig
	 */
	protected $config;

	protected $fs;

	protected $root;

	protected $parent;

	/**
	 * @see Filicious\Internals\Adapter::getConfig()
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * @see Filicious\Internals\Adapter::setFilesystem()
	 */
	public function setFilesystem(Filesystem $fs)
	{
		$this->fs = $fs;
		return $this;
	}

	/**
	 * @see Filicious\Internals\Adapter::getFilesystem()
	 */
	public function getFilesystem()
	{
		return $this->fs;
	}

	/**
	 * @see Filicious\Internals\Adapter::setRootAdapter()
	 */
	public function setRootAdapter(Adapter $root)
	{
		$this->root = $root;
		return $this;
	}

	/**
	 * @see Filicious\Internals\Adapter::getRootAdapter()
	 */
	public function getRootAdapter()
	{
		return $this->root;
	}

	/**
	 * @see Filicious\Internals\Adapter::setParentAdapter()
	 */
	public function setParentAdapter(Adapter $parent)
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 * @see Filicious\Internals\Adapter::getParentAdapter()
	 */
	public function getParentAdapter()
	{
		return $this->parent;
	}

	/**
	 * @see Filicious\Internals\Adapter::resolveLocal()
	 */
	public function resolveLocal(Pathname $pathname, &$localAdapter, &$local)
	{
		$localAdapter = $this;
		$local = $pathname->full();
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
		$dstParentPathname = $dstPathname->parent();

		if ($flags & File::OPERATION_PARENTS) {
			$dstParentPathname->localAdapter()->createDirectory(
				$dstParentPathname,
				true
			);
		}
		else {
			$dstParentPathname->localAdapter()->checkDirectory($dstParentPathname);
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
				// native copy if supported
				$dstClass = new \ReflectionClass($this);
				$srcClass = new \ReflectionClass($srcPathname->localAdapter());

				if ($dstClass->getName() == $srcClass->getName() ||
					$dstClass->isSubclassOf($srcClass) ||
					$srcClass->isSubclassOf($dstClass)
				) {
					if ($this->nativeCopy($srcPathname, $dstPathname)) {
						return;
					}
				}

				// stream copy
				return $this->execute(
					function() use ($srcPathname, $dstPathname) {
						$srcStream = $srcPathname->localAdapter()->getStream($srcPathname);
						$srcStream->open(new StreamMode('rb'));

						$dstStream = $this->getStream($dstPathname);
						$dstStream->open(new StreamMode('wb'));

						$result = stream_copy_to_stream(
							$srcStream->getRessource(),
							$dstStream->getRessource()
						);

						$srcStream->close();
						$dstStream->close();

						return $result;
					},
					0,
					'Could not copy %s to %s.',
					$srcPathname,
					$dstPathname
				);
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

	public function nativeCopy(
		Pathname $srcPathname,
		Pathname $dstPathname
	) {
		return false;
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
		$dstParentPathname = $dstPathname->parent();

		if ($flags & File::OPERATION_PARENTS) {
			$dstParentPathname->localAdapter()->createDirectory(
				$dstParentPathname,
				true
			);
		}
		else {
			$dstParentPathname->localAdapter()->checkDirectory($dstParentPathname);
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
			if (!$dstExists || !($flags & File::OPERATION_REJECT) && $flags & File::OPERATION_REPLACE) {
				if ($dstExists) {
					$this->delete($dstPathname, true, false);
				}

				$flags |= File::OPERATION_RECURSIVE;
				// continue recursive move
			}

			// recursive merge directories
			if ($flags & File::OPERATION_RECURSIVE) {
				// native move if supported
				$dstClass = new \ReflectionClass($this);
				$srcClass = new \ReflectionClass($srcPathname->localAdapter());

				if ($dstClass->getName() == $srcClass->getName() ||
					$dstClass->isSubclassOf($srcClass) ||
					$srcClass->isSubclassOf($dstClass)
				) {
					if ($this->nativeMove($srcPathname, $dstPathname)) {
						return;
					}
				}

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

			else {
				throw new DirectoryOverwriteDirectoryException(
					$srcPathname,
					$dstPathname
				);
			}
		}

		// move file -> file
		else if (!$srcIsDirectory && !$dstIsDirectory) {
			if (!$dstExists || !($flags & File::OPERATION_REJECT) && $flags & File::OPERATION_REPLACE) {
				// native move if supported
				$dstClass = new \ReflectionClass($this);
				$srcClass = new \ReflectionClass($srcPathname->localAdapter());

				if ($dstClass->getName() == $srcClass->getName() ||
					$dstClass->isSubclassOf($srcClass) ||
					$srcClass->isSubclassOf($dstClass)
				) {
					if ($this->nativeMove($srcPathname, $dstPathname)) {
						return;
					}
				}

				// stream move
				return $this->execute(
					function() use ($srcPathname, $dstPathname) {
						$srcStream = $srcPathname->localAdapter()->getStream($srcPathname);
						$srcStream->open(new StreamMode('rb'));

						$dstStream = $this->getStream($dstPathname);
						$dstStream->open(new StreamMode('wb'));

						$result = stream_copy_to_stream(
							$srcStream->getRessource(),
							$dstStream->getRessource()
						);

						$srcStream->close();
						$dstStream->close();

						return $result;
					},
					0,
					'Could not move %s to %s.',
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

	public function nativeMove(
		Pathname $srcPathname,
		Pathname $dstPathname
	) {
		return false;
	}

	/**
	 * @see Filicious\Internals\Adapter::getMD5()
	 */
	public function getMD5(Pathname $pathname, $binary)
	{
		return md5($this->getContents($pathname), $binary);
	}

	/**
	 * @see Filicious\Internals\Adapter::getSHA1()
	 */
	public function getSHA1(Pathname $pathname, $binary)
	{
		return sha1($this->getContents($pathname), $binary);
	}

	/**
	 * @see Filicious\Internals\Adapter::count()
	 */
	public function count(Pathname $pathname, array $filter)
	{
		$i = 0;
		foreach ($this->getIterator($pathname, $filter) as $pathname) {
			$i++;
		}
		return $i;
	}

	/**
	 * @see Filicious\Internals\Adapter::getIterator()
	 */
	public function getIterator(Pathname $pathname, array $filter)
	{
		if (Util::hasBit($filter, File::LIST_RECURSIVE)) {
			return new \RecursiveIteratorIterator(
				new RecursivePathnameIterator($pathname, $filter)
			);
		}
		else {
			return new PathnameIterator($pathname, $filter);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::requireExists()
	 */
	public function requireExists(Pathname $pathname)
	{
		if (!$this->exists($pathname)) {
			throw new FileNotFoundException($pathname);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::checkFile()
	 */
	public function checkFile(Pathname $pathname)
	{
		$this->requireExists($pathname);
		if (!$this->isFile($pathname)) {
			throw new NotAFileException($pathname);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::checkDirectory()
	 */
	public function checkDirectory(Pathname $pathname)
	{
		$this->requireExists($pathname);
		if (!$this->isDirectory($pathname)) {
			throw new NotADirectoryException($pathname);
		}
	}
}
