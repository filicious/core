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
 * A filesystem configuration
 *
 * @package filicious-core
 * @author  Oliver Hoff <oliver@hofff.com>
 */
interface FilesystemConfig
	//extends \Serializable // TODO
{
	/**
	 * @param FilesystemConfig $config
	 *
	 * @return Filesystem
	 * @throws FilesystemException
	 */
	public static function create();

	/**
	 * Make this config immutable
	 */
	public function makeImmutable();

	/**
	 * @return string
	 */
	public function getBasePath();

	/**
	 * @param string $basePath
	 */
	public function setBasePath($basePath);
}