<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem;

use ArrayIterator;
use Bit3\Filesystem\Filesystem;
use Bit3\Filesystem\File;
use Exception;
use Traversable;

/**
 * A file object
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
abstract class AbstractFile
	implements File
{
    /**
     * @var Filesystem
     */
    protected $fs;

    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    /**
     * Get the underlaying filesystem for this pathname.
     *
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->fs;
    }
    
    /* (non-PHPdoc)
     * @see Bit3\Filesystem.File::isFile()
     */
    public function isFile() {
    	return (bool) ($this->getType() & File::TYPE_FILE);
    }
    
    /* (non-PHPdoc)
     * @see Bit3\Filesystem.File::isLink()
     */
    public function isLink() {
    	return (bool) ($this->getType() & File::TYPE_LINK);
    }
    
    /* (non-PHPdoc)
     * @see Bit3\Filesystem.File::isDirectory()
     */
    public function isDirectory() {
    	return (bool) ($this->getType() & File::TYPE_DIRECTORY);
    }

    /**
     * Get the name of the file or directory.
     *
     * @return string
     */
    public function getBasename($suffix = '')
    {
        return basename($this->getPathname(), $suffix);
    }

    /**
     * Get the extension of the file.
     *
     * @return mixed
     */
    public function getExtension()
    {
        $basename = $this->getBasename();
        $pos = strrpos($basename, '.');

        if ($pos !== false) {
            return substr($basename, $pos+1);
        }

        return null;
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
        $finfo = FS::getFileInfo();

        return finfo_file($finfo, $this->getRealUrl(), FILEINFO_NONE);
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
        $finfo = FS::getFileInfo();

        return finfo_file($finfo, $this->getRealUrl(), FILEINFO_MIME_TYPE);
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
        $finfo = FS::getFileInfo();

        return finfo_file($finfo, $this->getRealUrl(), FILEINFO_MIME_ENCODING);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
    	$args = func_get_args();
        return new ArrayIterator(call_user_func_array(array($this, 'ls'), $args));
    }
    
    public function count() {
    	$args = func_get_args();
    	return count(call_user_func_array(array($this, 'ls'), $args));
    }

    public function __toString()
    {
        return $this->pathname;
    }
}
