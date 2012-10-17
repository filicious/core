<?php

namespace bit3\filesystem\local;

use bit3\filesystem\Filesystem;
use bit3\filesystem\File;
use bit3\filesystem\FilesystemException;
use bit3\filesystem\Util;

class LocalFilesystem implements Filesystem
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @param string $basePath
     */
    public function __construct($basePath = '/')
    {
        $this->basePath = Util::normalizePath($basePath) . '/';
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
}