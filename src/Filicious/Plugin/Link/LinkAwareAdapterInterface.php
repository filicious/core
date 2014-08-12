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

namespace Filicious\Plugin\Link;

use Filicious\Exception\InvalidArgumentException;
use Filicious\Internals\AbstractAdapter;
use Filicious\Internals\Pathname;
use Filicious\Internals\Util;
use Filicious\Exception\FilesystemException;
use Filicious\Exception\AdapterException;
Use Filicious\Stream\BuildInStream;

interface LinkAwareAdapterInterface
{
	/**
	 * Checks if the file is a (symbolic) link.
	 *
	 * @return bool True if the file is a link; otherwise false
	 */
	public function isLink(Pathname $pathname);

	/**
	 * Receive the link target from symbolic links.
	 *
	 * @return string|null
	 */
	public function getLinkTarget(Pathname $pathname);
}
