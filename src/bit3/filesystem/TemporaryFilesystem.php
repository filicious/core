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


/**
 * A temporary filesystem allow creation of temporary files,
 * that will be deleted when the filesystem object get destroyed.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
interface TemporaryFilesystem extends Filesystem
{
    /**
     * Create a temporary file and return the file object.
     *
     * @param string $prefix
     *
     * @return File
     */
    public function createTempFile($prefix);

    /**
     * Create a temporary directory and return the file object.
     *
     * @param string $prefix
     *
     * @return File
     */
    public function createTempDir($prefix);
}