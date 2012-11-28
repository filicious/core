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
     * Get the underlaying filesystem for this pathname.
     *
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->fs;
    }

    /**
     * Get the type of this file.
     *
     * @return "file"|"directory"|"link"|"unknown"
     */
    public function getType()
    {
        if ($this->isLink()) {
            return 'link';
        }
        if ($this->isFile()) {
            return 'file';
        }
        if ($this->isDirectory()) {
            return 'directory';
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
     * Find pathnames matching a pattern.
     *
     * @param string $pattern
     * @param int    $flags Use GLOB_* flags. Not all may supported on each filesystem.
     *
     * @return array<File>
     */
    public function glob($pattern)
    {
        $files = $this->listAll();

        if (is_array($files)) {
            return array_filter($files, function(File $path) use ($pattern) {
                return fnmatch($pattern, $path->getPathname());
            });
        }
    }

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function globFiles($pattern)
    {
        $files = $this->glob($pattern);

        if (is_array($files)) {
            return array_filter($files,
                function (File $path) {
                    return $path->isFile();
                });
        }

        return false;
    }

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function globDirectories($pattern)
    {
        $files = $this->glob($pattern);

        if (is_array($files)) {
            return array_filter($files,
                function (File $path) {
                    return $path->isDirectory();
                });
        }

        return false;
    }

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function listFiles()
    {
        $files = $this->listAll();

        if (is_array($files)) {
            return array_filter($files,
                function (File $path) {
                    return $path->isFile();
                });
        }

        return false;
    }

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function listDirectories()
    {
        $files = $this->listAll();

        if (is_array($files)) {
            return array_filter($files,
                function (File $path) {
                    return $path->isDirectory();
                });
        }

        return false;
    }

    /**
     * Get mime content type.
     *
     * @param int $type
     *
     * @return string
     */
    public function getMimeName()
    {
        $finfo = FS::getFileInfo();

        return finfo_file($finfo, $this->getRealUrl(), FILEINFO_NONE);
    }

    /**
     * Get mime content type.
     *
     * @param int $type
     *
     * @return string
     */
    public function getMimeType()
    {
        $finfo = FS::getFileInfo();

        return finfo_file($finfo, $this->getRealUrl(), FILEINFO_MIME_TYPE);
    }

    /**
     * Get mime content type.
     *
     * @param int $type
     *
     * @return string
     */
    public function getMimeEncoding()
    {
        $finfo = FS::getFileInfo();

        return finfo_file($finfo, $this->getRealUrl(), FILEINFO_MIME_ENCODING);
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

    public function __toString()
    {
        return $this->pathname;
    }
}
