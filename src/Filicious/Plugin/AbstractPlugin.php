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

abstract class AbstractPlugin implements PluginInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function providesFilesystemPlugin(Filesystem $filesystem)
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilesystemPlugin(Filesystem $filesystem)
	{
		throw new UnsupportedPluginException(
			sprintf(
				'The %s plugin does not provide a filesystem plugin',
				$this->getName()
			)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function providesFilePlugin(File $file)
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilePlugin(File $file)
	{
		throw new UnsupportedPluginException(
			sprintf(
				'The %s plugin does not provide a file plugin',
				$this->getName()
			)
		);
	}
}
