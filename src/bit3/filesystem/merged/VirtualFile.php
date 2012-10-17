<?php

namespace bit3\filesystem\merged;

use bit3\filesystem\Filesystem;
use bit3\filesystem\File;
use bit3\filesystem\FilesystemException;

class VirtualFile extends File
{
    /**
     * @var string
     */
    protected $parentPath;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var MergedFilesystem
     */
    protected $fs;

    /**
     * @param string           $parentPath
     * @param string           $fileName
     * @param MergedFilesystem $fs
     */
    public function __construct($parentPath, $fileName, MergedFilesystem $fs)
    {
        $this->parentPath = $parentPath != '.' ? $parentPath : '';
        $this->fileName = $fileName;
        $this->fs = $fs;
    }

    public function getPath()
    {
        return $this->parentPath;
    }

    public function getFilename()
    {
        return $this->fileName;
    }

    public function getExtension()
    {
        return preg_replace('#^.*\.(\w+)$', '$1', $this->fileName);
    }

    public function getBasename($suffix = null)
    {
        return basename($this->fileName, $suffix);
    }

    public function getPathname()
    {
        return $this->parentPath . '/' . $this->fileName;
    }

    public function getPerms()
    {
        return 0777;
    }

    public function getInode()
    {
        return -1;
    }

    public function getSize()
    {
        return 0;
    }

    public function getOwner()
    {
        return '';
    }

    public function getGroup()
    {
        return '';
    }

    public function getATime()
    {
        return time();
    }

    public function getMTime()
    {
        return time();
    }

    public function getCTime()
    {
        return time();
    }

    public function getType()
    {
        return 'dir';
    }

    public function isWritable()
    {
        return false;
    }

    public function isReadable()
    {
        return false;
    }

    public function isExecutable()
    {
        return false;
    }

    public function isFile()
    {
        return false;
    }

    public function isDir()
    {
        return true;
    }

    public function isLink()
    {
        return true;
    }

    public function getLinkTarget()
    {
        return '.';
    }

    public function getRealPath()
    {
        return $this->fileName;
    }

    public function getFileInfo($class_name = null)
    {
        return $this;
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

    /**
     * Change file group.
     *
     * @param mixed $group
     *
     * @return bool
     */
    public function chgrp($group)
    {
        return false;
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
        return false;
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
        return false;
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
        return false;
    }

    /**
     * Delete a file or directory.
     *
     * @return bool
     */
    public function delete()
    {
        return false;
    }

    /**
     * Checks whether a file or directory exists.
     *
     * @return bool
     */
    public function exists()
    {
        return false;
    }

    /**
     * Portable advisory shared file locking. (reader)
     *
     * @param bool $noblocking
     *
     * @return bool
     */
    public function lockShared($noblocking = false)
    {
        return false;
    }

    /**
     * Portable advisory exclusive file locking. (writer)
     *
     * @param bool $noblocking
     *
     * @return bool
     */
    public function lockExclusive($noblocking = false)
    {
        return false;
    }

    /**
     * Unlock a file.
     *
     * @param File $path
     *
     * @return bool
     */
    public function unlock()
    {
        return false;
    }

    /**
     * Makes directory
     *
     * @return bool
     */
    public function mkdir()
    {
        return false;
    }

    /**
     * Makes directories
     *
     * @return bool
     */
    public function mkdirs()
    {
        return false;
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
        return false;
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
        return false;
    }

    public function openFile($open_mode = 'r', $use_include_path = false, $context = null)
    {
        return null;
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
        return false;
    }

    /**
     * Find pathnames matching a pattern.
     *
     * @param string $pattern
     * @param int    $flags Use GLOB_* flags. Not all may supported on each filesystem.
     *
     * @return array<File>
     */
    public function glob($pattern, $flags = 0)
    {
        return $this->fs->glob($this->parentPath . '/' . $this->fileName . '/' . $pattern, $flags);
    }

    public function __toString()
    {
        return $this->parentPath . '/' . $this->fileName;
    }
}
