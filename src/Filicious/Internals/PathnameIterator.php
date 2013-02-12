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
use Filicious\Internals\Adapter;
use Filicious\Internals\Pathname;
use Filicious\Exception\FilesystemException;
use Iterator;
use SeekableIterator;

/**
 * Filesystem iterator
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class PathnameIterator
	implements Iterator, SeekableIterator
{
	/**
	 * @var Pathname
	 */
	protected $pathname;

	/**
	 * @var array
	 */
	protected $filters;

	/**
	 * @var int
	 */
	protected $bitmask;

	/**
	 * @var array
	 */
	protected $globs;

	/**
	 * @var array
	 */
	protected $callables;

	/**
	 * @var array
	 */
	protected $globSearchPatterns;

	/**
	 * @var array
	 */
	protected $files;

	/**
	 * @var array
	 */
	protected $keys;

	/**
	 * @var int
	 */
	protected $index;

	public function __construct(
		Pathname $pathname,
		$filters
	) {
		if (!$pathname
			->localAdapter()
			->isDirectory($pathname)
		) {
			throw new FilesystemException('Path ' . $pathname->full() . ' is not a directory.');
		}

		$this->pathname = $pathname;
		$this->filters  = $filters;
		$this->index    = -1;
	}

	protected function getKeys()
	{
		if ($this->keys === null) {
			$this->getFiles();
		}

		return $this->keys;
	}

	protected function getFiles()
	{
		if ($this->files === null) {
			$this->prepareFilters();

			$this->files = array();

			$fs = $this->pathname
				->rootAdapter()
				->getFilesystem();

			$fileNames = $this->pathname
				->localAdapter()
				->ls($this->pathname);
			foreach ($fileNames as $fileName) {
				$childPathname = $this->pathname->child($fileName);
				$file          = $fs->getFile($childPathname);

				if (
					$this->applyBitmaskFilters($file) &&
					$this->applyGlobFilters($file) &&
					$this->applyCallableFilters($file)
				) {
					$this->files[] = $file;
				}
			}

			$this->keys = array_keys($this->files);
		}

		return $this->files;
	}

	protected function prepareFilters(PathnameIterator $iterator = null)
	{
		if (
			$this->bitmask === null ||
			$this->globs === null ||
			$this->callables === null ||
			$this->globSearchPatterns === null
		) {
			if ($iterator !== null) {
				$this->bitmask            = $iterator->bitmask;
				$this->globs              = $iterator->globs;
				$this->callables          = $iterator->callables;
				$this->globSearchPatterns = $iterator->globSearchPatterns;
			}
			else {
				$this->bitmask            = null;
				$this->globs              = array();
				$this->callables          = array();
				$this->globSearchPatterns = array();

				$this->evaluateFilters($this->filters);

				// fallback bitmask, list all
				if ($this->bitmask === null) {
					$this->bitmask = File::LIST_ALL;
				}

				// if only decided between hidden and not hidden, list everythink else
				else if (
					$this->bitmask === File::LIST_HIDDEN ||
					$this->bitmask === File::LIST_VISIBLE ||
					$this->bitmask === (File::LIST_HIDDEN | File::LIST_VISIBLE)
				) {
					$this->bitmask |= File::LIST_FILES;
					$this->bitmask |= File::LIST_DIRECTORIES;
					$this->bitmask |= File::LIST_LINKS;
					$this->bitmask |= File::LIST_OPAQUE;
				}

				// if only decided between files/directories, also list links
				else if (
					$this->bitmask === File::LIST_FILES ||
					$this->bitmask === File::LIST_DIRECTORIES ||
					$this->bitmask === (File::LIST_FILES | File::LIST_DIRECTORIES)
				) {
					$this->bitmask |= File::LIST_HIDDEN;
					$this->bitmask |= File::LIST_VISIBLE;
					$this->bitmask |= File::LIST_LINKS;
					$this->bitmask |= File::LIST_OPAQUE;
				}

				// if only decided between links/non-links, list hidden and visible
				else if (
					$this->bitmask === File::LIST_LINKS ||
					$this->bitmask === File::LIST_OPAQUE ||
					$this->bitmask === (File::LIST_LINKS | File::LIST_OPAQUE)
				) {
					$this->bitmask |= File::LIST_HIDDEN;
					$this->bitmask |= File::LIST_VISIBLE;
				}

				// if only recursive, list everything else
				else if (
					$this->bitmask === File::LIST_RECURSIVE
				) {
					$this->bitmask |= File::LIST_ALL;
				}

				// prepare globs
				foreach ($this->globs as $index => $glob) {
					$parts = explode('/', $glob);

					if (count($parts) > 1) {
						$max  = count($parts) - 2;
						$path = '';
						for ($i = 0; $i < $max; $i++) {
							$path .= ($path ? '/' : '') . $parts[$i];

							$globSearchPatterns[] = Util::normalizePath('*/' . $this->pathname->full() . '/' . $path);
						}
					}

					$globs[$index] = Util::normalizePath('*/' . $this->pathname->full() . '/' . $glob);
				}
			}
		}
	}

	protected function evaluateFilters($filters)
	{
		if (\Filicious\is_traversable($filters)) {
			// search for File::LIST_RECURSIVE
			foreach ($filters as $arg) {
				if (is_int($arg)) {
					if ($this->bitmask === null) {
						$this->bitmask = $arg;
					}
					else {
						$this->bitmask |= $arg;
					}
				}
				else if (is_string($arg)) {
					$this->globs[] = Util::normalizePath($arg);
				}
				else if (is_callable($arg)) {
					$this->callables[] = $arg;
				}
				else if (is_array($arg)) {
					$this->evaluateFilters($arg);
				}
				else {
					if (is_object($arg)) {
						$type = get_class($arg);
					}
					else {
						ob_start();
						var_dump($arg);
						$type = ob_get_contents();
						ob_end_clean();
					}

					throw new Exception(
						sprintf(
							'Can not use %s as listing filter.',
							$type
						)
					);
				}
			}
		}
	}

	/**
	 * Apply bitmask filters on current file.
	 *
	 * @param \Filicious\File $file
	 *
	 * @return bool
	 */
	protected function applyBitmaskFilters(File $file)
	{
		$basename = $file->getBasename();

		if (!($this->bitmask & File::LIST_HIDDEN) &&
				$basename[0] == '.' ||
			!($this->bitmask & File::LIST_VISIBLE) &&
				$basename[0] != '.' ||
			!($this->bitmask & File::LIST_FILES) &&
				$file->isFile() ||
			!($this->bitmask & File::LIST_DIRECTORIES) &&
				$file->isDirectory() ||
			!($this->bitmask & File::LIST_LINKS) &&
				$file->isLink() ||
			!($this->bitmask & File::LIST_OPAQUE) &&
				!$file->isLink()
		) {
			return false;
		}

		return true;
	}

	/**
	 * Apply glob filters on current file.
	 *
	 * @param \Filicious\File $file
	 *
	 * @return bool
	 */
	protected function applyGlobFilters(File $file)
	{
		foreach ($this->globs as $glob) {
			if (!fnmatch($glob, $file->getPathname())) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Apply callable filters on current file.
	 *
	 * @param \Filicious\File $file
	 *
	 * @return bool
	 */
	protected function applyCallableFilters(File $file)
	{
		foreach ($this->callables as $callable) {
			if (!$callable($file->getPathname(), $file)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Apply glob filters on current file.
	 *
	 * @param \Filicious\File $file
	 *
	 * @return bool
	 */
	protected function applyGlobSearchPattern(File $file = null)
	{
		if (count($this->globSearchPatterns)) {
			if ($file === null) {
				$file = $this->currentFile();
			}

			foreach ($this->globSearchPatterns as $glob) {
				if (fnmatch($glob, $file->getPathname())) {
					return true;
				}
			}
			return false;
		}

		return true;
	}

	/**
	 * @return File
	 */
	protected function currentFile()
	{
		$files = $this->getFiles();
		$keys  = $this->getKeys();
		return $files[$keys[$this->index]];
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return File Can return any type.
	 */
	public function current()
	{
		return $this->currentFile();
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Move forward to next element
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next()
	{
		$keys = $this->getKeys();
		if ($this->index < count($keys)) {
			$this->index++;
		}
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return int scalar on success, or null on failure.
	 */
	public function key()
	{
		return $this->index;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 *       Returns true on success or false on failure.
	 */
	public function valid()
	{
		$keys = $this->getKeys();
		return $this->index >= 0 && $this->index < count($keys);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind()
	{
		$this->index = 0;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Seeks to a position
	 *
	 * @link http://php.net/manual/en/seekableiterator.seek.php
	 *
	 * @param int $position <p>
	 *                      The position to seek to.
	 * </p>
	 *
	 * @return void
	 */
	public function seek($position)
	{
		$this->index = (int) $position;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return $this->getFiles();
	}
}
