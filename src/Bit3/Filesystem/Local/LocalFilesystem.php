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

use Bit3\Filesystem\AbstractFilesystem;
use Bit3\Filesystem\File;
use Bit3\Filesystem\PublicURLProvider;
use Bit3\Filesystem\Util;

/**
 * Local filesystem adapter.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class LocalFilesystem
    extends AbstractFilesystem
{
	const CONFIG_CLASS = 'Bit3\Filesystem\Local\LocalFilesystemConfig';

    public function __construct(LocalFilesystemConfig $config, PublicURLProvider $provider = null)
    {
    	parent::__construct($config, $provider);
    }

    /**
     * Get the root (/) file node.
     *
     * @return File
     */
    public function getRoot()
    {
        return new LocalFile('/', $this);
    }

    /**
     * Get a file object for the specific file.
     *
     * @param string $path
     *
     * @return File
     */
    public function getFile($path)
    {
        return new LocalFile($path, $this);
    }

    /**
     * Returns available space on filesystem or disk partition.
     *
     * @param File $path
     *
     * @return int
     */
    public function getFreeSpace(File $path = null)
    {
        if (!$path) {
            $path = $this->getRoot();
        }

        return disk_free_space($path->getRealPath());
    }

    /**
     * Returns the total size of a filesystem or disk partition.
     *
     * @param File $path
     *
     * @return int
     */
    public function getTotalSpace(File $path = null)
    {
        if (!$path) {
            $path = $this->getRoot();
        }

        return disk_total_space($path->getRealPath());
    }
}
