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

/**
 * A plugin provide additional functionality for a filesystem or file.
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 */
interface PluginInterface
{
	/**
	 * Return the plugin name.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Determine if this plugin provide a filesystem plugin.
	 *
	 * @return bool
	 */
	public function providesFilesystemPlugin(Filesystem $filesystem);

	/**
	 * Return a filesystem plugin.
	 *
	 * @throws UnsupportedPluginException Thrown if this plugin does not provide a filesystem plugin.
	 * @return FilesystemPluginInterface
	 */
	public function getFilesystemPlugin(Filesystem $filesystem);

	/**
	 * Determine if this plugin provide a file plugin.
	 *
	 * @return bool
	 */
	public function providesFilePlugin(File $file);

	/**
	 * Return a file plugin.
	 *
	 * @throws UnsupportedPluginException Thrown if this plugin does not provide a file plugin.
	 * @return FilePluginInterface
	 */
	public function getFilePlugin(File $file);
}
