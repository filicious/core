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

/**
 * Local filesystem adapter.
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 */
class RootAdapter extends AbstractDelegatorAdapter
{

	protected $streamScheme;

	protected $streamHost;

	/**
	 * @param string $basepath
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

	/**
	 * {@inheritdoc}
	 */
	protected function selectDelegate(Pathname $pathname = null)
	{
		return $this->delegate;
	}
}
