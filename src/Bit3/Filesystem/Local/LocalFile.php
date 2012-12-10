<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem\Local;

use Bit3\Filesystem\Filesystem;
use Bit3\Filesystem\File;
use Bit3\Filesystem\AbstractFile;
use Bit3\Filesystem\FilesystemException;
use Bit3\Filesystem\Util;

/**
 * A file from the local file system.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class LocalFile
    extends AbstractFile
{
    /**
     * @var string
     */
    protected $pathname;

    /**
     * @var string
     */
    protected $realpath;

    /**
     * @var LocalFilesystem
     */
    protected $fs;

    /**
     * @param                 $fileName
     * @param LocalFilesystem $fs
     */
    public function __construct($pathname, LocalFilesystem $fs)
    {
        parent::__construct($fs);
        $this->pathname = Util::normalizePath('/' . $pathname);
        $this->realpath = Util::normalizePath($fs->getConfig()->getBasePath() . '/' . $pathname);
    }
    
    /* (non-PHPdoc)
     * @see Bit3\Filesystem.File::getType()
     */
    public function getType() {
    	$type = 0;
    	if($this->exists()) {
    		is_file($this->realpath) && $type |= File::TYPE_FILE;
    		is_link($this->realpath) && $type |= File::TYPE_LINK;
    		is_dir($this->realpath) && $type |= File::TYPE_DIRECTORY;
    	}
    	return $type;
    }

    /**
     * Returns the absolute pathname.
     *
     * @return string
     */
    public function getPathname()
    {
        return $this->pathname;
    }

    /**
     * Get the link target of the link.
     *
     * @return string
     */
    public function getLinkTarget()
    {
        return $this->isLink() && readlink($this->realpath);
    }

    /**
     * Returns the the path of this pathname's parent, or <em>null</em> if this pathname does not name a parent directory.
     *
     * @return File|null
     */
    public function getParent()
    {
        return $this->pathname == '/' ? null : $this->fs->getFile(dirname($this->pathname));
    }

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getAccessTime()
    {
        return $this->exists() ? fileatime($this->realpath) : false;
    }

    /**
     * Sets the last-modified time of the file or directory named by this pathname.
     *
     * @param int $time
     */
    public function setAccessTime($time)
    {
        if ($this->exists()) {
            return touch($this->realpath, $this->getModifyTime(), time());
        }
        return false;
    }

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getCreationTime()
    {
        return $this->exists() ? filectime($this->realpath) : false;
    }

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getModifyTime()
    {
        return $this->exists() ? filemtime($this->realpath) : false;
    }

    /**
     * Sets the last-modified time of the file or directory named by this pathname.
     *
     * @param int $time
     */
    public function setModifyTime($time)
    {
        if ($this->exists()) {
            return touch($this->realpath, time(), $this->getAccessTime());
        }
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
        return touch($this->realpath);
    }

    /**
     * Get the size of the file denoted by this pathname.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->exists() ? filesize($this->realpath) : false;
    }

    /**
     * Get the owner of the file denoted by this pathname.
     *
     * @return string|int
     */
    public function getOwner()
    {
        return $this->exists() ? fileowner($this->realpath) : false;
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
        return $this->exists() ? chown($this->realpath, $user) : false;
    }

    /**
     * Get the group of the file denoted by this pathname.
     *
     * @return string|int
     */
    public function getGroup()
    {
        return $this->exists() ? filegroup($this->realpath) : false;
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
        return $this->exists() ? chgrp($this->realpath, $group) : false;
    }

    /**
     * Get the mode of the file denoted by this pathname.
     *
     * @return int
     */
    public function getMode()
    {
        return $this->exists() ? fileperms($this->realpath) : false;
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
        return $this->exists() ? chmod($this->realpath, $mode) : false;
    }

    /**
     * Test whether this pathname is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        return $this->exists() && is_readable($this->realpath);
    }

    /**
     * Test whether this pathname is writeable.
     *
     * @return bool
     */
    public function isWritable()
    {
        if ($this->exists()) {
            return is_writable($this->realpath);
        }

        $parent = $this->getParent();
        if ($parent) {
            return $parent->isWritable();
        }

        return false;
    }

    /**
     * Test whether this pathname is executeable.
     *
     * @return bool
     */
    public function isExecutable()
    {
        return $this->exists() && is_executable($this->realpath);
    }

    /**
     * Checks whether a file or directory exists.
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->realpath);
    }

    /**
     * Delete a file or directory.
     *
     * @return bool
     */
    public function delete($recursive = false, $force = false)
    {
        if ($this->isDirectory()) {
            if ($recursive) {
                /** @var File $file */
                foreach ($this->ls() as $file) {
                    if (!$file->delete(true, $force)) {
                        return false;
                    }
                }
            }
            else if (count($this->ls()) > 0) {
                return false;
            }
            return rmdir($this->realpath);
        }
        else {
            if (!$this->isWritable()) {
                if ($force) {
                    $this->setMode(0666);
                }
                else {
                    return false;
                }
            }
            return unlink($this->realpath);
        }
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
        if ($this->isDirectory()) {

        }
        else if ($this->isFile()) {
            if ($destination instanceof LocalFile) {
                return copy($this->realpath, $destination->realpath);
            }
            else {
                return Util::streamCopy($this, $destination);
            }
        }
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
        if ($destination instanceof LocalFile) {
            return rename($this->realpath, $destination);
        }
        else {
            return Util::streamCopy($this, $destination) && $this->delete();
        }
    }

    /**
     * Makes directory
     *
     * @return bool
     */
    public function createDirectory($parents = false)
    {
        if ($this->exists()) {
            return $this->isDirectory();
        }
        else if ($parents) {
            return mkdir($this->realpath, 0777, true);
        }
        else {
            return mkdir($this->realpath);
        }
    }

    /**
     * Create new empty file.
     *
     * @return bool
     */
    public function createFile($parents = false)
    {
        $parent = $this->getParent();

        if ($parents) {
            if (!($parent && $parent->createDirectory(true))) {
                return false;
            }
        }
        else if (!($parent && $parent->isDirectory())) {
            return false;
        }

        return touch($this->realpath);
    }

    /**
     * Get contents of the file. Returns <em>null</em> if file does not exists
     * and <em>false</em> on error (e.a. if file is a directory).
     *
     * @return string|null|bool
     */
    public function getContents()
    {
        if (!$this->exists()) {
            return null;
        }
        if (!$this->isFile()) {
            return false;
        }
        return file_get_contents($this->realpath);
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
        if ($this->exists() && !$this->isFile()) {
            return false;
        }
        return false !== file_put_contents($this->realpath, $content);
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
        if (!$this->exists()) {
            return null;
        }
        if (!$this->isFile()) {
            return false;
        }
        if (false !== ($f = fopen($this->realpath, 'ab'))) {
            if (false !== fwrite($f, $content)) {
                fclose($f);
                return true;
            }
            fclose($f);
        }
        return false;
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
        if (!$this->exists()) {
            return null;
        }
        if (!$this->isFile()) {
            return false;
        }
        if (false !== ($f = fopen($this->realpath, 'ab'))) {
            if (false !== ftruncate($f, $size)) {
                fclose($f);
                return filesize($this->realpath);
            }
            fclose($f);
        }
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
        return fopen($this->realpath, $mode);
    }

    /**
     * Calculate the md5 hash of this file.
     * Returns <em>false</em> on error (e.a. if file is a directory).
     *
     * @return string|null
     */
    public function getMD5($raw = false)
    {
        if (!$this->exists()) {
            return null;
        }
        if (!$this->isFile()) {
            return false;
        }
        return md5_file($this->realpath, $raw);
    }

    /**
     * Calculate the sha1 hash of this file.
     * Returns <em>false</em> on error (e.a. if file is a directory).
     *
     * @return string|null
     */
    public function getSHA1($raw = false)
    {
        if (!$this->exists()) {
            return null;
        }
        if (!$this->isFile()) {
            return false;
        }
        return sha1_file($this->realpath, $raw);
    }

    public function ls()
    {
        list($recursive, $bitmask, $globs, $callables, $globSearchPatterns) = Util::buildFilters($this, func_get_args());

        $pathname = $this->getPathname();

        $files = array();

        $currentFiles = scandir($this->realpath);

        foreach ($currentFiles as $path) {
            $file = new LocalFile($pathname . '/' . $path, $this->fs);

            $files[] = $file;

            if ($recursive &&
                $path != '.' &&
                $path != '..' &&
                $file->isDirectory() ||
                count($globSearchPatterns) &&
                Util::applyGlobFilters($file, $globSearchPatterns)
            ) {
                $recuriveFiles = $file->ls();

                $files = array_merge(
                    $files,
                    $recuriveFiles
                );
            }
        }

        $files = Util::applyFilters($files, $bitmask, $globs, $callables);

        return $files;
    }

    /**
     * Get the real url, e.g. file:/real/path/to/file to the pathname.
     *
     * @return string
     */
    public function getRealURL()
    {
        return 'file:' . $this->realpath;
    }

    /**
     * Get a public url, e.g. http://www.example.com/path/to/public/file to the file.
     *
     * @return string
     */
    public function getPublicURL()
    {
        $publicURLProvider = $this->fs->getPublicURLProvider();

        return $publicURLProvider ? $publicURLProvider->getPublicURL($this) : false;
    }
}
