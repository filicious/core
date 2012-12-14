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


/**
 * A temporary filesystem allow creation of temporary files,
 * that will be deleted when the filesystem object get destroyed.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
interface TemporaryFilesystem extends Filesystem
{
	/**
	 * Create a temporary file and return the file object.
	 *
	 * @param string $prefix
	 *
	 * @return File
	 */
	public function createTempFile($prefix);

	/**
	 * Create a temporary directory and return the file object.
	 *
	 * @param string $prefix
	 *
	 * @return File
	 */
	public function createTempDirectory($prefix);
}