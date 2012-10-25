<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace bit3\filesystem\ftp;

use bit3\filesystem\Filesystem;
use bit3\filesystem\File;
use bit3\filesystem\BasicFileImpl;
use bit3\filesystem\FilesystemException;
use bit3\filesystem\Util;

/**
 * File from a mounted filesystem structure.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class FtpFile
    extends BasicFileImpl
{
    protected $pathname;

    /**
     * @var FtpFilesystem
     */
    protected $fs;

    public function __construct($pathname, FtpFilesystem $fs)
    {
        $this->pathname = Util::normalizePath($pathname);
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

    /**
     * Test whether this pathname is a file.
     *
     * @return bool
     */
    public function isFile()
    {
        // TODO: Implement isFile() method.
    }

    /**
     * Test whether this pathname is a link.
     *
     * @return bool
     */
    public function isLink()
    {
        // TODO: Implement isLink() method.
    }

    /**
     * Test whether this pathname is a directory.
     *
     * @return bool
     */
    public function isDirectory()
    {
        // TODO: Implement isDirectory() method.
    }

    /**
     * Returns the absolute pathname.
     *
     * @return string
     */
    public function getPathname()
    {
        // TODO: Implement getPathname() method.
    }

    /**
     * Get the link target of the link.
     *
     * @return string
     */
    public function getLinkTarget()
    {
        // TODO: Implement getLinkTarget() method.
    }

    /**
     * Returns the the path of this pathname's parent, or <em>null</em> if this pathname does not name a parent directory.
     *
     * @return File|null
     */
    public function getParent()
    {
        // TODO: Implement getParent() method.
    }

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getAccessTime()
    {
        // TODO: Implement getAccessTime() method.
    }

    /**
     * Sets the last-modified time of the file or directory named by this pathname.
     *
     * @param int $time
     */
    public function setAccessTime($time)
    {
        // TODO: Implement setAccessTime() method.
    }

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getCreationTime()
    {
        // TODO: Implement getCreationTime() method.
    }

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getLastModified()
    {
        // TODO: Implement getLastModified() method.
    }

    /**
     * Sets the last-modified time of the file or directory named by this pathname.
     *
     * @param int $time
     */
    public function setLastModified($time)
    {
        // TODO: Implement setLastModified() method.
    }

    /**
     * Get the size of the file denoted by this pathname.
     *
     * @return int
     */
    public function getSize()
    {
        // TODO: Implement getSize() method.
    }

    /**
     * Get the owner of the file denoted by this pathname.
     *
     * @return string|int
     */
    public function getOwner()
    {
        // TODO: Implement getOwner() method.
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
        // TODO: Implement setOwner() method.
    }

    /**
     * Get the group of the file denoted by this pathname.
     *
     * @return string|int
     */
    public function getGroup()
    {
        // TODO: Implement getGroup() method.
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
        // TODO: Implement setGroup() method.
    }

    /**
     * Get the mode of the file denoted by this pathname.
     *
     * @return int
     */
    public function getMode()
    {
        // TODO: Implement getMode() method.
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
        // TODO: Implement setMode() method.
    }

    /**
     * Test whether this pathname is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        // TODO: Implement isReadable() method.
    }

    /**
     * Test whether this pathname is writeable.
     *
     * @return bool
     */
    public function isWritable()
    {
        // TODO: Implement isWritable() method.
    }

    /**
     * Test whether this pathname is executeable.
     *
     * @return bool
     */
    public function isExecutable()
    {
        // TODO: Implement isExecutable() method.
    }

    /**
     * Checks whether a file or directory exists.
     *
     * @return bool
     */
    public function exists()
    {
        // TODO: Implement exists() method.
    }

    /**
     * Delete a file or directory.
     *
     * @return bool
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }

    /**
     * Copies file
     *
     * @param File $destination
     * @param bool $recursive
     *
     * @return bool
     */
    public function copyTo(File $destination, $recursive = false)
    {
        // TODO: Implement copyTo() method.
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
        // TODO: Implement moveTo() method.
    }

    /**
     * Makes directory
     *
     * @return bool
     */
    public function mkdir()
    {
        // TODO: Implement mkdir() method.
    }

    /**
     * Makes directories
     *
     * @return bool
     */
    public function mkdirs()
    {
        // TODO: Implement mkdirs() method.
    }

    /**
     * Create new empty file.
     *
     * @return bool
     */
    public function createNewFile()
    {
        // TODO: Implement createNewFile() method.
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
        // TODO: Implement openStream() method.
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
        // TODO: Implement glob() method.
    }

    /**
     * Get the real url, e.g. file:/real/path/to/file to the pathname.
     *
     * @return string
     */
    public function getRealUrl()
    {
        // TODO: Implement getRealUrl() method.
    }

    /**
     * Get a public url, e.g. http://www.example.com/path/to/public/file to the file.
     *
     * @return string
     */
    public function getPublicUrl()
    {
        // TODO: Implement getPublicUrl() method.
    }
}
