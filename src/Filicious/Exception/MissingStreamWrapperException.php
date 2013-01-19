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

/**
 * Missing stream wrapper exception.
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class MissingStreamWrapperException extends StreamException
{
	protected $scheme;

	protected $host;

	public function __construct($scheme, $host = false)
	{
		$this->scheme = $scheme;
		$this->host   = $host;

		parent::__construct(
			sprintf(
				'It is no stream wrapper registered for scheme %s://%s!',
				$scheme,
				$host
			)
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
