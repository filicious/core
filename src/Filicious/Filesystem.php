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
	 * Get the config used by this filesystem.
	 * 
	 * @return FilesystemConfig The config of this filesystem
	 */
	public function getConfig();
	
	/**
	 * Notify this filesystem that the given param should change to the given
	 * value.
	 * 
	 * <strong>THIS METHOD SHOULD ONLY BE CALLED FROM WITHIN
	 * A <em>FilesystemConfig</em>!</strong>
	 * 
	 * @param array $data The internal key value storage of the config object
	 * @param string $param The parameter to change
	 * @param mixed $value The new value to set
	 * @return void
	 * @throws ConfigurationException
	 */
	public function notify(array &$data, $param, $value);

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
