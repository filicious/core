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

use Bit3\Filesystem\Temp\LocalTemporaryFilesystem;

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
	 * @param \Bit3\Filesystem\Filesystem|null $systemTemporaryFilesystem
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

	/**
	 * @var resource
	 */
	protected static $finfo = null;

	/**
	 * Get the FileInfo resource identifier.
	 *
	 * @return resource
	 */
	public static function getFileInfo()
	{
		if (static::$finfo === null) {
			static::$finfo = finfo_open();
		}

		return static::$finfo;
	}
}
