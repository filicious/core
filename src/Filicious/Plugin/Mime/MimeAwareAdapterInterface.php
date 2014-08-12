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

namespace Filicious\Plugin\Mime;

use Filicious\Exception\InvalidArgumentException;
use Filicious\Internals\AbstractAdapter;
use Filicious\Internals\Pathname;
use Filicious\Internals\Util;
use Filicious\Exception\FilesystemException;
use Filicious\Exception\AdapterException;
Use Filicious\Stream\BuildInStream;

/**
 * Local filesystem adapter.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
interface MimeAwareAdapterInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getMimeName(Pathname $pathname);

	/**
	 * {@inheritdoc}
	 */
	public function getMimeType(Pathname $pathname);

	/**
	 * {@inheritdoc}
	 */
	public function getMimeEncoding(Pathname $pathname);
}
