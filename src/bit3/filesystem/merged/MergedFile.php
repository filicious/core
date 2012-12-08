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
 * File from a mounted filesystem structure.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class MergedFile
    implements File
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
        $this->setFileClass('bit3\filesystem\merged\MergedFile');
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
        return $this->file->isFile();
    }

    public function isLink()
    {
        return $this->file->isLink();
    }

    public function isDirectory()
    {
        return $this->file->isDirectory();
    }

    /**
     * Get the type of this file.
     *
     * @return "file"|"directory"|"link"|"unknown"
     */
    public function getType()
    {
        return $this->file->getType();
    }

    public function getPathname()
    {
        return $this->mount . $this->file->getPathname();
    }

    public function getLinkTarget()
    {
        return $this->file->getLinkTarget();
    }

    public function getBasename($suffix = null)
    {
        return $this->mount . $this->file->getBasename($suffix);
    }

    /**
     * Get the extension of the file.
     *
     * @return mixed
     */
    public function getExtension()
    {
        return $this->file->getExtension();
    }

    /**
     * Returns the the path of this pathname's parent, or <em>null</em> if this pathname does not name a parent directory.
     *
     * @return File|null
     */
    public function getParent()
    {
        $parent = $this->file->getParent();

        if ($parent === null) {
            $parent = dirname($this->mount);

            if ($parent != '.') {
                return $this->fs->getFile($parent);
            }

            return null;
        }

        return $parent;
    }

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getAccessTime()
    {
        return $this->file->getAccessTime();
    }

    /**
     * Sets the last-modified time of the file or directory named by this pathname.
     *
     * @param int $time
     */
    public function setAccessTime($time)
    {
        return $this->file->setAccessTime($time);
    }

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getCreationTime()
    {
        return $this->file->getCreationTime();
    }

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getModifyTime()
    {
        return $this->file->getModifyTime();
    }

    /**
     * Sets the last-modified time of the file or directory named by this pathname.
     *
     * @param int $time
     */
    public function setModifyTime($time)
    {
        return $this->file->setModifyTime($time);
    }

    public function getSize()
    {
        return $this->file->getSize();
    }

    public function getOwner()
    {
        return $this->file->getOwner();
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
        return $this->file->setOwner($user);
    }

    public function getGroup()
    {
        return $this->file->getGroup();
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
        return $this->file->setGroup($group);
    }

    /**
     * Get the mode of the file denoted by this pathname.
     *
     * @return int
     */
    public function getMode()
    {
        return $this->file->getMode();
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
        return $this->file->setMode($mode);
    }

    public function isReadable()
    {
        return $this->file->isReadable();
    }

    public function isWritable()
    {
        return $this->file->isWritable();
    }

    public function isExecutable()
    {
        return $this->file->isExecutable();
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
     * Delete a file or directory.
     *
     * @return bool
     */
    public function delete($recursive = false)
    {
        return $this->file->delete($recursive);
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
        return $this->file->copyTo($destination, $recursive);
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
        return $this->file->moveTo($destination);
    }

    /**
     * Makes directory
     *
     * @return bool
     */
    public function createDirectory()
    {
        return $this->file->createDirectory();
    }

    /**
     * Makes directories
     *
     * @return bool
     */
    public function createDirectories()
    {
        return $this->file->createDirectories();
    }

    /**
     * Create new empty file.
     *
     * @return bool
     */
    public function createFile()
    {
        return $this->file->createFile();
    }

    /**
     * Get contents of the file. Returns <em>null</em> if file does not exists
     * and <em>false</em> on error (e.a. if file is a directory).
     *
     * @return string|null|bool
     */
    public function getContents()
    {
        return $this->file->getContents();
    }

    /**
     * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
     *
     * @param string $content
     *
     * @return bool
     */
    public function setContents($content)
    {
        return $this->file->setContents($content);
    }

    /**
     * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
     *
     * @param string $content
     *
     * @return bool
     */
    public function appendContents($content)
    {
        return $this->file->appendContents($content);
    }

    /**
     * Truncate a file to a given length. Returns the new length or
     * <em>false</em> on error (e.a. if file is a directory).
     *
     * @param int $size
     *
     * @return int|bool
     */
    public function truncate($size = 0)
    {
        return $this->file->truncate($size);
    }

    /**
     * Gets an stream for the file.
     *
     * @param string $mode
     *
     * @return mixed
     */
    public function open($mode = 'rb')
    {
        return $this->file->open($mode);
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
        return $this->file->getMimeName();
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
        return $this->file->getMimeType();
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
        return $this->file->getMimeEncoding();
    }

    /**
     * Calculate the md5 hash of this file.
     * Returns <em>false</em> on error (e.a. if file is a directory).
     *
     * @return string|null
     */
    public function getMD5($raw = false)
    {
        return $this->file->getMD5($raw);
    }

    /**
     * Calculate the sha1 hash of this file.
     * Returns <em>false</em> on error (e.a. if file is a directory).
     *
     * @return string|null
     */
    public function getSHA1($raw = false)
    {
        return $this->file->getSHA1($raw);
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

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function globFiles($pattern)
    {
        return $this->file->globFiles($pattern);
    }

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function globDirectories($pattern)
    {
        return $this->file->globDirectories($pattern);
    }

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function listAll()
    {
        return $this->file->listAll();
    }

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function listFiles()
    {
        return $this->file->listFiles();
    }

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function listDirectories()
    {
        return $this->file->listDirectories();
    }

    /**
     * Get the real url, e.g. file:/real/path/to/file to the pathname.
     *
     * @return string
     */
    public function getRealURL()
    {
        return $this->file->getRealURL();
    }

    /**
     * Get a public url, e.g. http://www.example.com/path/to/public/file to the file.
     *
     * @return string
     */
    public function getPublicURL()
    {
        return $this->file->getPublicURL();
    }
}
