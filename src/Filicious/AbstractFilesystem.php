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
use Filicious\Stream\StreamManager;

/**
 * Skeleton for Filesystem implementors.
 *
 * @package filicious-core
 * @author  Oliver Hoff <oliver@hofff.com>
 */
abstract class AbstractFilesystem
	implements Filesystem
{
	/* (non-PHPdoc)
	 * @see Filicious.Filesystem::newConfig()
	 */
	public static function newConfig(\Traversable $data = null) {
		$clazz = new \ReflectionClass(get_called_class());
		if(!$clazz->isInstantiable()) {
			throw new LogicException(); // TODO
		}
		return FilesystemConfig::newConfig($data)->setImplementation($clazz->getName());
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
	 * @var PublicURLProvider
	 */
	protected $provider;

	/**
	 * @param FilesystemConfig $config
	 */
	protected function __construct(FilesystemConfig $config, PublicURLProvider $provider = null)
	{
		$this->config = $config = $config->fork();
		$this->provider = $provider;
		$this->prepareConfig();
		$this->config = null; // because config binding asserts fs->getConfig is null
		$this->config = $config->bind($this);
	}

	function __destruct()
	{
		$url = array_merge(
			array(
			     'scheme' => '',
			     'host'   => '',
			     'port'   => '',
			),
			parse_url($this->config->getStreamURI())
		);

		$scheme = $url['scheme'];
		$host   = $url['host'] . ($url['port'] ? ':' . $url['port'] : '');

		StreamManager::unregister($host, $scheme, true);
	}

	/* (non-PHPdoc)
	 * @see Filicious.Filesystem::getConfig()
	 */
	public function getConfig()
	{
		return $this->config;
	}
	
	public function notify(array &$data, $param, $value)
	{
		throw new Exception();//InvalidStateException(); // TODO
	}

	/**
	 * Gets called before at construction time before the config is made
	 * immutable. Override in concrete classes to extend or alter behavior.
	 */
	protected function prepareConfig()
	{
		$this->config->setBasePath(Util::normalizePath($this->config->getBasePath()));
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
