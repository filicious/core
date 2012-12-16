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

namespace Filicious\Stream;

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

	public function __construct($pathname, Exception $previous = null)
	{
		$this->pathname = $pathname;
		parent::__construct(
			sprintf(
				'There file %s does not support streaming!',
				$pathname
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
