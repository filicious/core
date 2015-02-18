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

use Filicious\Exception\AdapterException;
use Filicious\Exception\InvalidArgumentException;


/**
 * A mount aggregator can mount adapters to various paths.
 * Multiple adapters can be mounted to the same path, but only the last mounted
 * adapter can be seen.
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 */
class MountAdapter extends AbstractDelegatorAdapter
{
	protected $mounts = array();

	public function mount($path, Adapter $adapter)
	{
		$path = Util::normalizePath($path);
		$path = '/' . ltrim($path, '/');

		if (empty($path)) {
			throw new InvalidArgumentException('Mount path cannot be empty');
		}
		if (isset($this->mounts[$path])) {
			throw new InvalidArgumentException('Could not mount over the already mounted path ' . $path);
		}

		$this->mounts[$path] = $adapter;
	}

	public function unmount($path)
	{
		$path = Util::normalizePath($path);

		if (empty($path)) {
			throw new InvalidArgumentException('Mount path cannot be empty');
		}

		unset($this->mounts[$path]);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function selectDelegate(Pathname $pathname = null)
	{
		$path = $pathname->full();

		do {
			if (isset($this->mounts[$path])) {
				return $this->mounts[$path];
			}
		}
		while ('/' !== $path = Util::dirname($path));

		throw new AdapterException('No mount found for ' . $pathname->full());
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolveLocal(Pathname $pathname, &$localAdapter, &$local)
	{
		$path = $pathname->full();

		do {
			if (isset($this->mounts[$path])) {
				$localAdapter = $this->mounts[$path];
				$local        = substr($pathname->full(), strlen($path));
				return $this;
			}
		}
		while ('/' !== $path = Util::dirname($path));

		throw new AdapterException('No mount found for ' . $pathname->full());
	}

}
