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

namespace Filicious\Iterator;

use Filicious\File;
use Filicious\FilesystemException;
use Filicious\Internals\PathnameIterator;
use Filicious\Internals\Util;

/**
 * Filesystem iterator
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class FilesystemIterator
	extends PathnameIterator
{
	/**
	 * Makes FilesystemIterator::current() return the pathname.
	 */
	const CURRENT_AS_PATHNAME = 32;

	/**
	 * Makes FilesystemIterator::current() return the filename.
	 */
	const CURRENT_AS_BASENAME = 64;

	/**
	 * Makes FilesystemIterator::current() return an File instance.
	 */
	const CURRENT_AS_FILE = 0;

	/**
	 * Makes FilesystemIterator::current() return $this (the FilesystemIterator).
	 */
	const CURRENT_AS_SELF = 16;

	/**
	 * Makes FilesystemIterator::key() return the pathname.
	 */
	const KEY_AS_PATHNAME = 0;

	/**
	 * Makes FilesystemIterator::key() return the filename.
	 */
	const KEY_AS_FILENAME = 256;

	/**
	 * @var int
	 */
	protected $flags = 0;

	/**
	 * @param \Filicious\File $path
	 * @param int|string|callable   $_ List of flags, bitmask filters File::LIST_*, glob patterns or callables function($file) { return true|false; }
	 */
	public function __construct(File $path, $_ = null)
	{
		$filters = func_get_args();

		foreach ($filters as $filter) {
			if (is_int($filter)) {
				if ($filter & static::CURRENT_AS_PATHNAME) {
					$this->flags |= static::CURRENT_AS_PATHNAME;
				}
				else if ($filter & static::CURRENT_AS_BASENAME) {
					$this->flags |= static::CURRENT_AS_BASENAME;
				}
				else if ($filter & static::CURRENT_AS_FILE) {
					$this->flags |= static::CURRENT_AS_FILE;
				}
				else if ($filter & static::CURRENT_AS_SELF) {
					$this->flags |= static::CURRENT_AS_SELF;
				}
				else if ($filter & static::KEY_AS_PATHNAME) {
					$this->flags |= static::KEY_AS_PATHNAME;
				}
				else if ($filter & static::KEY_AS_FILENAME) {
					$this->flags |= static::KEY_AS_FILENAME;
				}
			}
		}

		parent::__construct($path->internalPathname(), $filters);
	}

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed
	 */
    public function current()
    {
        if ($this->valid()) {
            if ($this->flags & self::CURRENT_AS_SELF) {
                return $this;
            }
            else if ($this->flags & self::CURRENT_AS_PATHNAME) {
                return $this->currentFile()->getPathname();
            }
            else if ($this->flags & self::CURRENT_AS_BASENAME) {
                return $this->currentFile()->getBasename();
            }
            return $this->currentFile();
        }
        else {
            return null;
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return int scalar on success, or null on failure.
     */
    public function key()
    {
        if ($this->flags & self::KEY_AS_FILENAME) {
            return $this->currentFile()->getFilename();
        }
        return $this->currentFile()->getPathname();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Seeks to a position
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
