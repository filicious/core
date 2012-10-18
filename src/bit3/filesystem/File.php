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

use SplFileInfo;
use Traversable;
use IteratorAggregate;
use ArrayIterator;

/**
 * A file object
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
abstract class File
    extends SplFileInfo
    implements IteratorAggregate
{
    /**
     * Get the underlaying filesystem for this file.
     *
     * @return Filesystem
     */
    public abstract function getFilesystem();

    /**
     * Change file group.
     *
     * @param mixed $group
     *
     * @return bool
     */
    public abstract function chgrp($group);

    /**
     * Change file mode.
     *
     * @param int  $mode
     *
     * @return bool
     */
    public abstract function chmod($mode);

    /**
     * Change file owner.
     *
     * @param string|int $user
     *
     * @return bool
     */
    public abstract function chown($user);

    /**
     * Copies file
     *
     * @param File $destination
     *
     * @return bool
     */
    public abstract function copy(File $destination);

    /**
     * Delete a file or directory.
     *
     * @return bool
     */
    public abstract function delete();

    /**
     * Checks whether a file or directory exists.
     *
     * @return bool
     */
    public abstract function exists();

    /**
     * Portable advisory shared file locking. (reader)
     *
     * @param bool $noblocking
     *
     * @return bool
     */
    public abstract function lockShared($noblocking = false);

    /**
     * Portable advisory exclusive file locking. (writer)
     *
     * @param bool $noblocking
     *
     * @return bool
     */
    public abstract function lockExclusive($noblocking = false);

    /**
     * Unlock a file.
     *
     * @param File $path
     *
     * @return bool
     */
    public abstract function unlock();

    /**
     * Makes directory
     *
     * @return bool
     */
    public abstract function mkdir();

    /**
     * Makes directories
     *
     * @return bool
     */
    public abstract function mkdirs();

    /**
     * Renames a file or directory
     *
     * @param File $destination
     *
     * @return bool
     */
    public abstract function rename(File $destination);

    /**
     * Sets access and modification time of file
     *
     * @param int $time  = time()
     * @param int $atime = time()
     *
     * @return bool
     */
    public abstract function touch($time = null, $atime = null);

    /**
     * Alias for File->delete()
     *
     * @return bool
     */
    public function rmdir()
    {
        return $this->delete();
    }

    /**
     * Alias for File->delete()
     *
     * @return bool
     */
    public function unlink()
    {
        return $this->delete();
    }

    /**
     * Gets an stream for the file.
     *
     * @param string $mode
     *
     * @return mixed
     */
    public abstract function openStream($mode = 'r');

    /**
     * Find pathnames matching a pattern.
     *
     * @param string $pattern
     * @param int    $flags Use GLOB_* flags. Not all may supported on each filesystem.
     *
     * @return array<File>
     */
    public abstract function glob($pattern);

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
                return $path->isDir();
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
                return $path->isDir();
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
