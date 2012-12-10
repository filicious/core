<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem\FTP;

use Bit3\Filesystem\FS;
use Bit3\Filesystem\Filesystem;
use Bit3\Filesystem\File;
use Bit3\Filesystem\AbstractFile;
use Bit3\Filesystem\Util;
use Exception;

/**
 * File from a mounted filesystem structure.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class FTPFile
    extends AbstractFile
{
    protected $pathname;

    /**
     * @var FTPFilesystem
     */
    protected $fs;

    public function __construct($pathname, FTPFilesystem $fs)
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
    
    /* (non-PHPdoc)
     * @see Bit3\Filesystem.File::getType()
     */
    public function getType() {
    	$type = 0;
    	$stat = $this->fs->ftpStat($this);
    	if($stat) {
    		$stat->isFile && $type |= File::TYPE_FILE;
    		$stat->isLink && $type |= File::TYPE_LINK;
    		$stat->isDirectory && $type |= File::TYPE_DIRECTORY;
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
        $stat = $this->fs->ftpStat($this);

        return $stat && $stat->isLink ? $stat->target : false;
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
        return $this->getModifyTime();
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
        return $this->getModifyTime();
    }

    /**
     * Return the time that the file denoted by this pathname was las modified.
     *
     * @return int
     */
    public function getModifyTime()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat->modified : false;
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

    /**
     * Get the size of the file denoted by this pathname.
     *
     * @return int
     */
    public function getSize()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat->size : false;
    }

    /**
     * Get the owner of the file denoted by this pathname.
     *
     * @return string|int
     */
    public function getOwner()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat->user : false;
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

        return $stat ? $stat->group : false;
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

        return $stat ? $stat->mode : false;
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

        return $stat ? $stat->mode & 0444 : false;
    }

    /**
     * Test whether this pathname is writeable.
     *
     * @return bool
     */
    public function isWritable()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat->mode & 0222 : false;
    }

    /**
     * Test whether this pathname is executeable.
     *
     * @return bool
     */
    public function isExecutable()
    {
        $stat = $this->fs->ftpStat($this);

        return $stat ? $stat->mode & 0111 : false;
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
    public function delete($recursive = false, $force = false)
    {
        $stat = $this->fs->ftpStat($this);

        if ($stat->isDirectory) {
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
            return $this->fs->ftpDelete($this);
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

            return $this->fs->ftpDelete($this);
        }
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
        if ($destination instanceof FTPFile && $destination->getFilesystem() == $this->getFilesystem()) {
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
    public function createDirectory($parents = false)
    {
        $stat = $this->fs->ftpStat($this);

        if (!$stat) {
            $parent = $this->getParent();

            if ($parent) {
                if ($parents) {
                    if (!$parent->createDirectory(true)) {
                        return false;
                    }
                }
                else {
                    return false;
                }
            }

            return $this->fs->ftpMkdir($this);
        }
        
        return $stat->isDirectory;
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

        $stream = fopen('php://memory', 'w+');

        // write empty string to initialize the stream,
        // otherwise something unexpected may happen
        fwrite($stream, '');

        return $this->fs->ftpStreamPut($this, $stream);
    }

    /**
     * Get contents of the file. Returns <em>null</em> if file does not exists
     * and <em>false</em> on error (e.a. if file is a directory).
     *
     * @return string|null|bool
     */
    public function getContents()
    {
        $stat = $this->fs->ftpStat($this);

        if ($stat) {
            $tempFS = FS::getSystemTemporaryFilesystem();
            $tempFile = $tempFS->createTempFile('ftp_');

            if ($this->fs->ftpGet($this, $tempFile)) {
                return $tempFile->getContents();
            }

            return false;
        }

        return null;
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
        $stat = $this->fs->ftpStat($this);

        if (!$stat or !$stat->isDirectory) {
            $tempFS = FS::getSystemTemporaryFilesystem();
            $tempFile = $tempFS->createTempFile('ftp_');
            $tempFile->setContents($content);

            return $this->fs->ftpPut($this, $tempFile);
        }

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
    public function open($mode = 'rb')
    {
    	$cfg = $this->fs->getConfig();
        $url = $cfg->toURL(false, true) . $this->pathname;

        $stream_options = array($cfg->getProtocol() => array('overwrite' => true)); 
        $stream_context = stream_context_create($stream_options);

        $fp = fopen($url, $mode, null, $stream_context);
        
        if(!$fp) {
        	throw new Exception('FTP connection error'); // TODO
        }
        
        stream_set_timeout($fp,
        	$cfg->getTimeoutSeconds(),
        	$cfg->getTimeoutMilliseconds()
        );
        
        return $fp;
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
    public function getSHA1($raw = false)
    {
        return sha1($this->getContents(), $raw);
    }

    /**
     * List all files.
     *
     * @return array<File>
     */
    public function ls()
    {
        list($recursive, $bitmask, $globs, $callables, $globSearchPatterns) = Util::buildFilters($this, func_get_args());

        $pathname = $this->getPathname();

        $files = array();

        $currentStats = $this->fs->ftpList($this);

        foreach ($currentStats as $stat) {
            $file = new FTPFile($pathname . '/' . $stat->name, $this->fs);

            $files[] = $file;

            if ($recursive &&
                basename($stat->name) != '.' &&
                basename($stat->name) != '..' &&
                $stat->isDirectory ||
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
        return $this->fs->getConfig()->toURL() . $this->pathname;
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
