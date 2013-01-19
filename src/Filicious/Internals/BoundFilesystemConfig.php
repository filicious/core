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

namespace Filicious\Internals;

use Filicious\FilesystemConfig;
use Filicious\Exception\ConfigurationException;
use Filicious\Exception\ImmutableConfigException;
use Filicious\Internals\Adapter;

/**
 * An object that hold the absolute and the adapter local pathname.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
final class BoundFilesystemConfig
	extends FilesystemConfig
{
	/**
	 * @var FilesystemConfig
	 */
	protected $parentConfig = null;

	/**
	 * @var array
	 */
	protected $linkedConfigs = array();

	/**
	 * The adapter this config is bound to
	 *
	 * @var Adapter
	 */
	protected $adapter;

	/**
	 * @var boolean
	 */
	protected $opened = true;

	/**
	 * Previous data, before opening.
	 *
	 * @var array
	 */
	protected $previous = null;

	/**
	 * @param Adapter $adapter The root adapter
	 * @param string $full  The full abstracted pathname
	 */
	public function __construct(Adapter $adapter, $data = null)
	{
		$this->adapter = $adapter;
		parent::merge($data);
		$this->opened = false;
	}

	/**
	 * @param \Filicious\FilesystemConfig $parentConfig
	 */
	public function setParentConfig($parentConfig)
	{
		if ($this->parentConfig !== null && $parentConfig !== null) {
			throw new ConfigurationException('This configuration is already linked!'); // TODO
		}
		$this->parentConfig = $parentConfig;
		return $this;
	}

	/**
	 * @return \Filicious\FilesystemConfig
	 */
	public function getParentConfig()
	{
		return $this->parentConfig;
	}

	/**
	 * @param                             $path
	 * @param \Filicious\FilesystemConfig $config
	 */
	public function linkConfig($path, BoundFilesystemConfig $config)
	{
		$config->setParentConfig($this);
		$this->linkedConfigs[$path] = $config;
		$this->merge($config, $path);
	}

	/**
	 * @param BoundFilesystemConfig $config
	 *
	 * @return bool
	 */
	public function unlinkConfig(BoundFilesystemConfig $config)
	{
		/** @var BoundFilesystemConfig $linkedConfig */
		foreach ($this->linkedConfigs as $path => $linkedConfig) {
			if ($linkedConfig == $config) {
				$linkedConfig->setParentConfig(null);
				unset($this->linkedConfigs[$path]);
				return true;
			}
		}
		return false;
	}

	/**
	 * @return array
	 */
	public function getLinkedConfigs()
	{
		return $this->linkedConfigs;
	}

	public function isMutable()
	{
		return $this->opened;
	}

	public function isImmutable()
	{
		return !$this->opened;
	}

	public function open()
	{
		if ($this->isMutable()) {
			throw new ConfigurationException('Already opened configuration!');
		}

		$this->previous = $this->data;
		$this->opened = true;
		return $this;
	}

	public function commit()
	{
		if ($this->isImmutable()) {
			throw new ConfigurationException('Cannot commit closed configuration!');
		}
		$this->adapter->notifyConfigChange();
		$this->previous = null;
		$this->opened = false;
	}

	public function revert()
	{
		$this->data = $this->previous;
		$this->opened = false;
	}

	public function set($param, $value = null, $path = 'global')
	{
		if (!$this->opened) {
			throw new ImmutableConfigException();
		}

		return parent::set($param, $value, $path);
	}

	public function isBound()
	{
		return true;
	}
}
