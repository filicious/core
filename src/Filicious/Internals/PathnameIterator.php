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
	public function __construct(
		Filesystem $fs,
		Adapter $rootAdapter,
		Adapter $adapter,
		$pathname,
		$local,
		$filter
	) {
		// TODO rework filtering

		list($recursive, $bitmask, $globs, $callables, $globSearchPatterns) = Util::buildFilters($local, $filter);

		$files = array();

		$currentFiles = scandir($this->basepath . $pathname);

		foreach ($currentFiles as $path) {
			$file = new SimpleFile($pathname . '/' . $path, $this);

			$files[] = $file;

			if ($recursive &&
				$path != '.' &&
				$path != '..' &&
				$file->isDirectory() ||
				count($globSearchPatterns) &&
					Util::applyGlobFilters($file, $globSearchPatterns)
			) {
				$recursiveFiles = $file->ls();

				$files = array_merge(
					$files,
					$recursiveFiles
				);
			}
		}

		$files = Util::applyFilters($files, $bitmask, $globs, $callables);

		return $files;
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
		if (isset($this->files[$this->keys[$this->index]])) {
			if ($this->flags & self::CURRENT_AS_SELF) {
				return $this;
			}
			else if ($this->flags & self::CURRENT_AS_PATHNAME) {
				return $this->files[$this->keys[$this->index]]->getPathname();
			}
			else if ($this->flags & self::CURRENT_AS_BASENAME) {
				return $this->files[$this->keys[$this->index]]->getBasename();
			}
			return $this->files[$this->keys[$this->index]];
		}
		else {
			return null;
		}
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
		if ($this->index < count($this->keys)) {
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
		if ($this->flags & self::KEY_AS_FILENAME) {
			return $this->files[$this->keys[$this->index]]->getFilename();
		}
		return $this->files[$this->keys[$this->index]]->getPathname();
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
		return $this->index >= 0 && $this->index < count($this->keys);
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
		if (is_numeric($position)) {
			$this->index = (int) $position;
		}
		else {
			/** @var int $index */
			/** @var File $file */
			foreach ($this->keys as $index => $key) {
				$file = $this->files[$key];
				if ($this->flags & self::KEY_AS_FILENAME) {
					if ($file->getFilename() == $position) {
						$this->index = $index;
						return;
					}
				}
				else {
					if ($file->getPathname() == $position) {
						$this->index = $index;
						return;
					}
				}
			}
		}
	}
}
