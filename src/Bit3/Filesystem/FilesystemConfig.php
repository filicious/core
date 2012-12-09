<?php

/**
 * High level object oriented filesystem abstraction.
 *
 * @package php-filesystem
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @link    http://bit3.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Bit3\Filesystem;


/**
 * A filesystem configuration
 *
 * @package php-filesystem
 * @author  Oliver Hoff <oliver@hofff.com>
 */
interface FilesystemConfig
	//extends \Serializable // TODO
{
	/**
	 * @param FilesystemConfig $config
	 * @return Filesystem
	 * @throws FilesystemException
	 */
	public static function create();
	
	/**
	 * Make this config immutable
	 */
	public function immutable();
	
	/**
	 * @return string
	 */
	public function getBasePath();
	
	/**
	 * @param string $basePath
	 */
	public function setBasePath($basePath);
	
	/**
	 * Normalize the base path set.
	 */
	public function normalizeBasePath();
	
	/**
	 * @return PublicURLProvider
	 */
	public function getPublicURLProvider();
	
	/**
	 * @param PublicURLProvider $provider
	 */
	public function setPublicURLProvider(PublicURLProvider $provider = null);
}