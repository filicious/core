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

use Filicious\Internals\Pathname;

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
