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

namespace Filicious\Test;

/**
 * A test adapter allow direct access to a filesystem.
 * This adapter is an alternative implementation to a filesystem.
 */
interface SingleFilesystemTestEnvironment
{
	/**
	 * @return TestAdapter
	 */
	public function getAdapter();

	/**
	 * @return \Filicious\Filesystem
	 */
	public function getFilesystem();

	/**
	 * Cleanup environment.
	 */
	public function cleanup();
}
