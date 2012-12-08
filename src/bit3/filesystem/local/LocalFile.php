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
use bit3\filesystem\BasicFileImpl;
use bit3\filesystem\FilesystemException;
use bit3\filesystem\Util;

/**
 * A file from the local file system.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class LocalFile
    extends BasicFileImpl
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
        $this->realpath = Util::normalizePath($fs->getBasePath() . '/' . $pathname);
    }

    /**
     * Test whether this pathname is a file.
     *
     * @return bool
     */
    public function isFile()
    {
        return $this->exists() && is_file($this->realpath);
    }

    /**
     * Test whether this pathname is a link.
     *
     * @return bool
     */
    public function isLink()
    {
        return $this->exists() && is_link($this->realpath);
    }

    /**
     * Test whether this pathname is a directory.
     *
     * @return bool
     */
    public function isDirectory()
    {
        return $this->exists() && is_dir($this->realpath);
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
            return touch($this->realpath, $this->getLastModified(), time());
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
    public function getLastModified()
    {
        return $this->exists() ? filemtime($this->realpath) : false;
    }

    /**
     * Sets the last-modified time of the file or directory named by this pathname.
     *
     * @param int $time
     */
    public function setLastModified($time)
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
    public function delete($recursive = false)
    {
        if ($this->isDirectory()) {
            if ($recursive) {
                /** @var File $file */
                foreach ($this->listAll() as $file) {
                    if (!$file->delete(true)) {
                        return false;
                    }
                }
            }
            else if (count($this->listAll()) > 0) {
                return false;
            }
            return rmdir($this->realpath);
        }
        else if ($this->isFile() || $this->isLink()) {
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
    public function copyTo(File $destination, $recursive = false)
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
    public function mkdir()
    {
        return mkdir($this->realpath);
    }

    /**
     * Makes directory
     *
     * @return bool
     */
    public function mkdirs()
    {
        return $this->exists() ? true : mkdir($this->realpath, 0777, true);
    }

    /**
     * Create new empty file.
     *
     * @return bool
     */
    public function createNewFile()
    {
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
    public function hashMD5($raw = false)
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
    public function hashSHA1($raw = false)
    {
        if (!$this->exists()) {
            return null;
        }
        if (!$this->isFile()) {
            return false;
        }
        return sha1_file($this->realpath, $raw);
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
            glob($this->realpath . '/' . $pattern));
    }

    public function listAll()
    {
        $files = scandir($this->realpath);

        // skip dot files
        $files = array_filter($files,
            function ($file) {
                return $file != '.' && $file != '..';
            });

        $parent = $this->getPathname();
        $fs = $this->fs;

        return array_map(function ($path) use ($parent, $fs) {
            return new LocalFile($parent . '/' . $path, $fs);
        }, array_values($files));
    }

    /**
     * Get the real local path to the pathname, e.g. /real/path/to/file.
     *
     * @return string
     */
    public function getRealPath()
    {
        return $this->realpath;
    }

    /**
     * Get the real url, e.g. file:/real/path/to/file to the pathname.
     *
     * @return string
     */
    public function getRealUrl()
    {
        return 'file:' . $this->realpath;
    }

    /**
     * Get a public url, e.g. http://www.example.com/path/to/public/file to the file.
     *
     * @return string
     */
    public function getPublicUrl()
    {
        $publicUrlProvider = $this->fs->getPublicUrlProvider();

        return $publicUrlProvider ? $publicUrlProvider->getPublicUrl($this) : false;
    }
}