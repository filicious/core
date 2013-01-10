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


/**
 * An object that hold the absolute and the adapter local pathname.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
final class Pathname
{
	/**
	 * The filesystem full abstracted pathname.
	 *
	 * @var string
	 */
	protected $full;

	/**
	 * The adapter local pathname.
	 *
	 * @var string
	 */
	protected $local;

	/**
	 * @param string $full  The full abstracted pathname
	 * @param string $local The adapter local path
	 */
	public function __construct($full, $local)
	{
		$this->full  = $full;
		$this->local = $local;
	}

	/**
	 * @return string
	 */
	public function full() {
		return $this->full;
	}

	/**
	 * @return string
	 */
	public function local() {
		return $this->local;
	}

	/**
	 * @return string
	 */
	public function basename() {
		return basename($this->full);
	}

	/**
	 * @param string|Pathname $basename
	 *
	 * @return Pathname
	 */
	public function child($basename) {
		if ($basename instanceof Pathname) {
			$basename = $basename->basename();
		}
		return new Pathname(
			$this->full() . '/' . $basename,
			$this->local() . '/' . $basename
		);
	}
}
