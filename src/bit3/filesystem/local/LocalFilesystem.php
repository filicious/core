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
use bit3\filesystem\PublicUrlProvider;
use bit3\filesystem\Util;

/**
 * Local filesystem adapter.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class LocalFilesystem
    implements Filesystem
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var PublicUrlProvider
     */
    protected $publicUrlProvider;

    /**
     * @param string $basePath
     */
    public function __construct($basePath = '/', PublicUrlProvider $publicUrlProvider = null)
    {
        $this->basePath = Util::normalizePath('/' . $basePath) . '/';
        $this->publicUrlProvider = $publicUrlProvider;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
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
    public function diskFreeSpace(File $path = null)
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
    public function diskTotalSpace(File $path = null)
    {
        if (!$path) {
            $path = $this->getRoot();
        }

        return disk_total_space($path->getRealPath());
    }

    /**
     * @return PublicUrlProvider
     */
    public function getPublicUrlProvider()
    {
        return $this->publicUrlProvider;
    }
}