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
use bit3\filesystem\BasicFileImpl;
use bit3\filesystem\FilesystemException;

/**
 * A virtual file in a merged filesystem.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class VirtualFile
    extends BasicFileImpl
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
        $this->parentPath = $parentPath != '.'
            ? $parentPath
            : '';
        $this->fileName   = $fileName;
        $this->fs         = $fs;
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

    public function isFile()
    {
        return false;
    }

    public function isDirectory()
    {
        return true;
    }

    public function isLink()
    {
        return false;
    }

    public function getType()
    {
        return 'dir';
    }

    public function getPathname()
    {
        return $this->parentPath . '/' . $this->fileName;
    }

    public function getLinkTarget()
    {
        return false;
    }

    public function getBasename($suffix = null)
    {
        return basename($this->fileName, $suffix);
    }

    /**
     * Returns the the path of this pathname's parent, or <em>null</em> if this pathname does not name a parent directory.
     *
     * @return File|null
     */
    public function getParent()
    {
        return $this->parentPath ? $this->fs->getFile(dirname($this->parentPath)) : null;
    }

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getAccessTime()
    {
        return false;
    }

    /**
     * Sets the last-modified time of the file or directory named by this pathname.
     *
     * @param int $time
     */
    public function setAccessTime($time)
    {
        return false;
    }

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getCreationTime()
    {
        return false;
    }

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getLastModified()
    {
        return false;
    }

    /**
     * Sets the last-modified time of the file or directory named by this pathname.
     *
     * @param int $time
     */
    public function setLastModified($time)
    {
        return false;
    }

    public function getSize()
    {
        return 0;
    }

    public function getOwner()
    {
        return -1;
    }

    /**
     * Set the owner of the file denoted by this pathname.
     *
     * @param string|int $user
     *
     * @return bool
     */
    public function setOwner($user)
    {
        return false;
    }

    public function getGroup()
    {
        return -1;
    }

    /**
     * Change the group of the file denoted by this pathname.
     *
     * @param mixed $group
     *
     * @return bool
     */
    public function setGroup($group)
    {
        return false;
    }

    public function getMode()
    {
        return 0555;
    }

    /**
     * Set the mode of the file denoted by this pathname.
     *
     * @param int  $mode
     *
     * @return bool
     */
    public function setMode($mode)
    {
        return false;
    }

    public function isReadable()
    {
        return true;
    }

    public function isWritable()
    {
        return false;
    }

    public function isExecutable()
    {
        return true;
    }

    /**
     * Checks whether a file or directory exists.
     *
     * @return bool
     */
    public function exists()
    {
        return true;
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
     * Copies file
     *
     * @param File $destination
     *
     * @return bool
     */
    public function copyTo(File $destination, $recursive = false)
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
    public function moveTo(File $destination)
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
     * Create new empty file.
     *
     * @return bool
     */
    public function createNewFile()
    {
        return false;
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

    /**
     * Get the real url, e.g. file:/real/path/to/file to the pathname.
     *
     * @return string
     */
    public function getRealUrl()
    {
        return null;
    }

    /**
     * Get a public url, e.g. http://www.example.com/path/to/public/file to the file.
     *
     * @return string
     */
    public function getPublicUrl()
    {
        return null;
    }

    public function __toString()
    {
        return $this->parentPath . '/' . $this->fileName;
    }
}
