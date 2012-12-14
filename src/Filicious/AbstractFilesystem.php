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

use \ReflectionClass;
use \Exception;

/**
 * Skeleton for Filesystem implementors.
 *
 * @package filicious-core
 * @author  Oliver Hoff <oliver@hofff.com>
 */
abstract class AbstractFilesystem
	implements Filesystem
{
	/**
	 * @var string The name of the config class used by instances of this
	 *         filesystem implementation. Override in concrete classes to specify
	 *         another config class.
	 */
	const CONFIG_CLASS = 'FilesystemConfig';
	
	/* (non-PHPdoc)
	 * @see Filicious.Filesystem::create()
	*/
	public static function create(FilesystemConfig $config, PublicURLProvider $provider = null)
	{
		// the instanceof operator has lexer issues...
		if (!is_a($config, static::CONFIG_CLASS)) {
			throw new FilesystemException(sprintf(
				'%s requires a config of type %s, given %s',
				get_called_class(),
				static::CONFIG_CLASS,
				get_class($config)
			));
		}

		$args  = func_get_args();
		$clazz = new \ReflectionClass(get_called_class());
		return $clazz->newInstanceArgs($args);
	}

	/**
	 * @var FilesystemConfig
	 */
	protected $config;
	
	/**
	 * @var PublicURLProvider
	 */
	protected $provider;
	
	/**
	 * @param FilesystemConfig $config
	 */
	protected function __construct(FilesystemConfig $config, PublicURLProvider $provider = null)
	{
		$this->config   = clone $config;
		$this->provider = $provider;
		$this->prepareConfig();
		$this->config->makeImmutable();
	}

	/* (non-PHPdoc)
	 * @see Filicious.Filesystem::getConfig()
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Gets called before at construction time before the config is made
	 * immutable. Override in concrete classes to extend or alter behavior.
	 */
	protected function prepareConfig()
	{
		$this->config->setBasePath(Util::normalizePath($this->config->getBasePath()) . '/');
	}

	/* (non-PHPdoc)
	 * @see Filicious.FilesystemConfig::getPublicURLProvider()
	 */
	public function getPublicURLProvider()
	{
		return $this->provider;
	}

	/* (non-PHPdoc)
	 * @see Filicious.FilesystemConfig::setPublicURLProvider()
	 */
	public function setPublicURLProvider(PublicURLProvider $provider = null)
	{
		$this->provider = $provider;
	}
}
