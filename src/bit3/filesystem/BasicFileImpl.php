<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace bit3\filesystem;

use Traversable;
use IteratorAggregate;
use ArrayIterator;
use bit3\filesystem\Filesystem;
use bit3\filesystem\File;

/**
 * A file object
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
abstract class BasicFileImpl implements File
{
    /**
     * @var Filesystem
     */
    protected $fs;

    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    /**
     * Get the type of this file.
     *
     * @return "file"|"directory"|"link"|"unknown"
     */
    public function getType()
    {
        if ($this->isFile()) {
            return 'file';
        }
        if ($this->isDirectory()) {
            return 'directory';
        }
        if ($this->isLink()) {
            return 'link';
        }
        return 'unknown';
    }

    /**
     * Get the name of the file or directory.
     *
     * @return string
     */
    public function getBasename($suffix = '')
    {
        return basename($this->getPathname(), $suffix);
    }

    /**
     * Get the extension of the file.
     *
     * @return mixed
     */
    public function getExtension()
    {
        $basename = $this->getBasename();
        $pos = strrpos($basename, '.');

        if ($pos !== false) {
            return substr($basename, $pos+1);
        }

        return null;
    }

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function globFiles($pattern)
    {
        return array_filter($this->glob($pattern),
            function (File $path) {
                return $path->isFile();
            });
    }

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function globDirectories($pattern)
    {
        return array_filter($this->glob($pattern),
            function (File $path) {
                return $path->isDirectory();
            });
    }

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function listAll()
    {
        return $this->glob('*');
    }

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function listFiles()
    {
        return array_filter($this->listAll(),
            function (File $path) {
                return $path->isFile();
            });
    }

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function listDirectories()
    {
        return array_filter($this->listAll(),
            function (File $path) {
                return $path->isDirectory();
            });
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->listAll());
    }
}
