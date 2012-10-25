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
        $this->pathname = Util::normalizePath('/' . $pathname);
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
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat['isFile'] : false;
    }

    /**
     * Test whether this pathname is a link.
     *
     * @return bool
     */
    public function isLink()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat['isLink'] : false;
    }

    /**
     * Test whether this pathname is a directory.
     *
     * @return bool
     */
    public function isDirectory()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat['isDirectory'] : false;
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
        $stat = $this->fs->ftpStat($this);

        return $stat && $stat['isLink'] ? $stat['target'] : false;
    }

    /**
     * Returns the the path of this pathname's parent, or <em>null</em> if this pathname does not name a parent directory.
     *
     * @return File|null
     */
    public function getParent()
    {
        $parent = dirname($this->pathname);

        if ($parent != '.') {
            return $this->fs->getFile($parent);
        }

        return null;
    }

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getAccessTime()
    {
        return $this->getLastModified();
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
        return $this->getLastModified();
    }

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getLastModified()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat['modified'] : false;
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

    /**
     * Get the size of the file denoted by this pathname.
     *
     * @return int
     */
    public function getSize()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat['size'] : false;
    }

    /**
     * Get the owner of the file denoted by this pathname.
     *
     * @return string|int
     */
    public function getOwner()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat['user'] : false;
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

    /**
     * Get the group of the file denoted by this pathname.
     *
     * @return string|int
     */
    public function getGroup()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat['group'] : false;
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

    /**
     * Get the mode of the file denoted by this pathname.
     *
     * @return int
     */
    public function getMode()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat['mode'] : false;
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
        return $this->fs->ftpChmod($this, $mode);
    }

    /**
     * Test whether this pathname is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat['mode'] & 0444 : false;
    }

    /**
     * Test whether this pathname is writeable.
     *
     * @return bool
     */
    public function isWritable()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat['mode'] & 0222 : false;
    }

    /**
     * Test whether this pathname is executeable.
     *
     * @return bool
     */
    public function isExecutable()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat['mode'] & 0111 : false;
    }

    /**
     * Checks whether a file or directory exists.
     *
     * @return bool
     */
    public function exists()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? true : false;
    }

    /**
     * Delete a file or directory.
     *
     * @return bool
     */
    public function delete()
    {
        return $this->fs->ftpDelete($this);
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
        Util::streamCopy($this, $destination);
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
        if ($destination instanceof FtpFile && $destination->getFilesystem() == $this->getFilesystem()) {
            $this->fs->ftpRename($this, $destination);
        }
        else {
            Util::streamCopy($this, $destination);
            $this->fs->ftpDelete($this);
        }
    }

    /**
     * Makes directory
     *
     * @return bool
     */
    public function mkdir()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $this->fs->ftpMkdir($this) : $stat['isDirectory'];
    }

    /**
     * Makes directories
     *
     * @return bool
     */
    public function mkdirs()
    {
        $stat = $this->fs->ftpStat($this);

        if (!$stat) {
            $parent = $this->getParent();

            if ($parent) {
                $parent->mkdirs();
            }
        }

        return $this->mkdir();
    }

    /**
     * Create new empty file.
     *
     * @return bool
     */
    public function createNewFile()
    {
        return $this->fs->ftpPut($this, '');
    }

    /**
     * Get contents of the file. Returns <em>null</em> if file does not exists
     * and <em>false</em> on error (e.a. if file is a directory).
     *
     * @return string|null|bool
     */
    public function getContents()
    {
        return $this->fs->ftpGet($this);
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
        return $this->fs->ftpPut($this, $content);
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
        $previous = $this->getContents();
        return $this->fs->ftpPut($this, $previous . $content);
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
        $content = '';
        if ($size > 0) {
            $content = $this->getContents();
            $content = substr($content, 0, $size);
        }
        return $this->fs->ftpPut($this, $content);
    }

    /**
     * Gets an stream for the file.
     *
     * @param string $mode
     *
     * @return mixed
     */
    public function openStream($mode = 'rb')
    {
        $config = $this->fs->getConfig();

        $url = $config->getSsl() ? 'ftps://' : 'ftp://';
        $url .= $config->getUsername();
        if ($config->getPassword()) {
            $url .= ':' . $config->getPassword();
        }
        $url .= '@' . $config->getHost();
        $url .= ':' . $config->getPort();
        $url .= $config->getPath();
        $url .= $this->pathname;

        return fopen($url, $mode);
    }

    /**
     * Calculate the md5 hash of this file.
     * Returns <em>false</em> on error (e.a. if file is a directory).
     *
     * @param bool $raw Return binary hash, instead of string hash.
     *
     * @return string|null
     */
    public function hashMD5($raw = false)
    {
        return md5($this->getContents(), $raw);
    }

    /**
     * Calculate the sha1 hash of this file.
     * Returns <em>false</em> on error (e.a. if file is a directory).
     *
     * @param bool $raw Return binary hash, instead of string hash.
     *
     * @return string|null
     */
    public function hashSHA1($raw = false)
    {
        return sha1($this->getContents(), $raw);
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
