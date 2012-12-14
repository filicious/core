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
 * A filesystem object
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
interface Filesystem
{
	/**
	 * @param FilesystemConfig $config
	 *
	 * @return Filesystem
	 * @throws FilesystemException
	 */
	public static function create(FilesystemConfig $config, PublicURLProvider $provider = null);

	/**
	 * @return FilesystemConfig
	 */
	public function getConfig();

	/**
	 * Get the root (/) file node.
	 *
	 * @return File
	 */
	public function getRoot();

	/**
	 * Get a file object for the specific file.
	 *
	 * @param string $path
	 *
	 * @return File
	 */
	public function getFile($path);

	/**
	 * Returns available space on filesystem or disk partition.
	 *
	 * @param File $path
	 *
	 * @return int
	 */
	public function getFreeSpace(File $path = null);

	/**
	 * Returns the total size of a filesystem or disk partition.
	 *
	 * @param File $path
	 *
	 * @return int
	 */
	public function getTotalSpace(File $path = null);
}
