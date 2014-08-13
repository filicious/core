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
 * @author  Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author  Tristan Lins <tristan.lins@bit3.de>
 * @author  Oliver Hoff <oliver@hofff.com>
 */
class FileNotFoundException
	extends FilesystemException
{

	/**
	 * @var \Filicious\Internals\Pathname
	 */
	protected $pathname;

	public function __construct(Pathname $pathname, $code = 0, $previous = null)
	{
		if ($code === 0) {
			$code = FilesystemException::FILE_NOT_FOUND;
		}
		parent::__construct(
			sprintf('File %s not found!', $pathname->full()),
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