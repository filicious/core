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

use Filicious\Exception\DirectoryOverwriteDirectoryException;
use Filicious\Exception\DirectoryOverwriteFileException;
use Filicious\Exception\FileOverwriteDirectoryException;
use Filicious\Exception\FileOverwriteFileException;
use Filicious\Exception\FilesystemException;
use Filicious\Exception\NotADirectoryException;
use Filicious\File;
use Filicious\Filesystem;
use Filicious\Stream\StreamMode;

/**
 * A mount aggregator can mount adapters to various paths.
 * Multiple adapters can be mounted to the same path, but only the last mounted
 * adapter can be seen.
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 */
abstract class AbstractAdapter
	implements Adapter
{

	/**
	 * @var Filesystem
	 */
	protected $filesystem;

	/**
	 * @var RootAdapter
	 */
	protected $root;

	/**
	 * @var Adapter
	 */
	protected $parent;

	/**
	 * {@inheritdoc}
	 */
	public function setFilesystem(Filesystem $filesystem)
	{
		$this->filesystem = $filesystem;
		$this->root       = $filesystem->getRootAdapter();
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilesystem()
	{
		return $this->filesystem;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRootAdapter()
	{
		return $this->root;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setParentAdapter(Adapter $parent)
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParentAdapter()
	{
		return $this->parent;
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolveLocal(Pathname $pathname, &$localAdapter, &$local)
	{
		$localAdapter = $this;
		$local        = $pathname->full();
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
		else if (!$dstParentPathname->localAdapter()->isDirectory($dstParentPathname)) {
			throw new NotADirectoryException($dstParentPathname);
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
				return $this;
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
						return $this;
					}
				}

				// stream copy
				return Util::executeFunction(
					function () use ($srcPathname, $dstPathname) {
						$srcStream = $srcPathname->localAdapter()->getStream($srcPathname);
						$srcStream->open(new StreamMode('rb'));

						$dstStream = $this->getStream($dstPathname);
						$dstStream->open(new StreamMode('wb'));

						$result = stream_copy_to_stream(
							$srcStream->getResource(),
							$dstStream->getResource()
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

		return $this;
	}

	public function nativeCopy(
		Pathname $srcPathname,
		Pathname $dstPathname
	) {
		return false;
	}

	/**
	 * {@inheritdoc}
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

		return $this;
	}

	/**
	 * {@inheritdoc}
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
		else if (!$dstParentPathname->localAdapter()->isDirectory($dstParentPathname)) {
			throw new NotADirectoryException($dstParentPathname);
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
				return $this;
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
						return $this;
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
						return $this;
					}
				}

				// stream move
				return Util::executeFunction(
					function () use ($srcPathname, $dstPathname) {
						$srcStream = $srcPathname->localAdapter()->getStream($srcPathname);
						$srcStream->open(new StreamMode('rb'));

						$dstStream = $this->getStream($dstPathname);
						$dstStream->open(new StreamMode('wb'));

						$result = stream_copy_to_stream(
							$srcStream->getResource(),
							$dstStream->getResource()
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
	 * {@inheritdoc}
	 */
	public function count(Pathname $pathname, array $filter)
	{
		return iterator_count($this->getIterator($pathname, $filter));
	}

	/**
	 * {@inheritdoc}
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
}
