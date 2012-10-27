<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace bit3\filesystem\temp;

use bit3\filesystem\File;
use bit3\filesystem\TemporaryFilesystem;
use bit3\filesystem\local\LocalFilesystem;
use bit3\filesystem\local\LocalFile;

/**
 * Temporary filesystem adapter.
 *
 * The temporary filesystem is a special version of a local filesystem, that can be used to handle temporary files.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class LocalTemporaryFilesystem
    extends LocalFilesystem
    implements TemporaryFilesystem
{
    /**
     * @var array
     */
    protected $temporaryFiles = array();

    public function __destruct()
    {
        /** @var File $file */
        foreach ($this->temporaryFiles as $file) {
            if ($file->exists()) {
                $file->delete(true);
            }
        }
    }

    public function createTempFile($prefix)
    {
        // create a temporary file
        $pathname = tempnam($this->getBasePath(), $prefix);

        // remove the base path from pathname
        $file = substr($pathname, strlen($this->getBasePath()));

        // create new local file object
        $file = new LocalFile($file, $this);

        // register temporary file
        $this->temporaryFiles[] = $file;

        return $file;
    }

    public function createTempDirectory($prefix)
    {
        // create a temporary file
        $file = $this->createTempFile($prefix);

        // delete the file and...
        $file->delete();

        // finally create a directory
        $file->mkdir();

        // return the local file object
        return $file;
    }
}