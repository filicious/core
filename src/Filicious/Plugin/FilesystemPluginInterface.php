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
use Filicious\Filesystem;
use Filicious\Internals\AbstractAdapter;
use Filicious\Internals\Pathname;
use Filicious\Internals\Util;
use Filicious\Exception\FilesystemException;
use Filicious\Exception\AdapterException;
Use Filicious\Stream\BuildInStream;

/**
 * A filesystem plugin provide additional functionality for a filesystem.
 *
 * @package filicious-core
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 */
interface FilesystemPluginInterface
{
	/**
	 * Return the filesystem.
	 *
	 * @return Filesystem
	 */
	public function getFilesystem();
}