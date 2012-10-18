<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace bit3\filesystem\merged;

use bit3\filesystem\Filesystem;
use bit3\filesystem\File;
use bit3\filesystem\FilesystemException;

/**
 * File from a mounted filesystem structure.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class MergedFile
    extends File
{
    /**
     * @var string
     */
    protected $mount;

    /**
     * The "real" file object.
     *
     * @var File
     */
    protected $file;

    /**
     * @var MergedFilesystem
     */
    protected $fs;

    public function __construct($mount, File $file, MergedFilesystem $fs)
    {
        $this->mount = $mount;
        $this->file  = $file;
        $this->fs    = $fs;
        $this->setFileClass('bit3\filesystem\File');
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

    public function getPath()
    {
        return $this->mount . $this->file->getPath();
    }

    public function getFilename()
    {
        return $this->mount . $this->file->getFilename();
    }

    public function getExtension()
    {
        return $this->mount . $this->file->getExtension();
    }

    public function getBasename($suffix = null)
    {
        return $this->mount . $this->file->getBasename($suffix);
    }

    public function getPathname()
    {
        return $this->mount . $this->file->getPathname();
    }

    public function getPerms()
    {
        return $this->file->getPerms();
    }

    public function getInode()
    {
        return $this->file->getInode();
    }

    public function getSize()
    {
        return $this->file->getSize();
    }

    public function getOwner()
    {
        return $this->file->getOwner();
    }

    public function getGroup()
    {
        return $this->file->getGroup();
    }

    public function getATime()
    {
        return $this->file->getATime();
    }

    public function getMTime()
    {
        return $this->file->getMTime();
    }

    public function getCTime()
    {
        return $this->file->getCTime();
    }

    public function getType()
    {
        return $this->file->getType();
    }

    public function isWritable()
    {
        return $this->file->isWritable();
    }

    public function isReadable()
    {
        return $this->file->isReadable();
    }

    public function isExecutable()
    {
        return $this->file->isExecutable();
    }

    public function isFile()
    {
        return $this->file->isFile();
    }

    public function isDir()
    {
        return $this->file->isDir();
    }

    public function isLink()
    {
        return $this->file->isLink();
    }

    public function getLinkTarget()
    {
        return $this->file->getLinkTarget();
    }

    public function getRealPath()
    {
        return $this->file->getRealPath();
    }

    public function getFileInfo($class_name = null)
    {
        return $this->file->getFileInfo($class_name);
    }

    public function getPathInfo($class_name = null)
    {
        return new MergedFile($this->mount, $this->file->getPathInfo($class_name), $this->fs);
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
        return $this->file->chgrp($group);
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
        return $this->file->chmod($mode);
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
        return $this->file->chown($user);
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
        return $this->file->copy($destination);
    }

    /**
     * Delete a file or directory.
     *
     * @return bool
     */
    public function delete()
    {
        return $this->file->delete();
    }

    /**
     * Checks whether a file or directory exists.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->file->exists();
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
        return $this->file->lockShared($noblocking);
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
        return $this->file->lockExclusive($noblocking);
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
        return $this->file->unlock();
    }

    /**
     * Makes directory
     *
     * @return bool
     */
    public function mkdir()
    {
        return $this->file->mkdir();
    }

    /**
     * Makes directories
     *
     * @return bool
     */
    public function mkdirs()
    {
        return $this->file->mkdirs();
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
        return $this->file->rename($destination);
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
        return $this->file->touch($time, $atime);
    }

    /**
     * @param string $open_mode
     * @param bool   $use_include_path
     * @param null   $context
     *
     * @return null|\SplFileObject
     */
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
        return $this->file->glob($pattern, $flags);
    }
}
