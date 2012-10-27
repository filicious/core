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

use bit3\filesystem\temp\LocalTemporaryFilesystem;

/**
 * Class FS
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class FS
{
    /**
     * @var string|null
     */
    protected static $systemTemporaryDirectory = null;

    /**
     * Get the default temporary directory.
     *
     * @return string
     */
    public static function getSystemTemporaryDirectory()
    {
        return static::$systemTemporaryDirectory !== null
            ? static::$systemTemporaryDirectory
            : sys_get_temp_dir();
    }

    /**
     * Set the default temporary directory.
     * Warning: Changing this pass will only new initialized TemporaryFilesystem's!
     *
     * @param string $tempPath
     */
    public static function setSystemTemporaryDirectory($tempPath)
    {
        static::$systemTemporaryDirectory = (string) $tempPath;
    }

    /**
     * @var TemporaryFilesystem|null
     */
    protected static $systemTemporaryFilesystem = null;

    /**
     * @param \bit3\filesystem\Filesystem|null $systemTemporaryFilesystem
     */
    public static function setSystemTemporaryFilesystem(TemporaryFilesystem $systemTemporaryFilesystem)
    {
        self::$systemTemporaryFilesystem = $systemTemporaryFilesystem;
    }

    /**
     * @return TemporaryFilesystem
     */
    public static function getSystemTemporaryFilesystem()
    {
        if (static::$systemTemporaryFilesystem === null) {
            static::$systemTemporaryFilesystem = new LocalTemporaryFilesystem(static::getSystemTemporaryDirectory());
        }

        return static::$systemTemporaryFilesystem;
    }
}
