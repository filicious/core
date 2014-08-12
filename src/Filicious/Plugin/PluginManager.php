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

namespace Filicious\Plugin;

use Filicious\Exception\InvalidArgumentException;
use Filicious\Exception\UnsupportedPluginException;
use Filicious\File;
use Filicious\Filesystem;
use Filicious\Internals\AbstractAdapter;
use Filicious\Internals\Pathname;
use Filicious\Internals\Util;
use Filicious\Exception\FilesystemException;
use Filicious\Exception\AdapterException;
Use Filicious\Stream\BuildInStream;

class PluginManager
{
	/**
	 * @var PluginInterface[]
	 */
	protected $plugins = array();

	/**
	 * Register a plugin to this filesystem.
	 *
	 * @param PluginInterface $plugin
	 *
	 * @return static
	 */
	public function registerPlugin(PluginInterface $plugin)
	{
		$name = $plugin->getName();

		if (isset($this->plugins[$name])) {
			throw new FilesystemException(
				sprintf(
					'A plugin %s is already registered',
					$name
				)
			);
		}

		$this->plugins[$name] = $plugin;
	}

	/**
	 * Unregister a plugin from this filesystem.
	 *
	 * @param string $name
	 *
	 * @return static
	 */
	public function unregisterPlugin($name)
	{
		if (!isset($this->plugins[$name])) {
			throw new FilesystemException(
				sprintf(
					'A plugin %s is not registered',
					$name
				)
			);
		}

		unset($this->plugins[$name]);
	}

	/**
	 * Determine if a plugin is registered to this filesystem.
	 *
	 * @param string $name
	 */
	public function hasPlugin($name)
	{
		return isset($this->plugins[$name]);
	}

	/**
	 * @return array
	 */
	public function getPlugins()
	{
		return array_values($this->plugins);
	}

	/**
	 * Return a registered plugin.
	 *
	 * @return PluginInterface
	 */
	public function getPlugin($name)
	{
		if (!isset($this->plugins[$name])) {
			throw new FilesystemException(
				sprintf(
					'A plugin %s is not registered',
					$name
				)
			);
		}

		return $this->plugins[$name];
	}
}
