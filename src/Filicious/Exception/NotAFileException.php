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

use Filicious\Internals\Pathname;

/**
 * Filesystem exception
 *
 * @package filicious-core
 * @author  Tristan Lins <tristan.lins@bit3.de>
 */
class NotAFileException
	extends FilesystemException
{
	/**
	 * @var \Filicious\Internals\Pathname
	 */
	protected $pathname;

	protected $local;

	public function __construct(Pathname $pathname, $code = 0, $previous = null) {
		if ($code === 0) {
			$code = FilesystemException::NOT_A_FILE;
		}
		parent::__construct(
			sprintf('Pathname %s is not a file!', $pathname->full()),
			$code,
			$previous
		);
		$this->pathname = $pathname;
	}

	/**
	 * @return \Filicious\Internals\Pathname
	 */
	public function getPathname()
	{
		return $this->pathname;
	}
}