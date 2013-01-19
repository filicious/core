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
use Filicious\FilesystemConfig;
use Filicious\Internals\Pathname;
use Filicious\Exception\FileNotFoundException;
use Filicious\Exception\NotAFileException;
use Filicious\Exception\NotADirectoryException;

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
	/**
	 * @var FilesystemConfig
	 */
	protected $config;

	protected $fs;

	protected $root;

	protected $parent;

	/**
	 * @see Filicious\Internals\Adapter::getConfig()
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * @see Filicious\Internals\Adapter::setFilesystem()
	 */
	public function setFilesystem(Filesystem $fs)
	{
		$this->fs = $fs;
		return $this;
	}

	/**
	 * @see Filicious\Internals\Adapter::getFilesystem()
	 */
	public function getFilesystem()
	{
		return $this->fs;
	}

	/**
	 * @see Filicious\Internals\Adapter::setRootAdapter()
	 */
	public function setRootAdapter(Adapter $root)
	{
		$this->root = $root;
		return $this;
	}

	/**
	 * @see Filicious\Internals\Adapter::getRootAdapter()
	 */
	public function getRootAdapter()
	{
		return $this->root;
	}

	/**
	 * @see Filicious\Internals\Adapter::setParentAdapter()
	 */
	public function setParentAdapter(Adapter $parent)
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 * @see Filicious\Internals\Adapter::getParentAdapter()
	 */
	public function getParentAdapter()
	{
		return $this->parent;
	}

	/**
	 * @see Filicious\Internals\Adapter::resolveLocal()
	 */
	public function resolveLocal(Pathname $pathname, &$localAdapter, &$local)
	{
		$localAdapter = $this;
		$local = $pathname->full();
	}

	/**
	 * @see Filicious\Internals\Adapter::getMD5()
	 */
	public function getMD5(Pathname $pathname, $binary)
	{
		return md5($this->getContents($pathname), $binary);
	}

	/**
	 * @see Filicious\Internals\Adapter::getSHA1()
	 */
	public function getSHA1(Pathname $pathname, $binary)
	{
		return sha1($this->getContents($pathname), $binary);
	}

	/**
	 * @see Filicious\Internals\Adapter::count()
	 */
	public function count(Pathname $pathname, array $filter)
	{
		$i = 0;
		foreach ($this->getIterator($pathname, $filter) as $pathname) {
			$i++;
		}
		return $i;
	}

	/**
	 * @see Filicious\Internals\Adapter::getIterator()
	 */
	public function getIterator(Pathname $pathname, array $filter)
	{
		return new PathnameIterator($pathname, 0, $filter);
	}

	/**
	 * @see Filicious\Internals\Adapter::requireExists()
	 */
	public function requireExists(Pathname $pathname)
	{
		if (!$this->exists($pathname)) {
			throw new FileNotFoundException($pathname);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::checkFile()
	 */
	public function checkFile(Pathname $pathname)
	{
		$this->requireExists($pathname);
		if (!$this->isFile($pathname)) {
			throw new NotAFileException($pathname);
		}
	}

	/**
	 * @see Filicious\Internals\Adapter::checkDirectory()
	 */
	public function checkDirectory(Pathname $pathname)
	{
		$this->requireExists($pathname);
		if (!$this->isDirectory($pathname)) {
			throw new NotADirectoryException($pathname);
		}
	}
}
