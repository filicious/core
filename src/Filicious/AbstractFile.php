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

namespace Filicious;

use ArrayIterator;
use Filicious\Filesystem;
use Filicious\File;
use Exception;
use Traversable;

/**
 * A file object
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
abstract class AbstractFile
	implements File
{
	/**
	 * @var Filesystem
	 */
	protected $fs;

	/**
	 * @var string
	 */
	protected $pathname;

	public function __construct($pathname, SimpleFilesystem $fs)
	{
		$this->pathname = Util::normalizePath('/' . $pathname);

		$this->fs = $fs;
	}

	/**
	 * Get the underlaying filesystem for this pathname.
	 *
	 * @return Filesystem
	 */
	public function getFilesystem()
	{
		return $this->fs;
	}

	/**
	 * Returns the absolute pathname.
	 *
	 * @return string
	 */
	public function getPathname()
	{
		return $this->pathname;
	}

	/* (non-PHPdoc)
	 * @see Filicious.File::isFile()
	 */
	public function isFile()
	{
		return (bool) ($this->getType() & File::TYPE_FILE);
	}

	/* (non-PHPdoc)
	 * @see Filicious.File::isLink()
	 */
	public function isLink()
	{
		return (bool) ($this->getType() & File::TYPE_LINK);
	}

	/* (non-PHPdoc)
	 * @see Filicious.File::isDirectory()
	 */
	public function isDirectory()
	{
		return (bool) ($this->getType() & File::TYPE_DIRECTORY);
	}

	/**
	 * Get the name of the file or directory.
	 *
	 * @return string
	 */
	public function getBasename($suffix = '')
	{
		return basename($this->getPathname(), $suffix);
	}

	/**
	 * Get the extension of the file.
	 *
	 * @return mixed
	 */
	public function getExtension()
	{
		$basename = $this->getBasename();
		$pos      = strrpos($basename, '.');

		if ($pos !== false) {
			return substr($basename, $pos + 1);
		}

		return null;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 */
	public function getIterator()
	{
		$args = func_get_args();
		return new ArrayIterator(call_user_func_array(array($this, 'ls'), $args));
	}

	public function count()
	{
		$args = func_get_args();
		return count(call_user_func_array(array($this, 'ls'), $args));
	}

	public function __toString()
	{
		return $this->getPathname();
	}
}
