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

namespace Filicious\Plugin\Hash;

use Filicious\Internals\Pathname;

interface HashAwareAdapterInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getHash(Pathname $pathname, $algorithm, $binary);
}
