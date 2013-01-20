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

use Filicious\File;
use Filicious\Filesystem;
use Filicious\FilesystemConfig;
use Filicious\Internals\Adapter;
use Filicious\Stream\StreamManager;

/**
 * Local filesystem adapter.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class RootAdapter
	extends DelegatorAdapter
{
	protected $streamScheme;

	protected $streamHost;

	/**
	 * @param string|FilesystemConfig $basepath
	 */
	public function __construct(Filesystem $fs)
	{
		$this->fs = $fs;
	}

	/**
	 * @param \Filicious\Internals\Adapter $delegate
	 */
	public function setDelegate($delegate)
	{
		$this->delegate = $delegate;
		return $this;
	}

	/**
	 * @return \Filicious\Internals\Adapter
	 */
	public function getDelegate()
	{
		return $this->delegate;
	}

	public function getConfig()
	{
		return $this->fs->getConfig();
	}

	public function getStreamURL(Pathname $pathname)
	{
		if ($this->streamScheme && $this->streamHost) {
			return $this->streamScheme . '://' . $this->streamHost . $pathname->full();
		}
		return $pathname->full();
	}

	public function notifyConfigChange()
	{
		// unregister previous registered stream wrapper
		if ($this->streamHost && $this->streamScheme) {
			StreamManager::unregisterFilesystem($this->streamHost, $this->streamScheme);
			$this->streamHost = null;
			$this->streamScheme = null;
		}

		// streaming disabled
		if (!$this->getConfig()->get(FilesystemConfig::STREAM_SUPPORTED, true)) {
			$this->streamHost = null;
			$this->streamScheme = null;
		}
		else {
			// register stream wrapper
			$host = $this->fs->getConfig()->get(FilesystemConfig::STREAM_HOST);
			$scheme = $this->fs->getConfig()->get(FilesystemConfig::STREAM_SCHEME);

			if ($host) {
				StreamManager::registerFilesystem($this->fs, $host, $scheme);
			}
			else {
				list($host, $scheme) = StreamManager::autoregisterFilesystem($this->fs);
			}

			$this->streamHost = $host;
			$this->streamScheme = $scheme;

			$this->fs->getConfig()->set(FilesystemConfig::STREAM_HOST, $host);
			$this->fs->getConfig()->set(FilesystemConfig::STREAM_SCHEME, $scheme);
		}

		// release unused stream wrappers
		StreamManager::free();

		parent::notifyConfigChange();
	}
}
