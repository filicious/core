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
 * A virtual file in a merged filesystem.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class VirtualFile
    extends AbstractFile
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

    public function getType()
    {
        return File::TYPE_DIRECTORY;
    }

    public function getPathname()
    {
        return Util::normalizePath($this->parentPath . '/' . $this->fileName);
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
    public function getModifyTime()
    {
        return false;
    }

    /**
     * Sets the last-modified time of the file or directory named by this pathname.
     *
     * @param int $time
     */
    public function setModifyTime($time)
    {
        return false;
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
    public function delete($recursive = false, $force = false)
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
    public function copyTo(File $destination, $parents = false)
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
    public function createDirectory($parents = false)
    {
        return false;
    }

    /**
     * Create new empty file.
     *
     * @return bool
     */
    public function createFile($parents = false)
    {
        return false;
    }

    /**
     * Get contents of the file. Returns <em>null</em> if file does not exists
     * and <em>false</em> on error (e.a. if file is a directory).
     *
     * @return string|null|bool
     */
    public function getContents()
    {
        return false;
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
        return false;
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
        return false;
    }

    /**
     * Truncate a file to a given length. Returns the new length or
     * <em>false</em> on error (e.a. if file is a directory).
     * @param int $size
     *
     * @return int|bool
     */
    public function truncate($size = 0)
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
    public function open($mode = 'rb')
    {
        return false;
    }

    /**
     * Calculate the md5 hash of this file.
     * Returns <em>false</em> on error (e.a. if file is a directory).
     *
     * @param bool $raw Return binary hash, instead of string hash.
     *
     * @return string|null
     */
    public function getMD5($raw = false)
    {
        return null;
    }

    /**
     * Calculate the sha1 hash of this file.
     * Returns <em>false</em> on error (e.a. if file is a directory).
     *
     * @param bool $raw Return binary hash, instead of string hash.
     *
     * @return string|null
     */
    public function getSHA1($raw = false)
    {
        return null;
    }

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function ls()
    {
        $allMounts = $this->fs->mounts();
        $filterBase = $this->getPathname();
        // special case, we are the root element.
        if ($filterBase != '/')
        {
            $filterBase .= '/';
        }
        $offset = strlen($filterBase);

        // filter out non matching mount points in parent vfs
        $allMounts = array_filter($allMounts, function ($path) use ($filterBase) {
            return substr($path, 0, strlen($filterBase)) == $filterBase;
        });

        // get unique virtual children list
        $allRoots = array_unique(array_map(function ($path) use ($filterBase, $offset) {
            $length = strpos($path, '/', $offset+1) - $offset;
            if ($length > 0)
            {
                return substr($path, $offset, $length);
            }
            return substr($path, $offset);
        }, $allMounts));

        $arrFiles = array();

        foreach($allRoots as $subpath)
        {
            $objFile = new VirtualFile($this->getPathname(), $subpath, $this->fs);
            //  it a mount point? if so, return its root.
            if (in_array((string)$objFile, $allMounts))
            {
                $arrFiles[] = $this->fs->getFile((string)$objFile);
            } else {
                $arrFiles[] = $objFile;
            }
        }
        return $arrFiles;
    }

    /**
     * Get the real url, e.g. file:/real/path/to/file to the pathname.
     *
     * @return string
     */
    public function getRealURL()
    {
        return null;
    }

    /**
     * Get a public url, e.g. http://www.example.com/path/to/public/file to the file.
     *
     * @return string
     */
    public function getPublicURL()
    {
        return null;
    }

    public function __toString()
    {
       return $this->getPathname();
    }
}
