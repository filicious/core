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

namespace Filicious\Iterator;

use Filicious\File;
use Filicious\FilesystemException;
use Filicious\Internals\PathnameIterator;
use Filicious\Internals\Util;

/**
 * Filesystem iterator
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class FilesystemIterator
	extends PathnameIterator
{
	public function __construct(File $path, $flags = 0)
	{
		parent::__construct($path->internalPathname(), $flags, Util::buildFilters($path, $flags));
	}
}
