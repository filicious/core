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

namespace Filicious\Exception;

use Exception;

/**
 * Stream wrapper already registered exception.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class StreamNotSupportedException extends StreamException
{
	protected $pathname;

	public function __construct($srcPathname, Exception $previous = null)
	{
		$this->pathname = $srcPathname;
		parent::__construct(
			sprintf(
				'There file %s does not support streaming!',
				$srcPathname
			),
			0,
			$previous
		);
	}

	public function getScheme()
	{
		return $this->scheme;
	}

	public function getHost()
	{
		return $this->host;
	}
}
