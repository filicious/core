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

namespace Filicious\Plugin\DiskSpace;

use Filicious\Internals\Pathname;

interface DiskSpaceAwareAdapterInterface
{
	/**
	 * Checks if the file is a (symbolic) space.
	 *
	 * @return float|null
	 */
	public function getTotalSpace(Pathname $pathname);

	/**
	 * Receive the space target from symbolic spaces.
	 *
	 * @return float|null
	 */
	public function getFreeSpace(Pathname $pathname);
}
