<?php

namespace bit3\filesystem;

use SplFileInfo;

interface Filesystem
{
    /**
     * Get the root (/) file node.
     *
     * @return File
     */
    public function getRoot();

    /**
     * Get a file object for the specific file.
     *
     * @param string $path
     *
     * @return File
     */
    public function getFile($path);

    /**
     * Returns available space on filesystem or disk partition.
     *
     * @param File $path
     *
     * @return int
     */
    public function diskFreeSpace(File $path = null);

    /**
     * Returns the total size of a filesystem or disk partition.
     *
     * @param File $path
     *
     * @return int
     */
    public function diskTotalSpace(File $path = null);
}