<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace bit3\filesystem;

use SplFileInfo;
use Traversable;
use IteratorAggregate;
use ArrayIterator;

/**
 * A file object
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
interface File
    extends IteratorAggregate
{
    /**
     * Get the underlaying filesystem for this pathname.
     *
     * @return Filesystem
     */
    public function getFilesystem();

    /**
     * Test whether this pathname is a file.
     *
     * @return bool
     */
    public function isFile();

    /**
     * Test whether this pathname is a link.
     *
     * @return bool
     */
    public function isLink();

    /**
     * Test whether this pathname is a directory.
     *
     * @return bool
     */
    public function isDirectory();

    /**
     * Get the type of this file.
     *
     * @return "file"|"directory"|"link"|"unknown"
     */
    public function getType();

    /**
     * Returns the absolute pathname.
     *
     * @return string
     */
    public function getPathname();

    /**
     * Get the link target of the link.
     *
     * @return string
     */
    public function getLinkTarget();

    /**
     * Get the name of the file or directory.
     *
     * @return string
     */
    public function getBasename($suffix = '');

    /**
     * Get the extension of the file.
     *
     * @return mixed
     */
    public function getExtension();

    /**
     * Returns the the path of this pathname's parent, or <em>null</em> if this pathname does not name a parent directory.
     *
     * @return File|null
     */
    public function getParent();

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getAccessTime();

    /**
     * Sets the last-modified time of the file or directory named by this pathname.
     *
     * @param int $time
     */
    public function setAccessTime($time);

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getCreationTime();

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getLastModified();

    /**
     * Sets the last-modified time of the file or directory named by this pathname.
     *
     * @param int $time
     */
    public function setLastModified($time);

    /**
     * Get the size of the file denoted by this pathname.
     *
     * @return int
     */
    public function getSize();

    /**
     * Get the owner of the file denoted by this pathname.
     *
     * @return string|int
     */
    public function getOwner();

    /**
     * Set the owner of the file denoted by this pathname.
     *
     * @param string|int $user
     *
     * @return bool
     */
    public function setOwner($user);

    /**
     * Get the group of the file denoted by this pathname.
     *
     * @return string|int
     */
    public function getGroup();

    /**
     * Change the group of the file denoted by this pathname.
     *
     * @param mixed $group
     *
     * @return bool
     */
    public function setGroup($group);

    /**
     * Get the mode of the file denoted by this pathname.
     *
     * @return int
     */
    public function getMode();

    /**
     * Set the mode of the file denoted by this pathname.
     *
     * @param int  $mode
     *
     * @return bool
     */
    public function setMode($mode);

    /**
     * Test whether this pathname is readable.
     *
     * @return bool
     */
    public function isReadable();

    /**
     * Test whether this pathname is writeable.
     *
     * @return bool
     */
    public function isWritable();

    /**
     * Test whether this pathname is executeable.
     *
     * @return bool
     */
    public function isExecutable();

    /**
     * Checks whether a file or directory exists.
     *
     * @return bool
     */
    public function exists();

    /**
     * Delete a file or directory.
     *
     * @return bool
     */
    public function delete();

    /**
     * Copies file
     *
     * @param File $destination
     * @param bool $recursive
     *
     * @return bool
     */
    public function copyTo(File $destination, $recursive = false);

    /**
     * Renames a file or directory
     *
     * @param File $destination
     *
     * @return bool
     */
    public function moveTo(File $destination);

    /**
     * Makes directory
     *
     * @return bool
     */
    public function mkdir();

    /**
     * Makes directories
     *
     * @return bool
     */
    public function mkdirs();

    /**
     * Create new empty file.
     *
     * @return bool
     */
    public function createNewFile();

    /**
     * Get contents of the file. Returns <em>null</em> if file does not exists
     * and <em>false</em> on error (e.a. if file is a directory).
     *
     * @return string|null|bool
     */
    public function getContents();

    /**
     * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
     *
     * @param string $content
     *
     * @return bool
     */
    public function setContents($content);

    /**
     * Write contents to a file. Returns <em>false</em> on error (e.a. if file is a directory).
     *
     * @param string $content
     *
     * @return bool
     */
    public function appendContents($content);

    /**
     * Truncate a file to a given length. Returns the new length or
     * <em>false</em> on error (e.a. if file is a directory).
     * @param int $size
     *
     * @return int|bool
     */
    public function truncate($size = 0);

    /**
     * Gets an stream for the file. May return <em>null</em> if streaming is not supported.
     *
     * @param string $mode
     *
     * @return resource|null
     */
    public function openStream($mode = 'rb');

    /**
     * Calculate the md5 hash of this file.
     * Returns <em>false</em> on error (e.a. if file is a directory).
     *
     * @param bool $raw Return binary hash, instead of string hash.
     *
     * @return string|null
     */
    public function hashMD5($raw = false);

    /**
     * Calculate the sha1 hash of this file.
     * Returns <em>false</em> on error (e.a. if file is a directory).
     *
     * @param bool $raw Return binary hash, instead of string hash.
     *
     * @return string|null
     */
    public function hashSHA1($raw = false);

    /**
     * Find pathnames matching a pattern.
     *
     * @param string $pattern
     * @param int    $flags Use GLOB_* flags. Not all may supported on each filesystem.
     *
     * @return array<File>
     */
    public function glob($pattern);

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function globFiles($pattern);

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function globDirectories($pattern);

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function listAll();

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function listFiles();

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function listDirectories();

    /**
     * Get the real url, e.g. file:/real/path/to/file to the pathname.
     *
     * @return string
     */
    public function getRealUrl();

    /**
     * Get a public url, e.g. http://www.example.com/path/to/public/file to the file.
     *
     * @return string
     */
    public function getPublicUrl();
}
