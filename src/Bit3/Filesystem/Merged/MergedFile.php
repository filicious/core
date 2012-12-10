<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem\Merged;

use Bit3\Filesystem\Filesystem;
use Bit3\Filesystem\File;
use Bit3\Filesystem\AbstractFile;
use Bit3\Filesystem\FilesystemException;
use Bit3\Filesystem\Util;

/**
 * File from a mounted filesystem structure.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MergedFile
    extends VirtualFile
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

    public function __construct($parentPath, $mount, File $file, MergedFilesystem $fs)
    {
        parent::__construct($parentPath, $mount, $fs);
        $this->mount = $mount;
        $this->file  = $file;
        $this->fs    = $fs;
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
        return Util::normalizePath(parent::getPathname() . $this->file->getPathname());
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

    /**
     * Sets access and modification time of file.
     *
     * @param int $time
     * @param int $atime
     *
     * @return bool
     */
    public function touch($time = null, $atime = null)
    {
        return $this->file->touch($time, $atime);
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
    public function delete($recursive = false, $force = false)
    {
        return $this->file->delete($recursive, $force);
    }

    /**
     * Copies file
     *
     * @param File $destination
     * @param bool $recursive
     *
     * @return bool
     */
    public function copyTo(File $destination, $parents = false)
    {
        return $this->file->copyTo($destination, $parents);
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
    public function createDirectory($parents = false)
    {
        return $this->file->createDirectory($parents);
    }

    /**
     * Create new empty file.
     *
     * @return bool
     */
    public function createFile($parents = false)
    {
        return $this->file->createFile($parents);
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
    public function getMIMEName()
    {
        return $this->file->getMIMEName();
    }

    /**
     * Get mime content type.
     *
     * @param int $type
     *
     * @return string
     */
    public function getMIMEType()
    {
        return $this->file->getMIMEType();
    }

    /**
     * Get mime content type.
     *
     * @param int $type
     *
     * @return string
     */
    public function getMIMEEncoding()
    {
        return $this->file->getMIMEEncoding();
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
     * List all files.
     *
     * @return array<File>
     */
    public function ls()
    {
        $args = func_get_args();
        // fetch nested files and those from child fs.
        return array_merge(
            call_user_func_array(array('parent', 'ls'), $args),
            call_user_func_array(array($this->file, 'ls'), $args)
        );
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
