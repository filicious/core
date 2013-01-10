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
class DirectoryOverwriteDirectoryException
	extends FilesystemException
{
	/**
	 * @var \Filicious\Internals\Pathname
	 */
	protected $srcPathname;

	/**
	 * @var \Filicious\Internals\Pathname
	 */
	protected $dstPathname;

	public function __construct(Pathname $srcPathname, Pathname $dstPathname, $code = 0, $previous = null) {
		parent::__construct(
			sprintf('Could not replace directory %s with directory %s!', $dstPathname->full(), $srcPathname->full()),
			$code,
			$previous
		);
		$this->srcPathname = $srcPathname;
		$this->dstPathname = $dstPathname;
	}

	/**
	 * @return \Filicious\Internals\Pathname
	 */
	public function getSrcPathname()
	{
		return $this->srcPathname;
	}

	/**
	 * @return \Filicious\Internals\Pathname
	 */
	public function getDstPathname()
	{
		return $this->dstPathname;
	}

}