<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem\Iterator;

use Bit3\Filesystem\File;
use Bit3\Filesystem\FilesystemException;
use Traversable;
use Iterator;
use SeekableIterator;

/**
 * Filesystem iterator
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class FilesystemIterator
    implements Iterator, SeekableIterator
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
     * @var File
     */
    protected $path;

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

    /**
     * @var int
     */
    protected $flags;

    public function __construct(File $path, $flags = 0)
    {
        if (!$path->isDirectory()) {
            throw new FilesystemException('Path ' . $path->getPathname() . ' is not a directory.');
        }
        $this->path  = $path;
        $this->files = $path->ls();
        $this->keys  = array_keys($this->files);
        $this->index = -1;
        $this->flags = $flags;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
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
