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

use Filicious\Exception\UnsupportedPluginException;
use Filicious\File;
use Filicious\Filesystem;

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
