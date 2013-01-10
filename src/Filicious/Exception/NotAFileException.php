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
 * Filesystem exception
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class NotAFileException
	extends FilesystemException
{
	protected $pathname;

	protected $local;

	public function __construct($pathname, $local, $code = 0, $previous = null) {
		parent::__construct(
			sprintf('Pathname %s is not a file!', $pathname),
			$code,
			$previous
		);
		$this->pathname = $pathname;
		$this->local = $local;
	}

	public function getPathname()
	{
		return $this->pathname;
	}

	public function getLocal()
	{
		return $this->local;
	}
}