<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace bit3\filesystem\local;

use bit3\filesystem\Filesystem;
use bit3\filesystem\File;
use bit3\filesystem\FilesystemException;
use bit3\filesystem\Util;

/**
 * A file from the local file system.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class LocalFile
    extends File
{
    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var LocalFilesystem
     */
    protected $fs;

    /**
     * @param                 $fileName
     * @param LocalFilesystem $fs
     */
    public function __construct($fileName, LocalFilesystem $fs)
    {
        $fileName         = Util::normalizePath($fileName);
        $absoluteFileName = Util::normalizePath($fs->getBasePath() . $fileName);

        parent::__construct($absoluteFileName);
        $this->fileName = $fileName;
        $this->fs       = $fs;
    }

    /**
     * Get the underlaying filesystem for this file.
     *
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->fs;
    }

    public function getPathname()
    {
        return $this->fileName;
    }

    /**
     * Change file group.
     *
     * @param mixed $group
     *
     * @return bool
     */
    public function chgrp($group)
    {
        return chgrp($this->getRealPath(), $group);
    }

    /**
     * Change file mode.
     *
     * @param int  $mode
     *
     * @return bool
     */
    public function chmod($mode)
    {
        return chmod($this->getRealPath(), $mode);
    }

    /**
     * Change file owner.
     *
     * @param string|int $user
     *
     * @return bool
     */
    public function chown($user)
    {
        return chown($this->getRealPath(), $user);
    }

    /**
     * Copies file
     *
     * @param File $destination
     *
     * @return bool
     */
    public function copy(File $destination)
    {
        if ($destination instanceof LocalFile) {
            return copy($this->getRealPath(), $destination->getRealPath());
        }
        else {
            return Util::streamCopy($this, $destination);
        }
    }

    /**
     * Delete a file or directory.
     *
     * @return bool
     */
    public function delete()
    {
        if ($this->isDir()) {
            return rmdir($this->getRealPath());
        }
        else {
            return unlink($this->getRealPath());
        }
    }

    /**
     * Checks whether a file or directory exists.
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->getRealPath());
    }

    /**
     * Shared file locking. (reader)
     *
     * @param bool $noblocking
     *
     * @return bool
     */
    public function lockShared($noblocking = false)
    {
        // TODO: Implement lockShared() method.
    }

    /**
     * Exclusive file locking. (writer)
     *
     * @param bool $noblocking
     *
     * @return bool
     */
    public function lockExclusive($noblocking = false)
    {
        // TODO: Implement lockExclusive() method.
    }

    /**
     * Unlock file.
     *
     * @param File $path
     *
     * @return bool
     */
    public function unlock()
    {
        // TODO: Implement unlock() method.
    }

    /**
     * Makes directory
     *
     * @return bool
     */
    public function mkdir()
    {
        return mkdir($this->getRealPath());
    }

    /**
     * Makes directory
     *
     * @return bool
     */
    public function mkdirs()
    {
        return mkdir($this->getRealPath(), 0777, true);
    }

    /**
     * Renames a file or directory
     *
     * @param File $destination
     *
     * @return bool
     */
    public function rename(File $destination)
    {
        if ($destination instanceof LocalFile) {
            return rename($this->getRealPath(), $destination);
        }
        else {
            return Util::streamCopy($this, $destination) && $this->delete();
        }
    }

    /**
     * Sets access and modification time of file
     *
     * @param int $time  = time()
     * @param int $atime = time()
     *
     * @return bool
     */
    public function touch($time = null, $atime = null)
    {
        return touch($this->getRealPath(), $time, $atime);
    }

    /**
     * Gets an stream for the file.
     *
     * @param string $mode
     *
     * @return mixed
     */
    public function openStream($mode = 'r')
    {
        return fopen($this->getRealPath(), $mode);
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
        $pattern = Util::normalizePath($pattern);

        $substr = strlen($this->fs->getBasePath());

        return array_map(function ($path) use ($substr) {
            $path = substr($path, $substr);
            return new LocalFile($path, $this->fs);
        },
            glob($this->getRealPath() . '/' . $pattern));
    }

    public function listAll()
    {
        $files = scandir($this->getRealPath());

        // skip dot files
        $files = array_filter($files,
            function ($file) {
                return $file != '.' && $file != '..';
            });

        $parent = $this->getPathname();

        return array_map(function ($path) use ($parent) {
            return new LocalFile($parent . '/' . $path, $this->fs);
        },
            $files);
    }

    public function __toString()
    {
        return $this->fileName;
    }
}