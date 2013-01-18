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

use Filicious\Internals\Adapter;

/**
 * An object that hold the absolute and the adapter local pathname.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
final class Pathname
{
	/**
	 * The root adapter
	 *
	 * @var RootAdapter
	 */
	protected $adapter;

	/**
	 * The filesystem full abstracted pathname.
	 *
	 * @var string
	 */
	protected $full;

	/**
	 * The local adapter.
	 *
	 * @var Adapter
	 */
	protected $localAdapter;

	/**
	 * The adapter local pathname.
	 *
	 * @var string
	 */
	protected $local;

	/**
	 * @param Adapter $adapter The root adapter
	 * @param string $full  The full abstracted pathname
	 */
	public function __construct(RootAdapter $adapter, $full)
	{
		$this->adapter = $adapter;
		$this->full  = Util::normalizePath('/' . $full);
		$this->localAdapter = null;
		$this->local = null;
	}

	/**
	 * @return RootAdapter
	 */
	public function rootAdapter() {
		return $this->adapter;
	}

	/**
	 * @return string
	 */
	public function full() {
		return $this->full;
	}

	/**
	 * @return Adapter
	 */
	public function localAdapter() {
		if ($this->localAdapter === null) {
			$this->adapter->resolveLocal(
				$this,
				$this->localAdapter,
				$this->local
			);
		}

		return $this->localAdapter;
	}

	/**
	 * @return string
	 */
	public function local() {
		if ($this->local === null) {
			$this->adapter->resolveLocal(
				$this,
				$this->localAdapter,
				$this->local
			);
		}

		return $this->local;
	}

	/**
	 * @return string
	 */
	public function basename() {
		return basename($this->full);
	}

	/**
	 * Resolve the parent pathname.
	 *
	 * @return Pathname
	 */
	public function parent() {
		return new Pathname($this->adapter, dirname($this->full()));
	}

	/**
	 * Resolve the child pathname.
	 *
	 * @param string|Pathname $basename
	 *
	 * @return Pathname
	 */
	public function child($basename) {
		if ($basename instanceof Pathname) {
			$basename = $basename->basename();
		}
		return new Pathname(
			$this->adapter,
			$this->full() . '/' . $basename
		);
	}

	function __toString()
	{
		return $this->full();
	}
}
