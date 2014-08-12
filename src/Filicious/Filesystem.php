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

use Filicious\Internals\Adapter;
use Filicious\Internals\RootAdapter;
use Filicious\Internals\Pathname;
use Filicious\Internals\Util;

/**
 * Virtual filesystem structure.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Filesystem
{
	protected $adapter;

	/**
	 * @param Adapter $adapter
	 */
	public function __construct(Adapter $adapter)
	{
		$this->adapter = new RootAdapter($this);
		$this->adapter->setDelegate($adapter);
	}
	}

	/**
	 * Get the root (/) file node.
	 *
	 * @return File
	 */
	public function getRoot()
	{
		return $this->getFile(); // same as ->getFile('/') and ->getFile('')
	}

	/**
	 * Get a file object for the specific file.
	 *
	 * @param string $path
	 *
	 * @return File
	 */
	public function getFile($path = null)
	{
		// cheap recreate of File object
		if ($path instanceof Pathname && $path->rootAdapter() == $this->adapter) {
			return new File($this, $path);
		}

		$pathname = implode('/', Util::getPathnameParts($path));
		strlen($pathname) && $pathname = '/' . $pathname;
		return new File($this, new Pathname($this->adapter, $pathname));
	}
}
