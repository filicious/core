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

use Filicious\Internals\Adapter;
use Filicious\Internals\RootAdapter;
use Filicious\Internals\Pathname;
use Filicious\Internals\Util;
use Filicious\Plugin\FilesystemPluginInterface;
use Filicious\Plugin\PluginInterface;
use Filicious\Plugin\PluginManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Virtual filesystem structure.
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 */
class Filesystem
{

	/**
	 * @var RootAdapter
	 */
	protected $adapter;

	/**
	 * @var EventDispatcherInterface|null
	 */
	protected $eventDispatcher;

	/**
	 * @var PluginManager|null
	 */
	protected $pluginManager;

	/**
	 * @param Adapter $adapter
	 */
	public function __construct(Adapter $adapter)
	{
		$this->adapter = new RootAdapter($this);
		$this->adapter->setDelegate($adapter);
	}

	/**
	 * @return RootAdapter
	 */
	public function getRootAdapter()
	{
		return $this->adapter;
	}

	/**
	 * @return EventDispatcherInterface|null
	 */
	public function getEventDispatcher()
	{
		return $this->eventDispatcher;
	}

	/**
	 * @param EventDispatcherInterface|null $eventDispatcher
	 *
	 * @return static
	 */
	public function setEventDispatcher(EventDispatcherInterface $eventDispatcher = null)
	{
		$this->eventDispatcher = $eventDispatcher;
		return $this;
	}

	/**
	 * @return PluginManager|null
	 */
	public function getPluginManager()
	{
		return $this->pluginManager;
	}

	/**
	 * @param PluginManager|null $pluginManager
	 *
	 * @return static
	 */
	public function setPluginManager(PluginManager $pluginManager = null)
	{
		$this->pluginManager = $pluginManager;
		return $this;
	}

	/**
	 * Return a plugin for the filesystem.
	 *
	 * @param $name
	 *
	 * @return FilesystemPluginInterface|null
	 */
	public function hasPlugin($name)
	{
		return $this->pluginManager &&
			$this->pluginManager->hasPlugin($name) &&
			$this->pluginManager->getPlugin($name)->providesFilesystemPlugin($this);
	}

	/**
	 * Return a plugin for the filesystem.
	 *
	 * @param $name
	 *
	 * @return FilesystemPluginInterface|null
	 */
	public function getPlugin($name)
	{
		if ($this->pluginManager && $this->pluginManager->hasPlugin($name)) {
			$plugin = $this->pluginManager->getPlugin($name);

			if ($plugin->providesFilesystemPlugin($this)) {
				return $plugin->getFilesystemPlugin($this);
			}
		}

		return null;
	}

	/**
	 * Get the root (/) file node.
	 *
	 * @return File
	 */
	public function getRoot()
	{
		return $this->getFile(); // same as ->getFile('/') and ->getFile('')
	}

	/**
	 * Get a file object for the specific file.
	 *
	 * @param string $path
	 *
	 * @return File
	 */
	public function getFile($path = null)
	{
		// cheap recreate of File object
		if ($path instanceof Pathname && $path->rootAdapter() == $this->adapter) {
			return new File($path);
		}

		$pathname = implode('/', Util::getPathnameParts($path));
		strlen($pathname) && $pathname = '/' . $pathname;
		return new File(new Pathname($this->adapter, $pathname));
	}
}
