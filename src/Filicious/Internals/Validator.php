<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 * @link    http://filicious.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Filicious\Internals;

use Filicious\Exception\DirectoryOverwriteDirectoryException;
use Filicious\Exception\DirectoryOverwriteFileException;
use Filicious\Exception\FileNotFoundException;
use Filicious\Exception\FileOverwriteDirectoryException;
use Filicious\Exception\FileOverwriteFileException;
use Filicious\Exception\FilesystemException;
use Filicious\Exception\NotADirectoryException;
use Filicious\Exception\NotAFileException;
use Filicious\File;
use Filicious\Filesystem;
use Filicious\Stream\StreamMode;

class Validator
{
	/**
	 * Test if a pathname exists and throw an exception if not.
	 *
	 * @return void
	 *
	 * @throws FileNotFoundException
	 */
	public static function requireExists(Pathname $pathname)
	{
		if (!$pathname->localAdapter()->exists($pathname)) {
			throw new FileNotFoundException($pathname);
		}
	}

	/**
	 * Test if a pathname is a file and throw an exception if not.
	 *
	 * @return void
	 *
	 * @throws FileNotFoundException
	 * @throws NotAFileException
	 */
	public static function checkFile(Pathname $pathname)
	{
		static::requireExists($pathname);
		if (!$pathname->localAdapter()->isFile($pathname)) {
			throw new NotAFileException($pathname);
		}
	}

	/**
	 * Test if a pathname is a directory and throw an exception if not.
	 *
	 * @return void
	 *
	 * @throws FileNotFoundException
	 * @throws NotAFileException
	 */
	public static function checkDirectory(Pathname $pathname)
	{
		static::requireExists($pathname);
		if (!$pathname->localAdapter()->isDirectory($pathname)) {
			throw new NotADirectoryException($pathname);
		}
	}
}
