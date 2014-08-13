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

use Filicious\Exception\InvalidArgumentException;
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

	/**
	 * @var Adapter
	 */
	protected $delegate;

	public function __construct(Filesystem $filesystem)
	{
		$this->filesystem = $filesystem;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setFilesystem(Filesystem $filesystem = null)
	{
		throw new InvalidArgumentException('You cannot overwrite the filesystem of the root adapter');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRootAdapter()
	{
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParentAdapter()
	{
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setParentAdapter(Adapter $parent = null)
	{
		throw new InvalidArgumentException('You cannot overwrite parent adapter of the root adapter');
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
