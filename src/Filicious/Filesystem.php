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
use Filicious\Internals\Pathname;
use Filicious\Internals\RootAdapter;
use Filicious\Internals\Util;
use Filicious\Plugin\FilesystemPluginInterface;
use Filicious\Plugin\PluginManager;
use Filicious\Stream\StreamManager;
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
	 * @var string
	 */
	protected $streamHost;

	/**
	 * @var string
	 */
	protected $streamScheme;

	/**
	 * @param Adapter $adapter
	 */
	public function __construct(Adapter $adapter)
	{
		$this->adapter = new RootAdapter($this);
		$this->adapter->setDelegate($adapter);
	}

	public function __destruct()
	{
		if ($this->streamHost) {
			$this->disableStreaming();
		}
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
	 * Enable streaming for this filesystem.
	 *
	 * @param      $host
	 * @param null $scheme
	 *
	 * @return static
	 */
	public function enableStreaming($host, $scheme = null)
	{
		if ($this->streamHost) {
			$this->disableStreaming();
		}

		$this->streamHost   = $host;
		$this->streamScheme = $scheme ?: 'filicious';
		StreamManager::registerFilesystem($this, $host, $scheme);

		return $this;
	}

	/**
	 * Disable streaming for this filesystem.
	 *
	 * @throws Exception\StreamWrapperNotRegisteredException
	 */
	public function disableStreaming()
	{
		if ($this->streamHost) {
			StreamManager::unregisterFilesystem($this->streamHost, $this->streamScheme);
			$this->streamHost   = null;
			$this->streamScheme = null;
		}
	}

	/**
	 * Determine is streaming currently enabled.
	 *
	 * @return bool
	 */
	public function isStreamingEnabled()
	{
		return (bool) $this->streamHost;
	}

	/**
	 * Return the stream host or null if streaming is disabled.
	 *
	 * @return string|null
	 */
	public function getStreamHost()
	{
		return $this->streamHost;
	}

	/**
	 * Return the stream scheme or null if streaming is disabled.
	 *
	 * @return string|null
	 */
	public function getStreamScheme()
	{
		return $this->streamScheme;
	}

	/**
	 * Return the complete stream url prefix, e.g. filicious://example
	 *
	 * @return string|null
	 */
	public function getStreamPrefix()
	{
		return sprintf('%s://%s', $this->streamScheme, $this->streamHost);
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
