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

use Filicious\Filesystem;
use Filicious\Internals\FileNotFoundException;
use Filicious\Internals\NotAFileException;
use Filicious\Internals\NotADirectoryException;

/**
 * A mount aggregator can mount adapters to various paths.
 * Multiple adapters can be mounted to the same path, but only the last mounted
 * adapter can be seen.
 *
 * @package filicious-core
 * @author  Oliver Hoff <oliver@hofff.com>
 */
abstract class AbstractAdapter
	implements Adapter
{

	protected $fs;

	protected $root;

	protected $parent;

	protected function __construct(Filesystem $fs, Adapter $root, Adapter $parent)
	{
		$this->fs     = $fs;
		$this->root   = $root;
		$this->parent = $parent;
	}

	/**
	 * @see Filicious\Internals\Adapter::getFilesystem()
	 */
	public function getFilesystem()
	{
		return $this->fs;
	}

	/**
	 * @see Filicious\Internals\Adapter::getRootAdapter()
	 */
	public function getRootAdapter()
	{
		return $this->root;
	}

	/**
	 * @see Filicious\Internals\Adapter::getParentAdapter()
	 */
	public function getParentAdapter()
	{
		return $this->parent;
	}

	/**
	 * @see Filicious\Internals\Adapter::getMD5()
	 */
	public function getMD5($pathname, $local, $binary)
	{
		return md5($this->getContents($pathname, $local), $binary);
	}

	/**
	 * @see Filicious\Internals\Adapter::getSHA1()
	 */
	public function getSHA1($pathname, $local, $binary)
	{
		return sha1($this->getContents($pathname, $local), $binary);
	}

	/**
	 * @see Filicious\Internals\Adapter::count()
	 */
	public function count($pathname, $local, array $filter)
	{
		$i = 0;
		foreach ($this->getIterator($pathname, $local, $filter) as $pathname) {
			$i++;
		}
		return $i;
	}

	/**
	 * @see Filicious\Internals\Adapter::getIterator()
	 */
	public function getIterator($pathname, $local, array $filter)
	{
		return new PathnameIterator($this->fs, $this->root, $this, $pathname, $local, $filter);
	}

	/**
	 * @see Filicious\Internals\Adapter::requireExists()
	 */
	public function requireExists($pathname, $local)
	{
		if (!$this->exists($pathname, $local)) {
			throw new FileNotFoundException($pathname, $local);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::checkFile()
	 */
	public function checkFile($pathname, $local)
	{
		if (!$this->isFile($pathname, $local)) {
			throw new NotAFileException($pathname, $local);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::checkDirectory()
	 */
	public function checkDirectory($pathname, $local)
	{
		if (!$this->isDirectory($pathname, $local)) {
			throw new NotADirectoryException($pathname, $local);
		}
	}

}
