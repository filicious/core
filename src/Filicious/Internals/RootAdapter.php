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

use Filicious\File;
use Filicious\Filesystem;
use Filicious\FilesystemConfig;
use Filicious\Internals\Adapter;

/**
 * Local filesystem adapter.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class RootAdapter
	extends DelegatorAdapter
{
	/**
	 * @param string|FilesystemConfig $basepath
	 */
	public function __construct(Filesystem $fs)
	{
		$this->fs = $fs;
	}

	/**
	 * @param \Filicious\Internals\Adapter $delegate
	 */
	public function setDelegate($delegate)
	{
		$this->delegate = $delegate;
		return $this;
	}

	/**
	 * @return \Filicious\Internals\Adapter
	 */
	public function getDelegate()
	{
		return $this->delegate;
	}

	public function notifyConfigChange()
	{

		return parent::notifyConfigChange();
	}
}
