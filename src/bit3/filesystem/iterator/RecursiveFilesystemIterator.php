<?php

namespace bit3\filesystem\iterator;

use SplStack;
use RecursiveIterator;
use bit3\filesystem\File;

class RecursiveFilesystemIterator extends FilesystemIterator implements RecursiveIterator
{
    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Returns if an iterator can be created for the current entry.
     * @link http://php.net/manual/en/recursiveiterator.haschildren.php
     * @return bool true if the current entry can be iterated over, otherwise returns false.
     */
    public function hasChildren()
    {
        if ($this->valid() && $this->files[$this->keys[$this->index]]->isDir()) {
            return (bool) count($this->files[$this->keys[$this->index]]->listAll());
        }
        return false;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Returns an iterator for the current entry.
     * @link http://php.net/manual/en/recursiveiterator.getchildren.php
     * @return RecursiveIterator An iterator for the current entry.
     */
    public function getChildren()
    {
        return new RecursiveFilesystemIterator($this->files[$this->keys[$this->index]], $this->flags);
    }
}
